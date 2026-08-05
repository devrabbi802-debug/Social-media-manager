<?php

namespace App\Jobs;

use App\Models\AiSetting;
use App\Models\AiSystemPrompt;
use App\Models\BusinessSetting;
use App\Models\Conversation;
use App\Models\FacebookSetting;
use App\Models\Message;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\Tenant;
use App\Services\AiChatService;
use App\Services\AiTools\ToolExecutor;
use App\Services\AiTools\ToolRegistry;
use App\Services\AudioTranscriptionService;
use App\Services\ChatOrderService;
use App\Services\ChatSelectionService;
use App\Services\ClipService;
use App\Services\ProductContextService;
use App\Services\TextSearchService;
use App\Services\ZernioService;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class SendAiReplyJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 5;

    public int $backoff = 15;

    public int $timeout = 180;

    public function retryUntil(): \DateTimeInterface
    {
        return now()->addMinutes(3);
    }

    public function __construct(
        public string $tenantId,
        public string $senderId,
        public string $messageText,
        public string $pageAccessToken,
        public ?array $imageUrls = null,
        public ?string $audioUrl = null,
        public ?string $zernioAccountId = null,
        public ?string $zernioApiKey = null,
        public ?string $zernioConversationId = null,
        public ?string $replyToMid = null,
        public bool $drainPending = false,
    ) {
        $this->onQueue('facebook');
    }

    public function failed(\Throwable $exception): void
    {
        Log::error('SendAiReplyJob failed permanently', [
            'tenant_id' => $this->tenantId,
            'sender_id' => $this->senderId,
            'message' => $this->messageText,
            'error' => $exception->getMessage(),
        ]);
    }

    public function handle(): void
    {
        Log::info('SendAiReplyJob: handle() called', [
            'tenant_id' => $this->tenantId,
            'sender_id' => $this->senderId,
            'has_images' => ! empty($this->imageUrls),
            'has_audio' => $this->audioUrl !== null,
            'image_urls_count' => count($this->imageUrls ?? []),
        ]);

        // Step 0: Transcribe audio if present and no text provided
        if ($this->audioUrl && empty($this->messageText)) {
            $this->transcribeAudio();
        }

        $this->processReply();
    }

    private function transcribeAudio(): void
    {
        $tenant = Tenant::find($this->tenantId);

        if (! $tenant) {
            return;
        }

        $tenant->run(function () {
            $facebookSetting = FacebookSetting::where('connection_type', 'zernio')
                ->where('zernio_account_id', $this->zernioAccountId)
                ->first()
                ?? FacebookSetting::where('page_access_token', $this->pageAccessToken)->first();

            if (! $facebookSetting) {
                Log::warning('SendAiReplyJob: no facebookSetting for audio transcription', [
                    'tenant_id' => $this->tenantId,
                ]);

                return;
            }

            $transcriptionService = new AudioTranscriptionService;
            $transcribedText = $transcriptionService->transcribe($this->audioUrl, $facebookSetting->user_id);

            if ($transcribedText) {
                $this->messageText = "[ভয়েস মেসেজ থেকে ট্রান্সক্রিপ্ট — শব্দ ভুল থাকতে পারে] {$transcribedText}";
                Log::info('SendAiReplyJob: audio transcribed successfully', [
                    'sender_id' => $this->senderId,
                    'text' => mb_substr($transcribedText, 0, 100),
                ]);
            } else {
                Log::warning('SendAiReplyJob: audio transcription failed', [
                    'sender_id' => $this->senderId,
                ]);
                $this->messageText = 'দুঃখিত, আমি আপনার ভয়েস মেসেজ বুঝতে পারিনি। আপনি কি লিখে পাঠাতে পারেন?';
            }
        });
    }

    private function processReply(): void
    {
        $tenant = Tenant::find($this->tenantId);

        if (! $tenant) {
            Log::warning('SendAiReplyJob: tenant not found', ['tenant_id' => $this->tenantId]);

            return;
        }

        // Acquire an atomic lock for this sender to prevent concurrent jobs from
        // processing the same message simultaneously (race condition between
        // Facebook and Zernio webhooks, or job retries).
        // Lock expires in 120 seconds — long enough for one AI reply cycle.
        $lockKey = "ai_reply_lock:{$this->senderId}";
        $lock = Cache::lock($lockKey, 120);

        if (! $lock->get()) {
            Log::info('SendAiReplyJob: SKIPPED - lock held by another job for this sender', [
                'sender_id' => $this->senderId,
            ]);

            return;
        }

        try {
            $this->processReplyLocked($tenant);
        } finally {
            $lock->release();
        }
    }

    private function processReplyLocked(Tenant $tenant): void
    {
        $tenant->run(function () use ($tenant) {
            $conversation = Conversation::where('sender_id', $this->senderId)->first();

            // Drain images/text queued by debounced webhooks while this job was waiting.
            // Without this, burst images arriving during the debounce window get dropped.
            $this->drainPendingMessage();

            // Dedup: only skip when the triggering incoming message has ALREADY been answered.
            // A brand-new customer message arriving right after a reply must still be processed.
            if ($conversation) {
                $lastIncomingAt = Message::where('conversation_id', $conversation->id)
                    ->where('direction', 'incoming')
                    ->max('created_at');

                $lastOutgoingAt = Message::where('conversation_id', $conversation->id)
                    ->where('direction', 'outgoing')
                    ->max('created_at');

                $alreadyAnswered = $lastIncomingAt
                    && $lastOutgoingAt
                    && Carbon::parse($lastOutgoingAt)->greaterThanOrEqualTo(Carbon::parse($lastIncomingAt));

                if ($alreadyAnswered && ! $this->isDrainImageJob()) {
                    Log::info('SendAiReplyJob: SKIPPED - latest incoming message already answered', [
                        'sender_id' => $this->senderId,
                        'last_incoming_at' => $lastIncomingAt ? Carbon::parse($lastIncomingAt)->toDateTimeString() : null,
                        'last_outgoing_at' => $lastOutgoingAt ? Carbon::parse($lastOutgoingAt)->toDateTimeString() : null,
                    ]);

                    return;
                }

                // Collect recent images from batch (text+image or multi-image scenarios)
                $recentImages = Message::where('conversation_id', $conversation->id)
                    ->where('direction', 'incoming')
                    ->where('type', 'image')
                    ->where('created_at', '>=', now()->subSeconds(10))
                    ->pluck('image_path')
                    ->unique()
                    ->values()
                    ->toArray();

                if (! $this->drainPending && empty($this->imageUrls) && ! empty($recentImages)) {
                    // Text job: pick up recent images if available
                    Log::info('SendAiReplyJob: text job found recent images', [
                        'sender_id' => $this->senderId,
                        'image_count' => count($recentImages),
                    ]);
                    $this->imageUrls = $recentImages;
                } elseif (! $this->drainPending && ! empty($this->imageUrls) && count($recentImages) > count($this->imageUrls)) {
                    // Image job: collect additional images from same batch
                    Log::info('SendAiReplyJob: image job collected additional images', [
                        'sender_id' => $this->senderId,
                        'original_count' => count($this->imageUrls),
                        'total_count' => count($recentImages),
                    ]);
                    $this->imageUrls = $recentImages;
                }
            }

            $facebookSetting = FacebookSetting::where('connection_type', 'zernio')
                ->where('zernio_account_id', $this->zernioAccountId)
                ->first()
                ?? FacebookSetting::where('page_access_token', $this->pageAccessToken)->first();

            if (! $facebookSetting) {
                Log::warning('SendAiReplyJob: facebookSetting not found', [
                    'tenant_id' => $tenant->id,
                    'page_access_token' => substr($this->pageAccessToken, 0, 20).'...',
                ]);

                return;
            }

            // Build system prompt with tenant-specific business info
            $systemPrompt = $this->buildSystemPrompt($tenant, $facebookSetting->user_id);

            // Step 1: Mark message as seen (customer sees ✓✓)
            $this->sendMarkSeen();

            // Step 2: Start typing indicator with keep-alive
            $this->sendTypingIndicator(true);
            $typingStartedAt = time();

            $hasImages = ! empty($this->imageUrls);

            try {
                Log::info('SendAiReplyJob: starting AI processing', [
                    'tenant_id' => $tenant->id,
                    'sender_id' => $this->senderId,
                    'has_images' => $hasImages,
                ]);
                $result = $hasImages
                    ? $this->handleImageMessage($facebookSetting, $systemPrompt, $conversation)
                    : $this->handleTextMessage($facebookSetting, $systemPrompt, $conversation);

                // Typing keep-alive: re-send typing_on every 5 sec during long AI processing
                $elapsed = time() - $typingStartedAt;
                if ($elapsed > 5) {
                    $this->sendTypingIndicator(true);
                }
            } finally {
                $this->sendTypingIndicator(false);
            }

            if (! $result) {
                Log::warning('SendAiReplyJob: AI returned null result', [
                    'tenant_id' => $tenant->id,
                    'sender_id' => $this->senderId,
                ]);

                return;
            }

            $imageAnalysis = $result['image_analysis'] ?? null;
            $textProductMatches = $result['text_product_matches'] ?? null;
            $replies = $result['replies'] ?? null;

            if (empty($replies)) {
                // Single reply (text path or one matched product)
                $this->finalizeReply(
                    $result['reply'],
                    null,
                    null,
                    $hasImages,
                    $imageAnalysis,
                    $textProductMatches,
                    $conversation,
                    $facebookSetting
                );

                // Send any images the AI requested via send_product_image tool
                $pendingImages = $result['pending_images'] ?? [];
                foreach ($pendingImages as $pendingImage) {
                    $sentMids = $this->sendFacebookMessage(
                        "📸 {$pendingImage['product_name']}",
                        $pendingImage['image_url']
                    );

                    // Save outgoing image to conversation so it shows in dashboard
                    if ($conversation) {
                        try {
                            Message::create([
                                'conversation_id' => $conversation->id,
                                'direction' => 'outgoing',
                                'type' => 'image',
                                'content' => "📸 {$pendingImage['product_name']}",
                                'image_path' => $pendingImage['image_url'],
                                'facebook_mid' => $sentMids['image_mid'] ?? $sentMids['text_mid'] ?? null,
                                'image_analysis' => [
                                    'product_id' => $pendingImage['product_id'] ?? null,
                                    'product_name' => $pendingImage['product_name'] ?? null,
                                ],
                            ]);
                            $conversation->update(['last_message_at' => now()]);
                        } catch (\Throwable $e) {
                            Log::error('SendAiReplyJob: failed to save pending image to conversation', [
                                'product' => $pendingImage['product_name'],
                                'error' => $e->getMessage(),
                            ]);
                        }
                    }

                    Log::info('SendAiReplyJob: sent pending image from tool call', [
                        'product' => $pendingImage['product_name'],
                        'image_url' => substr($pendingImage['image_url'], 0, 80),
                    ]);
                }
            } else {
                // One separate reply per matched product, each with its image attached
                foreach ($replies as $item) {
                    $this->finalizeReply(
                        $item['text'],
                        $item['image_url'] ?? null,
                        $item['product'] ?? null,
                        $hasImages,
                        $imageAnalysis,
                        $textProductMatches,
                        $conversation,
                        $facebookSetting
                    );
                }
            }

            // If more debounced messages arrived while we were processing, hand them
            // to a follow-up drain job so no image in the burst is left unanswered.
            $this->maybeDispatchPendingDrain();
        });
    }

    /**
     * Detect a chat order in the reply, send it (optionally with an image
     * attachment), and persist the outgoing message record.
     */
    private function finalizeReply(
        string $replyText,
        ?string $imageUrl,
        ?array $product,
        bool $hasImages,
        ?array $imageAnalysis,
        $textProductMatches,
        ?Conversation $conversation,
        FacebookSetting $facebookSetting
    ): void {
        // ─── Chat Order Detection ───────────────────────────
        $chatOrderService = new ChatOrderService;
        $orderData = $chatOrderService->extractOrderData($replyText);
        $order = null;
        $orderCreated = false;

        if ($orderData) {
            $cleanReply = $chatOrderService->removeOrderDataBlock($replyText);
            $order = $chatOrderService->createChatOrder($orderData, $facebookSetting->user_id);

            if ($order) {
                $orderCreated = true;
                $confirmationMsg = "\n\n✅ আপনার অর্ডার কনফার্ম হয়েছে!\n📋 অর্ডার নম্বর: {$order->order_number}\n💰 মোট: ৳".number_format($order->total, 2)."\n📦 স্ট্যাটাস: {$order->status}";
                $replyText = $cleanReply.$confirmationMsg;

                Log::info('SendAiReplyJob: Chat order created', [
                    'order_id' => $order->id,
                    'order_number' => $order->order_number,
                    'sender_id' => $this->senderId,
                ]);
            } else {
                // Order creation failed — send original reply without JSON block
                $replyText = $cleanReply;
                Log::warning('SendAiReplyJob: Chat order creation failed', [
                    'sender_id' => $this->senderId,
                ]);
            }
        } elseif ($chatOrderService->containsOrderDataBlock($replyText)) {
            // Markers present but JSON invalid/incomplete (AI output truncated) —
            // raw markers customer ke dekhabe na. Strip kore di, order create hoy na.
            $replyText = $chatOrderService->removeOrderDataBlock($replyText);

            if (trim($replyText) === '') {
                $replyText = 'দুঃখিত, অর্ডারের তথ্যটি সম্পূর্ণ হয়নি। আপনি কি আবার আপনার অর্ডারের বিবরণ পাঠাবেন?';
            }

            Log::warning('SendAiReplyJob: order markers present but JSON invalid, stripped', [
                'sender_id' => $this->senderId,
            ]);
        }

        // ─── Chat Selection (multi-product cart) ────────────
        $selectionService = new ChatSelectionService;
        $selectionData = $selectionService->extractSelectionData($replyText);

        if ($selectionData) {
            $replyText = $selectionService->removeSelectionDataBlock($replyText);

            // AI shudhu JSON block diyeche — customer er jonno fallback text di, noile
            // empty message send fail kore job retry hoye selection double save hobe.
            if (trim($replyText) === '') {
                $replyText = 'ঠিক আছে, আপনার পছন্দের প্রোডাক্টটি নোট করলাম। আর কী কী নিতে চান বলুন।';
            }

            if (! $conversation) {
                $conversation = Conversation::where('sender_id', $this->senderId)->first();
            }

            if ($conversation) {
                $selectionService->applySelection($conversation, $selectionData);
                Log::info('SendAiReplyJob: chat selection saved', [
                    'sender_id' => $this->senderId,
                    'item_count' => count($selectionData['items'] ?? []),
                ]);
            }
        } elseif ($selectionService->containsSelectionDataBlock($replyText)) {
            // Markers present but JSON invalid/incomplete — raw markers customer ke
            // dekhabe na, selection save-o hobe na.
            $replyText = $selectionService->removeSelectionDataBlock($replyText);

            if (trim($replyText) === '') {
                $replyText = 'দুঃখিত, আপনার পছন্দের তথ্যটি সম্পূর্ণ হয়নি। আবার বলুন কী নিতে চান।';
            }

            Log::warning('SendAiReplyJob: selection markers present but JSON invalid, stripped', [
                'sender_id' => $this->senderId,
            ]);
        }

        $sentMids = $this->sendFacebookMessage($replyText, $imageUrl);
        $imageMid = $sentMids['image_mid'] ?? null;
        $textMid = $sentMids['text_mid'] ?? null;

        try {
            if (! $conversation) {
                $conversation = Conversation::where('sender_id', $this->senderId)->first();
            }

            if ($conversation) {
                $messageType = $hasImages ? 'ai_reply' : 'text';
                $extra = [];

                // Per-product reply: store the product matched for THIS image so a
                // future reply-to the sent image resolves the correct product.
                if ($product) {
                    $extra['matched_products'] = [$product];
                    $extra['original_image_urls'] = $imageUrl ? [$imageUrl] : ($this->imageUrls ?? []);
                } elseif ($imageAnalysis) {
                    foreach ($imageAnalysis as $key => $value) {
                        $extra[$key] = $value;
                    }
                }

                if ($hasImages && empty($extra['original_image_urls'])) {
                    $extra['original_image_urls'] = $this->imageUrls;
                }

                if ($textProductMatches && empty($extra['text_product_matches'])) {
                    $extra['text_product_matches'] = $textProductMatches;
                }

                if ($orderCreated && isset($order)) {
                    $extra['chat_order_id'] = $order->id;
                    $extra['chat_order_number'] = $order->order_number;
                }

                if ($imageMid) {
                    $extra['image_mid'] = $imageMid;
                }

                Message::create([
                    'conversation_id' => $conversation->id,
                    'direction' => 'outgoing',
                    'type' => $messageType,
                    'content' => $replyText,
                    'image_path' => $imageUrl,
                    'facebook_mid' => $textMid,
                    'image_analysis' => $extra !== [] ? $extra : null,
                ]);

                $conversation->update(['last_message_at' => now()]);

                Log::info('AI reply saved to conversation', [
                    'conversation_id' => $conversation->id,
                    'sender_id' => $this->senderId,
                ]);
            } else {
                Log::warning('Conversation not found for outgoing message', [
                    'sender_id' => $this->senderId,
                ]);
            }
        } catch (\Throwable $e) {
            Log::error('Failed to save AI reply to conversation', [
                'sender_id' => $this->senderId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
        }

        Log::info('AI reply sent via Facebook', [
            'tenant_id' => $this->tenantId,
            'sender_id' => $this->senderId,
            'message' => $this->messageText,
            'image_count' => count($this->imageUrls ?? []),
            'reply' => $replyText,
            'with_image' => $imageUrl !== null,
        ]);
    }

    /**
     * True when this job is a follow-up drain that must process images that arrived
     * after the main reply was already sent (i.e. the normal dedup would wrongly
     * consider them "already answered" even though they were never covered).
     */
    private function isDrainImageJob(): bool
    {
        return $this->drainPending && ! empty($this->imageUrls);
    }

    /**
     * Merge any debounced messages queued by the webhook into this job's payload.
     * The webhook queues images/text for every message it skips during the debounce
     * window; the job consumes them here so bursts of 2-3 images are all analyzed.
     */
    private function drainPendingMessage(): void
    {
        $pendingKey = "pending_messages:{$this->senderId}";
        $pending = Cache::get($pendingKey);

        if (empty($pending)) {
            return;
        }

        $images = $pending['images'] ?? [];
        $text = $pending['text'] ?? null;

        if (! empty($images)) {
            $this->imageUrls = array_values(array_unique(array_merge($this->imageUrls ?? [], $images)));
        }
        if ($text && empty($this->messageText)) {
            $this->messageText = $text;
        }

        Cache::forget($pendingKey);

        Log::info('SendAiReplyJob: drained pending debounced messages', [
            'sender_id' => $this->senderId,
            'drained_images' => count($images),
            'drained_text' => $text ? mb_substr($text, 0, 50) : null,
            'total_images' => count($this->imageUrls ?? []),
        ]);
    }

    /**
     * After a reply is saved, hand off any messages that were queued while this job
     * was processing to a follow-up drain job, so they are not lost to the debounce.
     */
    private function maybeDispatchPendingDrain(): void
    {
        $pendingKey = "pending_messages:{$this->senderId}";
        $pending = Cache::get($pendingKey);

        if (empty($pending)) {
            return;
        }

        $images = $pending['images'] ?? [];
        $text = $pending['text'] ?? null;

        // Drop images this job already processed (e.g. collected via DB recent-images
        // while pending was still empty). Otherwise the drain would re-reply about the
        // exact same products the customer already received.
        $processedImages = $this->imageUrls ?? [];
        if (! empty($images) && ! empty($processedImages)) {
            $images = array_values(array_filter(
                $images,
                fn ($url) => ! in_array($url, $processedImages, true)
            ));
        }

        if (empty($images) && empty($text)) {
            Cache::forget($pendingKey);

            return;
        }

        Cache::forget($pendingKey);

        Log::info('SendAiReplyJob: dispatching drain job for queued messages', [
            'sender_id' => $this->senderId,
            'images' => count($images),
            'text' => $text ? mb_substr($text, 0, 50) : null,
        ]);

        SendAiReplyJob::dispatch(
            tenantId: $this->tenantId,
            senderId: $this->senderId,
            messageText: $text ?? '',
            pageAccessToken: $this->pageAccessToken,
            imageUrls: $images,
            audioUrl: null,
            zernioAccountId: $this->zernioAccountId,
            zernioApiKey: $this->zernioApiKey,
            zernioConversationId: $this->zernioConversationId,
            drainPending: true,
        );
    }

    private function handleTextMessage(FacebookSetting $facebookSetting, string $systemPrompt, ?Conversation $conversation = null): ?array
    {
        $aiKeys = AiSetting::where('user_id', $facebookSetting->user_id)
            ->active()
            ->byType('message')
            ->byPriority()
            ->get();

        $cerebrasKeys = AiSetting::where('user_id', $facebookSetting->user_id)
            ->active()
            ->byType('cerebras')
            ->byPriority()
            ->get();

        $geminiKeys = AiSetting::where('user_id', $facebookSetting->user_id)
            ->active()
            ->byType('image')
            ->byPriority()
            ->get();

        Log::info('SendAiReplyJob: handleTextMessage', [
            'user_id' => $facebookSetting->user_id,
            'groq_keys_count' => $aiKeys->count(),
            'cerebras_keys_count' => $cerebrasKeys->count(),
            'gemini_keys_count' => $geminiKeys->count(),
            'message_text' => mb_substr($this->messageText, 0, 50),
        ]);

        // No keys at all = no reply
        if ($aiKeys->isEmpty() && $cerebrasKeys->isEmpty() && $geminiKeys->isEmpty()) {
            Log::warning('SendAiReplyJob: no AI keys (groq, cerebras, or gemini) for text message', ['user_id' => $facebookSetting->user_id]);

            return null;
        }

        // Check if there are recent images (text + image scenario)
        if (! $conversation) {
            $conversation = Conversation::where('sender_id', $this->senderId)->first();
        }

        // HIGHEST PRIORITY: If this is a reply to a specific message (swipe left), use that message's product context
        // Customer specifically asked about THIS product — must override current_product_data
        // If replyToMid not passed via job param (e.g. Zernio strips it), recover from DB
        // Search ALL conversations — FB direct saves with reply_to_mid in FB conversation, Zernio in separate conversation
        if (empty($this->replyToMid)) {
            $sameTextMessage = Message::where('direction', 'incoming')
                ->where('type', 'text')
                ->where('content', $this->messageText)
                ->whereNotNull('reply_to_mid')
                ->where('created_at', '>=', now()->subSeconds(10))
                ->latest()
                ->first();
            if ($sameTextMessage && $sameTextMessage->reply_to_mid) {
                $this->replyToMid = $sameTextMessage->reply_to_mid;
                Log::info('SendAiReplyJob: recovered reply_to_mid from FB conversation message', [
                    'reply_to_mid' => $this->replyToMid,
                    'found_in_message_id' => $sameTextMessage->id,
                ]);
            }
        }

        // PRIORITY 0.5: Heuristic swipe-reply fallback for Zernio
        // Zernio strips reply_to data, so replyToMid is always null.
        // Match short follow-up questions ("aitar price?", "eta koto?", "dame koto?")
        // against the most recent outgoing message that has product context.
        if (empty($this->replyToMid) && $conversation) {
            $text = strtolower(trim($this->messageText));
            $isShortFollowUp = mb_strlen($text) < 100 && preg_match('/(aitar|eta|ekhane|ekta|dami?|price|koto|taka|rate|cost|sizar?|colou?r|available|stock|delivery|shipping|discount|offer|emi|ase|nai|kina|khabo|lagbo|dibo|nibo|chAi| lagbe|dAm|mong?sho|pocha|panjabi|kurta|shirt|t[- ]?shirt|polo|jean|pant|salwar|kameez|saree|sari|ganji|vest|under|shorts|lungi|fatua|topi|hat|shoe|boot|sneaker|bag|wallet|belt|watch|ring|chain|necklace|earring|bangle|bracelet|spec|goggl|sunglass|cap|scarf|tie|sock|inner)/ui', $text);

            Log::info('SendAiReplyJob: heuristic check', [
                'text' => $text,
                'is_short_follow_up' => $isShortFollowUp,
                'conversation_id' => $conversation->id,
                'reply_to_mid' => $this->replyToMid ?? 'null',
            ]);

            if ($isShortFollowUp) {
                $recentOutgoing = Message::where('conversation_id', $conversation->id)
                    ->where('direction', 'outgoing')
                    ->where(function ($q) {
                        $q->whereNotNull('image_analysis')
                            ->orWhere('type', 'image');
                    })
                    ->where('created_at', '>=', now()->subMinutes(10))
                    ->latest()
                    ->first();

                Log::info('SendAiReplyJob: heuristic outgoing search', [
                    'found' => $recentOutgoing ? $recentOutgoing->id : null,
                    'has_analysis' => $recentOutgoing && $recentOutgoing->image_analysis ? true : false,
                    'analysis_keys' => $recentOutgoing && is_array($recentOutgoing->image_analysis) ? array_keys($recentOutgoing->image_analysis) : null,
                ]);

                if ($recentOutgoing && ! empty($recentOutgoing->image_analysis)) {
                    $analysis = is_string($recentOutgoing->image_analysis)
                        ? json_decode($recentOutgoing->image_analysis, true)
                        : $recentOutgoing->image_analysis;
                    // Check both image messages (matched_products) and text messages (text_product_matches)
                    $productMatches = $analysis['matched_products']
                        ?? $analysis['text_product_matches']
                        ?? $analysis['image_analysis']['matched_products']
                        ?? $analysis['image_analysis']['text_product_matches']
                        ?? [];
                    $bestMatch = $productMatches[0] ?? null;

                    if ($bestMatch && ! empty($bestMatch['metadata']['product_id'])) {
                        $productId = $bestMatch['metadata']['product_id'];
                        $productName = $bestMatch['metadata']['product_name'] ?? 'Product';

                        try {
                            $product = Product::with(['variants', 'images'])->find($productId);
                            if ($product) {
                                $currentPrice = $product->discount_price ?? $product->base_price ?? 'N/A';
                                $context = "Customer is asking about this product: {$productName} (ID: {$productId}, Price: {$currentPrice})";

                                Log::info('SendAiReplyJob: heuristic swipe-reply matched recent product', [
                                    'product_id' => $productId,
                                    'product_name' => $productName,
                                    'price' => $currentPrice,
                                    'outgoing_message_id' => $recentOutgoing->id,
                                ]);

                                $productContextService = new ProductContextService;
                                $productContextService->saveCurrentProduct($conversation, $productId, $bestMatch['metadata']['variant_id'] ?? null);

                                $history = $this->getConversationHistory();
                                $messageToSend = "{$this->messageText}\n\n[Context: Customer is asking about {$productName}. Price: {$currentPrice} BDT]";
                                $result = $this->callAiWithTools($messageToSend, $history, $aiKeys, $cerebrasKeys, $geminiKeys, $systemPrompt, $facebookSetting);

                                return $result ? ['reply' => $result['reply'], 'text_product_matches' => $productMatches, 'tool_calls_used' => $result['tool_calls_used'] ?? [], 'pending_images' => $result['pending_images'] ?? []] : null;
                            }
                        } catch (\Throwable $e) {
                            Log::warning('SendAiReplyJob: heuristic swipe-reply failed', ['error' => $e->getMessage()]);
                        }
                    }
                }
            }
        }

        // PRIORITY 1: Reply-to specific message product context
        if (! empty($this->replyToMid) && $conversation) {
            $replyContext = $this->resolveReplyToProductContext($conversation);
            if ($replyContext) {
                $history = $this->getConversationHistory();
                $textProductMatches = $replyContext['product_info'];
                $messageToSend = "{$this->messageText}\n\n{$replyContext['context']}";

                Log::info('SendAiReplyJob: using replied message product context (live DB prices)', [
                    'reply_to_mid' => $this->replyToMid,
                    'product' => $replyContext['product_name'],
                    'current_price' => $replyContext['current_price'],
                ]);

                // Save to conversation's current product context
                if ($replyContext['product_id']) {
                    $productContextService = new ProductContextService;
                    $productContextService->saveCurrentProduct($conversation, $replyContext['product_id'], $replyContext['variant_id']);
                }

                // Use tool-calling for reply-to context as well
                $result = $this->callAiWithTools($messageToSend, $history, $aiKeys, $cerebrasKeys, $geminiKeys, $systemPrompt, $facebookSetting);

                return $result ? ['reply' => $result['reply'], 'text_product_matches' => $textProductMatches, 'tool_calls_used' => $result['tool_calls_used'] ?? [], 'pending_images' => $result['pending_images'] ?? []] : null;
            }

            Log::info('SendAiReplyJob: reply_to_mid found but no product context in original message', [
                'reply_to_mid' => $this->replyToMid,
            ]);
        }

        // Only after reply-to check: if there are recent images, treat as text+image scenario
        if ($conversation && ! $this->drainPending) {
            $recentImages = Message::where('conversation_id', $conversation->id)
                ->where('direction', 'incoming')
                ->where('type', 'image')
                ->where('created_at', '>=', now()->subSeconds(10))
                ->pluck('image_path')
                ->toArray();

            if (! empty($recentImages)) {
                Log::info('SendAiReplyJob: text job found recent images, switching to image handler', [
                    'sender_id' => $this->senderId,
                    'image_count' => count($recentImages),
                ]);
                $this->imageUrls = $recentImages;

                return $this->handleImageMessage($facebookSetting, $systemPrompt, $conversation);
            }
        }

        $history = $this->getConversationHistory();

        // ─── FUNCTION CALLING PATH ────────────────────────────
        // AI autonomously decides what to do: search products, get details, send images, etc.
        $result = $this->callAiWithTools($this->messageText, $history, $aiKeys, $cerebrasKeys, $geminiKeys, $systemPrompt, $facebookSetting);

        if (! $result) {
            return null;
        }

        // Extract product matches from tool calls for conversation history context.
        // This lets the AI know what products were discussed in previous turns.
        $textProductMatches = $this->extractProductMatchesFromTools($result['tool_calls_used'] ?? []);

        return ['reply' => $result['reply'], 'text_product_matches' => $textProductMatches, 'tool_calls_used' => $result['tool_calls_used'] ?? [], 'pending_images' => $result['pending_images'] ?? []];
    }

    /**
     * Resolve product context from a reply-to message (swipe-left reply).
     * Returns context string + product info, or null if no product found.
     */
    private function resolveReplyToProductContext(Conversation $conversation): ?array
    {
        // Match by the text message id OR the image bubble id stored in image_analysis,
        // so quoting either the AI's text or the AI's sent image resolves correctly.
        $repliedMessage = Message::where('facebook_mid', $this->replyToMid)
            ->orWhere('image_analysis->image_mid', $this->replyToMid)
            ->first();

        $productInfo = null;
        $productName = null;

        if ($repliedMessage) {
            // Facebook sends text+image as ONE message with SAME mid.
            // If we found a TEXT, check if there's also an IMAGE with same mid.
            if ($repliedMessage->type === 'text' && ! $repliedMessage->image_path) {
                $imageVersion = Message::where('facebook_mid', $this->replyToMid)
                    ->where('type', 'image')
                    ->first();
                if ($imageVersion) {
                    Log::info('SendAiReplyJob: found image version of replied text message', [
                        'text_mid' => $this->replyToMid,
                        'image_path' => substr($imageVersion->image_path ?? '', 0, 60),
                    ]);
                    $repliedMessage = $imageVersion;
                }
            }

            $repliedImageUrl = $repliedMessage->image_path ?? null;

            // Case 1: Replied to outgoing AI reply (has image_analysis directly)
            if ($repliedMessage->image_analysis && $repliedMessage->direction === 'outgoing') {
                $analysis = $repliedMessage->image_analysis;
                $matchedProducts = $analysis['matched_products']
                    ?? $analysis['image_analysis']['matched_products']
                    ?? [];

                $productInfo = $this->findCorrectMatchedProduct($matchedProducts, $analysis, $repliedImageUrl);

                if (! $productInfo) {
                    $productInfo = $analysis['text_product_matches'][0]
                        ?? $analysis['image_analysis']['text_product_matches'][0]
                        ?? null;
                }
            }

            // Case 2: Replied to incoming image message — find the product for THAT specific image.
            // No tight time window: match the AI reply whose original_image_urls contains the
            // replied image URL, so replying to an older image still resolves correctly.
            if (! $productInfo && $repliedMessage->direction === 'incoming') {
                $aiReply = Message::where('direction', 'outgoing')
                    ->where('created_at', '>=', $repliedMessage->created_at)
                    ->whereNotNull('image_analysis')
                    ->orderBy('created_at', 'asc')
                    ->get()
                    ->first(function ($msg) use ($repliedImageUrl) {
                        if (! $repliedImageUrl) {
                            return false;
                        }

                        $analysis = $msg->image_analysis;
                        $urls = $analysis['original_image_urls']
                            ?? $analysis['image_analysis']['original_image_urls']
                            ?? [];

                        $repliedPath = parse_url($repliedImageUrl, PHP_URL_PATH) ?? $repliedImageUrl;

                        foreach ($urls as $url) {
                            $urlPath = parse_url($url, PHP_URL_PATH) ?? $url;
                            if ($repliedImageUrl === $url
                                || $repliedPath === $urlPath
                                || str_contains($repliedImageUrl, $url)
                                || str_contains($url, $repliedImageUrl)
                                || str_contains($repliedPath, $urlPath)
                            ) {
                                return true;
                            }
                        }

                        return false;
                    });

                if ($aiReply && $aiReply->image_analysis) {
                    $analysis = $aiReply->image_analysis;
                    $matchedProducts = $analysis['matched_products']
                        ?? $analysis['image_analysis']['matched_products']
                        ?? [];

                    $productInfo = $this->findCorrectMatchedProduct($matchedProducts, $analysis, $repliedImageUrl);

                    if (! $productInfo) {
                        $productInfo = $analysis['text_product_matches'][0]
                            ?? $analysis['image_analysis']['text_product_matches'][0]
                            ?? null;
                    }
                }

                // Stored analysis did not resolve (CLIP failed at the time, URL changed, or reply
                // was processed long ago). Re-run CLIP matching on the replied image directly.
                if (! $productInfo && $repliedImageUrl) {
                    $productInfo = $this->matchImageUrlToProduct($repliedImageUrl);
                    if ($productInfo) {
                        Log::info('SendAiReplyJob: replied image resolved via fresh CLIP match', [
                            'product' => $productInfo['product_name'] ?? 'unknown',
                        ]);
                    }
                }
            }

            if ($productInfo) {
                $productId = $productInfo['product_id'] ?? $productInfo['full_details']['product_id'] ?? null;
                $variantId = $productInfo['variant_id'] ?? $productInfo['full_details']['variant_id'] ?? null;
                $productName = $productInfo['full_details']['name']
                    ?? $productInfo['product_name']
                    ?? $productInfo['name']
                    ?? null;
            }
        }

        if (! $productName) {
            return null;
        }

        // Fetch CURRENT price from DB (stale prices stored in image_analysis are unreliable)
        $currentPrice = null;
        $currentStock = null;
        $currentDescription = null;
        $currentSku = null;
        $currentAttributes = null;
        $allVariants = null;

        if ($variantId) {
            $variant = ProductVariant::with('product')->find($variantId);
            if ($variant) {
                $currentPrice = $variant->price ?? $variant->product->discount_price ?? $variant->product->base_price;
                $currentStock = $variant->stock_quantity;
                $currentDescription = $variant->product->description ?? null;
                $currentSku = $variant->sku ?? null;
                $currentAttributes = $variant->attributes;

                $siblingVariants = $variant->product->variants
                    ->where('is_active', true)
                    ->where('id', '!=', $variant->id);
                $allVariants = $siblingVariants->map(fn ($v) => [
                    'attributes' => $v->attributes,
                    'price' => $v->price ?? $variant->product->discount_price ?? $variant->product->base_price,
                    'stock' => $v->stock_quantity,
                ])->values()->toArray();
                array_unshift($allVariants, [
                    'attributes' => $variant->attributes,
                    'price' => $variant->price ?? $variant->product->discount_price ?? $variant->product->base_price,
                    'stock' => $variant->stock_quantity,
                ]);
            }
        } elseif ($productId) {
            $product = Product::with('variants')->find($productId);
            if ($product) {
                $currentPrice = $product->discount_price ?? $product->base_price;
                $currentStock = $product->stock_quantity;
                $currentDescription = $product->description ?? null;
                $currentSku = $product->sku ?? null;

                $activeVariants = $product->variants->where('is_active', true);
                if ($activeVariants->count() > 0) {
                    $allVariants = $activeVariants->map(fn ($v) => [
                        'attributes' => $v->attributes,
                        'price' => $v->price ?? $product->discount_price ?? $product->base_price,
                        'stock' => $v->stock_quantity,
                    ])->values()->toArray();
                }
            }
        }

        $context = "কাস্টমার একটি নির্দিষ্ট প্রোডাক্টের উপর reply করে জিজ্ঞাসা করছে।\n\n";
        $context .= "প্রোডাক্ট: {$productName}";
        if ($currentPrice) {
            $context .= ' — দাম: ৳'.number_format((float) $currentPrice, 2);
        }
        if ($currentSku) {
            $context .= ", SKU: {$currentSku}";
        }
        if ($currentStock !== null) {
            $stockText = $currentStock > 0 ? "{$currentStock}টি স্টকে" : 'স্টক শেষ';
            $context .= ", স্টক: {$stockText}";
        }
        if ($currentDescription) {
            $context .= ", বিবরণ: {$currentDescription}";
        }
        if ($currentAttributes && ! empty($currentAttributes)) {
            $attrStr = collect($currentAttributes)->map(fn ($v, $k) => "{$k}: {$v}")->implode(', ');
            $context .= ", বৈশিষ্ট্য: {$attrStr}";
        }
        if ($allVariants && ! empty($allVariants)) {
            $context .= "\n\nউপলব্ধ বিকল্পসমূহ:\n";
            foreach ($allVariants as $v) {
                $vAttrs = collect($v['attributes'] ?? [])->map(fn ($val, $k) => "{$k}: {$val}")->implode(', ');
                $vStock = ($v['stock'] ?? 0) > 0 ? "{$v['stock']}টি স্টকে" : 'স্টক শেষ';
                $context .= "• {$vAttrs} — ৳".number_format($v['price'] ?? 0, 2)." [{$vStock}]\n";
            }
        }
        $context .= "\n\nউপরের প্রোডাক্টটি নিয়ে কাস্টমার প্রশ্ন করছে। এই তথ্য ব্যবহার করে উত্তর দিন।";

        return [
            'context' => $context,
            'product_info' => $productInfo,
            'product_name' => $productName,
            'product_id' => $productId,
            'variant_id' => $variantId,
            'current_price' => $currentPrice,
        ];
    }

    /**
     * Check if this is a follow-up question about the current product context.
     * Returns context string if follow-up, null if new inquiry.
     */
    private function resolveFollowUpContext(Conversation $conversation): ?string
    {
        $productContextService = new ProductContextService;
        $currentProduct = $productContextService->getCurrentProductContext($conversation);

        if (! $currentProduct) {
            return null;
        }

        $msgLower = mb_strtolower($this->messageText);
        $isFollowUp = false;

        // 1. If message contains current product's name → follow-up
        if (! empty($currentProduct['name'])) {
            $productName = mb_strtolower($currentProduct['name']);
            $nameWords = array_filter(explode(' ', $productName), fn ($w) => mb_strlen($w) >= 3);
            foreach ($nameWords as $word) {
                if (str_contains($msgLower, $word)) {
                    $isFollowUp = true;
                    break;
                }
            }
        }

        // 2. Customer pointing back to the current product ("aitar", "eta", "ওইটা" etc.)
        //    — treat as follow-up about the current product, NOT a new inquiry.
        //    Without this, "aitar red color..." runs a fresh search that can match a
        //    DIFFERENT product and silently override the current context.
        if (! $isFollowUp) {
            $referencePatterns = [
                '/\b(aitar|eitar|aita|eita|eta|etar|oita|oitake|oitar|ota|otake|otar)\b/i',
                '/(এটার|এইটার|ওইটার|ওইটা|সেইটা|এইটা|এটা|ওটা|ওটার|এগুল|ওগুলো|সেইগুলো)/iu',
                '/\b(this one|that one|the same)\b/i',
            ];

            foreach ($referencePatterns as $pattern) {
                if (preg_match($pattern, $msgLower)) {
                    $isFollowUp = true;
                    break;
                }
            }
        }

        // 3. Follow-up question patterns (short, no new product keyword)
        if (! $isFollowUp) {
            $followUpPatterns = [
                '/\b(price|dam|dama|daam|taka|kit|koto)\b/i',
                '/\b(stock|ache|nai|shesh|kothay)\b/i',
                '/\b(delivery|deliver|ship|pathano|diner)\b/i',
                '/\b(order|ordered|kinte|nibo)\b/i',
                '/\b(available|paben|diben|thake)\b/i',
                '/\b(confirm|confir)\b/i',
                '/\b(ok|thik|ji|ha|nah|nio|nii)\b/i',
            ];

            $isFollowUp = mb_strlen($this->messageText) < 20;
            if (! $isFollowUp) {
                foreach ($followUpPatterns as $pattern) {
                    if (preg_match($pattern, $msgLower)) {
                        $isFollowUp = true;
                        break;
                    }
                }
            }
        }

        // 4. If message asks about a DIFFERENT product → NOT follow-up
        if ($isFollowUp && mb_strlen($this->messageText) > 5) {
            $productWords = ['pant', 'shirt', 't-shirt', 'tshirt', 'shoe', 'bag', 'watch',
                'hat', 'cap', 'jacket', 'coat', 'dress', 'skirt', 'shorts', 'hoodie',
                'saree', 'salwar', 'kameez', 'punjabi', 'panjabi', 'vest', 'inner',
                'belt', 'wallet', 'sunglasses', 'glasses', 'ring', 'necklace'];
            foreach ($productWords as $pw) {
                if (str_contains($msgLower, $pw) && ! str_contains($msgLower, mb_strtolower($currentProduct['name'] ?? ''))) {
                    $isFollowUp = false;
                    Log::info('SendAiReplyJob: detected new product inquiry despite short message', [
                        'message' => $this->messageText,
                        'detected_product_word' => $pw,
                    ]);
                    break;
                }
            }
        }

        if (! $isFollowUp) {
            Log::info('SendAiReplyJob: skipping current product context - new product inquiry', [
                'sender_id' => $this->senderId,
                'message' => mb_substr($this->messageText, 0, 50),
                'current_product' => $currentProduct['name'] ?? 'unknown',
            ]);

            return null;
        }

        $contextString = $productContextService->buildContextString($conversation, $this->messageText);
        if ($contextString) {
            Log::info('SendAiReplyJob: using conversation current product context (follow-up)', [
                'sender_id' => $this->senderId,
                'product' => $currentProduct['name'] ?? 'unknown',
            ]);
        }

        return $contextString;
    }

    /**
     * Call AI with fallback chain: Groq → Cerebras → Gemini.
     */
    private function callAi(
        string $messageToSend,
        array $history,
        $aiKeys,
        $cerebrasKeys,
        $geminiKeys,
        string $systemPrompt,
        FacebookSetting $facebookSetting,
        ?array $textProductMatches = null
    ): ?array {
        $messageToSend = $this->appendSelectionContext($messageToSend);

        $reply = $this->chatWithAiChain($systemPrompt, $messageToSend, $history, $aiKeys, $cerebrasKeys, $geminiKeys);

        return $reply ? ['reply' => $reply, 'text_product_matches' => $textProductMatches] : null;
    }

    /**
     * Call AI with function calling (agentic loop).
     * AI autonomously decides which tools to call based on customer message.
     */
    private function callAiWithTools(
        string $messageToSend,
        array $history,
        $aiKeys,
        $cerebrasKeys,
        $geminiKeys,
        string $systemPrompt,
        FacebookSetting $facebookSetting,
    ): ?array {
        $messageToSend = $this->appendSelectionContext($messageToSend);

        $tools = ToolRegistry::getOpenAiTools();

        $conversation = Conversation::where('sender_id', $this->senderId)->first();
        $toolExecutor = new ToolExecutor(
            $this->senderId,
            $conversation,
            $conversation->sender_name ?? null,
            $this->pageAccessToken,
        );

        $aiService = new AiChatService($systemPrompt);

        $result = $aiService->chatWithToolsAndExecution(
            message: $messageToSend,
            keys: $aiKeys,
            history: $history,
            tools: $tools,
            toolExecutor: fn (string $toolName, array $args) => $toolExecutor->execute($toolName, $args),
            fallbackProvider: $cerebrasKeys->isNotEmpty() ? 'cerebras' : ($geminiKeys->isNotEmpty() ? 'gemini' : null),
            fallbackKeys: $cerebrasKeys->isNotEmpty() ? $cerebrasKeys : $geminiKeys,
            secondFallbackProvider: $cerebrasKeys->isNotEmpty() && $geminiKeys->isNotEmpty() ? 'gemini' : null,
            secondFallbackKeys: $cerebrasKeys->isNotEmpty() ? $geminiKeys : null,
        );

        if (! $result || empty($result['reply'])) {
            return null;
        }

        // Extract pending images from tool executor
        $pendingImages = $toolExecutor->getPendingImages();

        Log::info('SendAiReplyJob: callAiWithTools completed', [
            'sender_id' => $this->senderId,
            'tools_used' => count($result['tool_calls_used']),
            'tool_names' => array_column($result['tool_calls_used'], 'tool'),
            'pending_images' => count($pendingImages),
        ]);

        return [
            'reply' => $result['reply'],
            'tool_calls_used' => $result['tool_calls_used'],
            'pending_images' => $pendingImages,
        ];
    }

    /**
     * Customer er current selection (multi-product cart) er context AI er message e
     * append koro — jate AI jane kon kon product/variant kon quantity te bachai hoyeche.
     */
    private function appendSelectionContext(string $messageToSend): string
    {
        try {
            $conversation = Conversation::where('sender_id', $this->senderId)->first();

            if (! $conversation) {
                return $messageToSend;
            }

            $selectionContext = (new ChatSelectionService)->buildContextString($conversation);

            if ($selectionContext) {
                $messageToSend .= "\n\n{$selectionContext}";
            }
        } catch (\Throwable $e) {
            Log::warning('SendAiReplyJob: failed to append selection context', [
                'sender_id' => $this->senderId,
                'error' => $e->getMessage(),
            ]);
        }

        return $messageToSend;
    }

    /**
     * Extract product matches from tool_calls_used so they get stored in
     * image_analysis of the outgoing message. This gives the AI context
     * about what products were discussed in previous turns.
     */
    private function extractProductMatchesFromTools(array $toolCallsUsed): ?array
    {
        $matches = [];

        foreach ($toolCallsUsed as $toolCall) {
            $toolName = $toolCall['tool'] ?? '';
            $result = $toolCall['result'] ?? [];

            if ($toolName === 'search_products' && ! empty($result['products'])) {
                foreach ($result['products'] as $product) {
                    $matches[] = [
                        'metadata' => [
                            'product_name' => $product['name'] ?? 'Unknown',
                            'product_price' => $product['price'] ?? 0,
                            'stock_quantity' => $product['stock'] ?? null,
                            'product_id' => $product['product_id'] ?? null,
                            'variant_id' => $product['variant_id'] ?? null,
                            'variant_attributes' => $product['variant_attributes'] ?? null,
                        ],
                    ];
                }
            } elseif ($toolName === 'get_product_details' && ! empty($result['product_id'])) {
                $matches[] = [
                    'metadata' => [
                        'product_name' => $result['name'] ?? 'Unknown',
                        'product_price' => $result['price'] ?? 0,
                        'stock_quantity' => $result['stock'] ?? null,
                        'product_id' => $result['product_id'] ?? null,
                        'variant_id' => $result['variant_id'] ?? null,
                    ],
                ];
            }
        }

        return ! empty($matches) ? $matches : null;
    }

    /**
     * Run the AI fallback chain: Groq → Cerebras → Gemini.
     */
    private function chatWithAiChain(string $systemPrompt, string $messageToSend, array $history, $groqKeys, $cerebrasKeys, $geminiKeys): ?string
    {
        $aiService = new AiChatService($systemPrompt);

        if ($groqKeys->isNotEmpty()) {
            return $aiService->chatWithHistory(
                $messageToSend,
                $groqKeys,
                $history,
                'cerebras',
                $cerebrasKeys->isNotEmpty() ? $cerebrasKeys : null,
                'gemini',
                $geminiKeys->isNotEmpty() ? $geminiKeys : null,
            );
        }

        if ($cerebrasKeys->isNotEmpty()) {
            Log::info('SendAiReplyJob: no Groq keys, using Cerebras directly');
            $reply = $aiService->chatWithCerebrasFallback($messageToSend, $cerebrasKeys, $history);
            if ($reply === null && $geminiKeys->isNotEmpty()) {
                Log::info('SendAiReplyJob: Cerebras failed, falling back to Gemini');
                $reply = $aiService->chatWithGeminiFallback($messageToSend, $geminiKeys, $history);
            }

            return $reply;
        }

        Log::info('SendAiReplyJob: no Groq/Cerebras keys, using Gemini directly');

        return $aiService->chatWithGeminiFallback($messageToSend, $geminiKeys, $history);
    }

    private function handleImageMessage(FacebookSetting $facebookSetting, string $systemPrompt, ?Conversation $conversation = null): ?array
    {
        // Dedup: only skip when the triggering incoming message has ALREADY been answered.
        // A brand-new image message arriving right after a reply must still be processed.
        if (! $conversation) {
            $conversation = Conversation::where('sender_id', $this->senderId)->first();
        }
        if ($conversation) {
            $lastIncomingAt = Message::where('conversation_id', $conversation->id)
                ->where('direction', 'incoming')
                ->max('created_at');

            $lastOutgoingAt = Message::where('conversation_id', $conversation->id)
                ->where('direction', 'outgoing')
                ->max('created_at');

            $alreadyAnswered = $lastIncomingAt
                && $lastOutgoingAt
                && Carbon::parse($lastOutgoingAt)->greaterThanOrEqualTo(Carbon::parse($lastIncomingAt));

            if ($alreadyAnswered && ! $this->isDrainImageJob()) {
                Log::info('SendAiReplyJob: skipping image job - latest incoming already answered', [
                    'sender_id' => $this->senderId,
                ]);

                return null;
            }
        }

        $groqKeys = AiSetting::where('user_id', $facebookSetting->user_id)
            ->active()
            ->byType('message')
            ->byPriority()
            ->get();

        $cerebrasKeys = AiSetting::where('user_id', $facebookSetting->user_id)
            ->active()
            ->byType('cerebras')
            ->byPriority()
            ->get();

        $geminiKeys = AiSetting::where('user_id', $facebookSetting->user_id)
            ->active()
            ->byType('image')
            ->byPriority()
            ->get();

        if ($groqKeys->isEmpty() && $cerebrasKeys->isEmpty() && $geminiKeys->isEmpty()) {
            Log::warning('No AI keys (groq, cerebras, or gemini) available for image reply', [
                'user_id' => $facebookSetting->user_id,
            ]);

            return null;
        }

        // If the customer replied to a specific message (swipe reply), prioritize THAT image's
        // product over a fresh re-analysis — the reply may reference an earlier image.
        // If replyToMid not available (Zernio strips it), try DB recovery.
        if (empty($this->replyToMid) && $conversation) {
            $sameTextMessage = Message::where('direction', 'incoming')
                ->where('type', 'image')
                ->where('conversation_id', $conversation->id)
                ->whereNotNull('reply_to_mid')
                ->where('created_at', '>=', now()->subSeconds(10))
                ->latest()
                ->first();
            if ($sameTextMessage && $sameTextMessage->reply_to_mid) {
                $this->replyToMid = $sameTextMessage->reply_to_mid;
            }
        }
        if (! empty($this->replyToMid) && $conversation) {
            $replyContext = $this->resolveReplyToProductContext($conversation);
            if ($replyContext && $replyContext['product_id']) {
                Log::info('SendAiReplyJob: image job resolved replied product context', [
                    'sender_id' => $this->senderId,
                    'product' => $replyContext['product_name'] ?? 'unknown',
                ]);
                $productContextService = new ProductContextService;
                $productContextService->saveCurrentProduct(
                    $conversation,
                    $replyContext['product_id'],
                    $replyContext['variant_id']
                );

                return $this->callAi(
                    $this->messageText ? "{$this->messageText}\n\n{$replyContext['context']}" : $replyContext['context'],
                    $this->getConversationHistory(),
                    $groqKeys,
                    $cerebrasKeys,
                    $geminiKeys,
                    $systemPrompt,
                    $facebookSetting,
                    $replyContext['product_info']
                );
            }
        }

        // Check CLIP server health
        $clipService = new ClipService;
        $health = $clipService->healthCheck();

        if ($health['status'] !== 'healthy') {
            Log::warning('CLIP server is not healthy', ['health' => $health]);

            return $this->getFallbackReply();
        }

        // Get catalog embeddings for matching
        $catalogEmbeddings = $clipService->getCatalogEmbeddings();

        if (empty($catalogEmbeddings)) {
            Log::warning('No catalog embeddings found', ['user_id' => $facebookSetting->user_id]);

            return $this->getFallbackReply();
        }

        // Step 1: Process all images and collect matched products
        $matchedProducts = [];
        $processedImages = [];

        foreach ($this->imageUrls as $index => $imageUrl) {
            try {
                $customerEmbedding = $clipService->getEmbeddingFromUrl($imageUrl);

                if (! $customerEmbedding || ! isset($customerEmbedding['embedding'])) {
                    $processedImages[] = [
                        'index' => $index + 1,
                        'status' => 'error',
                        'message' => 'ছবি বিশ্লেষণ করা যায়নি',
                    ];

                    continue;
                }

                $matchResult = $clipService->matchImage(
                    base64_encode(file_get_contents($imageUrl)),
                    $catalogEmbeddings,
                    5,
                    config('services.clip.threshold', 0.7)
                );

                if ($matchResult && isset($matchResult['best_match'])) {
                    $bestMatch = $matchResult['best_match'];
                    $score = round($bestMatch['score'] * 100, 1);

                    // Skip low-confidence matches to avoid wrong product identification
                    if ($score < 80) {
                        Log::info('SendAiReplyJob: CLIP match score too low, skipping', [
                            'score' => $score,
                            'product' => $bestMatch['product_name'] ?? 'unknown',
                        ]);
                        $processedImages[] = [
                            'index' => $index + 1,
                            'status' => 'low_confidence',
                            'message' => "ম্যাচ স্কোর কম ({$score}%)",
                        ];

                        continue;
                    }

                    // Find full catalog item details
                    $catalogItem = collect($catalogEmbeddings)->first(function ($item) use ($bestMatch) {
                        return $item['id'] == $bestMatch['id'] && $item['product_name'] == $bestMatch['product_name'];
                    });

                    if ($catalogItem) {
                        // Get full product/variant details from database
                        $fullDetails = $this->getFullProductDetails($catalogItem);

                        $matchedProducts[] = [
                            'image_index' => $index + 1,
                            'match_score' => $score,
                            'product_id' => $catalogItem['product_id'],
                            'variant_id' => $catalogItem['variant_id'] ?? null,
                            'product_name' => $catalogItem['product_name'],
                            'product_sku' => $catalogItem['product_sku'],
                            'product_price' => $catalogItem['product_price'],
                            'variant_attributes' => $catalogItem['variant_attributes'] ?? [],
                            'full_details' => $fullDetails,
                            'alternatives' => array_slice($matchResult['matches'] ?? [], 1, 3),
                        ];

                        $processedImages[] = [
                            'index' => $index + 1,
                            'status' => 'matched',
                            'product' => $catalogItem['product_name'],
                            'score' => $score,
                        ];
                    }
                } else {
                    $processedImages[] = [
                        'index' => $index + 1,
                        'status' => 'no_match',
                        'message' => 'কোনো প্রোডাক্ট ম্যাচ করা যায়নি',
                    ];
                }
            } catch (\Exception $e) {
                Log::error('Image processing failed', [
                    'image_url' => $imageUrl,
                    'error' => $e->getMessage(),
                ]);
                $processedImages[] = [
                    'index' => $index + 1,
                    'status' => 'error',
                    'message' => 'ছবি প্রক্রিয়াকরণে সমস্যা',
                ];
            }
        }

        if (empty($matchedProducts)) {
            return $this->getFallbackReply();
        }

        // Save first matched product to conversation context
        if ($conversation && ! empty($matchedProducts[0])) {
            $firstMatch = $matchedProducts[0];
            $productContextService = new ProductContextService;
            $productContextService->saveCurrentProduct(
                $conversation,
                $firstMatch['product_id'],
                $firstMatch['variant_id'] ?? null
            );
        }

        // Step 2: Deduplicate matched products by product_id — if the same product
        // is matched from multiple images, only reply once (with the best match).
        $seenProductIds = [];
        $deduplicatedProducts = [];
        foreach ($matchedProducts as $matched) {
            $pid = $matched['product_id'];
            if ($pid && isset($seenProductIds[$pid])) {
                // Same product already added — keep the one with higher score
                if ($matched['match_score'] > $deduplicatedProducts[$seenProductIds[$pid]]['match_score']) {
                    $deduplicatedProducts[$seenProductIds[$pid]] = $matched;
                }

                continue;
            }
            $seenProductIds[$pid] = count($deduplicatedProducts);
            $deduplicatedProducts[] = $matched;
        }
        $matchedProducts = $deduplicatedProducts;

        // Generate a SEPARATE reply per matched product, each with the
        // customer's own matched image attached, so one image gets one clear answer.
        // Cap at 3 to avoid flooding the customer with too many messages.
        $matchedProducts = array_slice($matchedProducts, 0, 3);

        $imageCount = count($processedImages);
        $matchedCount = count($matchedProducts);

        $imageAnalysis = [
            'matched_products' => $matchedProducts,
            'processed_images' => $processedImages,
            'image_count' => $imageCount,
            'matched_count' => $matchedCount,
        ];

        $history = $this->getConversationHistory();

        $replies = [];
        foreach ($matchedProducts as $index => $matched) {
            // Keep the typing indicator alive across the multiple AI calls
            if ($index > 0) {
                $this->sendTypingIndicator(true);
            }

            $productContext = $this->buildSingleProductContext($matched);

            $userMessage = $this->messageText
                ? "কাস্টমারের বার্তা: {$this->messageText}"
                : 'কাস্টমার একটি ছবি পাঠিয়েছে যা নিচের প্রোডাক্টের সাথে ম্যাচ করেছে।';

            $combinedMessage = "{$userMessage}\n\nইমেজ বিশ্লেষণ:\n{$productContext}";

            // Fallback chain: Groq → Cerebras → Gemini
            $reply = $this->chatWithAiChain($systemPrompt, $combinedMessage, $history, $groqKeys, $cerebrasKeys, $geminiKeys);

            if (! $reply) {
                continue;
            }

            $replies[] = [
                'text' => $reply,
                'product' => $matched,
                'image_url' => $this->imageUrls[$matched['image_index'] - 1] ?? null,
            ];
        }

        if (empty($replies)) {
            return null;
        }

        return [
            'replies' => $replies,
            'image_analysis' => $imageAnalysis,
            'reply' => count($replies) === 1 ? $replies[0]['text'] : null,
        ];
    }

    private function getFullProductDetails(array $catalogItem): array
    {
        $details = [
            'name' => $catalogItem['product_name'],
            'sku' => $catalogItem['product_sku'],
            'price' => $catalogItem['product_price'],
        ];

        if ($catalogItem['type'] === 'product' && isset($catalogItem['product_id'])) {
            $product = Product::with(['category', 'brand', 'variants'])->find($catalogItem['product_id']);
            if ($product) {
                $details['description'] = $product->description;
                $details['category'] = $product->category->name ?? null;
                $details['brand'] = $product->brand->name ?? null;
                $details['stock'] = $product->stock_quantity;
                $details['status'] = $product->status;
                $details['base_price'] = $product->base_price;
                $details['discount_price'] = $product->discount_price;
                $details['price'] = $product->discount_price ?? $product->base_price;

                // Include all active variants with their attributes and stock
                if ($product->variants->count() > 0) {
                    $details['all_variants'] = $product->variants
                        ->where('is_active', true)
                        ->map(fn ($v) => [
                            'name' => $v->name,
                            'sku' => $v->sku,
                            'price' => $v->price ?? $product->discount_price ?? $product->base_price,
                            'stock' => $v->stock_quantity,
                            'attributes' => $v->attributes,
                        ])
                        ->values()
                        ->toArray();
                }
            }
        } elseif (isset($catalogItem['variant_id'])) {
            $variant = ProductVariant::with('product')->find($catalogItem['variant_id']);
            if ($variant) {
                $product = $variant->product;
                $details['description'] = $product->description ?? null;
                $details['category'] = $product->category->name ?? null;
                $details['brand'] = $product->brand->name ?? null;
                $details['stock'] = $variant->stock_quantity;
                $details['status'] = $product->status;
                $details['attributes'] = $variant->attributes;
                $details['base_price'] = $product->base_price;
                $details['discount_price'] = $product->discount_price;
                $details['variant_price'] = $variant->price;
                $details['price'] = $variant->price ?? $product->discount_price ?? $product->base_price;

                // Include all active sibling variants of the same product
                $siblingVariants = $product->variants
                    ->where('is_active', true)
                    ->where('id', '!=', $variant->id);
                if ($siblingVariants->count() > 0) {
                    $details['all_variants'] = $siblingVariants
                        ->map(fn ($v) => [
                            'name' => $v->name,
                            'sku' => $v->sku,
                            'price' => $v->price ?? $product->discount_price ?? $product->base_price,
                            'stock' => $v->stock_quantity,
                            'attributes' => $v->attributes,
                        ])
                        ->values()
                        ->toArray();
                    // Also include current variant at the beginning
                    array_unshift($details['all_variants'], [
                        'name' => $variant->name,
                        'sku' => $variant->sku,
                        'price' => $variant->price ?? $product->discount_price ?? $product->base_price,
                        'stock' => $variant->stock_quantity,
                        'attributes' => $variant->attributes,
                    ]);
                }
            }
        }

        return $details;
    }

    /**
     * Build an AI context for a SINGLE matched product (one image → one reply).
     * Includes the full variant list so the per-image reply can quote options.
     */
    private function buildSingleProductContext(array $product): string
    {
        $details = $product['full_details'];

        $context = "ম্যাচ করা প্রোডাক্ট:\n\n";
        $context .= '**প্রোডাক্ট:**'."\n";
        $context .= "- নাম: {$details['name']}\n";
        $context .= "- SKU: {$details['sku']}\n";
        $context .= '- মূল্য: ৳'.number_format($details['price'], 2)."\n";

        if (isset($details['description']) && $details['description']) {
            $context .= "- বিবরণ: {$details['description']}\n";
        }
        if (isset($details['category']) && $details['category']) {
            $context .= "- ক্যাটাগরি: {$details['category']}\n";
        }
        if (isset($details['brand']) && $details['brand']) {
            $context .= "- ব্র্যান্ড: {$details['brand']}\n";
        }
        if (isset($details['stock'])) {
            $stockText = $details['stock'] > 0 ? "{$details['stock']}টি স্টকে আছে" : 'স্টক শেষ';
            $context .= "- স্টক: {$stockText}\n";
        }
        if (isset($details['attributes']) && ! empty($details['attributes'])) {
            $attrs = collect($details['attributes'])->map(fn ($v, $k) => "{$k}: {$v}")->implode(', ');
            $context .= "- বিকল্প: {$attrs}\n";
        }
        if (isset($details['discount_price']) && $details['discount_price'] && $details['discount_price'] < $details['base_price']) {
            $context .= '- মূল্যছাড় মূল্য: ৳'.number_format($details['discount_price'], 2)."\n";
        }

        // Show all available variants of this product
        if (isset($details['all_variants']) && ! empty($details['all_variants'])) {
            $context .= "- উপলব্ধ বিকল্পসমূহ:\n";
            foreach ($details['all_variants'] as $variant) {
                $vAttrs = collect($variant['attributes'] ?? [])->map(fn ($v, $k) => "{$k}: {$v}")->implode(', ');
                $vStock = ($variant['stock'] ?? 0) > 0 ? "{$variant['stock']}টি স্টকে" : 'স্টক শেষ';
                $context .= "  • {$vAttrs} — ৳".number_format($variant['price'] ?? 0, 2)." [{$vStock}]\n";
            }
        }

        $context .= "- ম্যাচ স্কোর: {$product['match_score']}%\n\n";

        $context .= 'উপরের তথ্য ব্যবহার করে কাস্টমারকে উত্তর দিন। নিচের নিয়মগুলো মেনে চলুন:
- কাস্টমার ছবি পাঠালে প্রোডাক্টের নাম এবং দাম বলুন।
- ভ্যারিয়েন্ট রুল (গুরুত্বপূর্ণ): যদি উপলব্ধ বিকল্পসমূহ বা variants থাকে (Size, Color, Weight ইত্যাদি), তাহলে প্রোডাক্টের নাম, দাম এবং প্রতিটি variant-এর বিকল্পসহ দাম লিস্ট আকারে দেখাও — যেমন "Size: XL, Color: Blue — ৳356.00 (স্টক: ৫টি)"। শেষে শুধু জিজ্ঞাসা করো — "আপনি কোনটি নিতে চান?"। "আমাদের কাছে available আছে" এরকম অতিরিক্ত কথা বলো না। কিন্তু যদি প্রোডাক্টের কোনো variant না থাকে (শুধু একটাই অপশন), তাহলে শুধু দাম বলো — variant সম্পর্কে কিছু জিজ্ঞাসা করো না।
- কাস্টমার আরো জানতে চাইলে (দাম, স্টক, ফিচার, ডেলিভারি ইত্যাদি) তাহলে বিস্তারিত জানাবে।
- স্টক না থাকলে জানাবে এবং বিকল্প সুপারিশ করবে।
- মূল্য নিশ্চিত না হলে বলুন অফিসিয়াল পেজে যোগাযোগ করুন।
- কথোপকথনের স্বাভাবিক ধারা বজায় রাখুন।';

        return $context;
    }

    private function searchProductsByText(string $query): ?array
    {
        try {
            $textSearchService = new TextSearchService;
            $results = $textSearchService->searchText($query, topK: 5, threshold: 0.3);

            if (! $results || empty($results['matches'])) {
                return null;
            }

            $context = "কাস্টমারের প্রশ্ন: {$query}\n\nসম্পর্কিত প্রোডাক্টসমূহ:\n";

            foreach ($results['matches'] as $match) {
                $score = round($match['score'] * 100);
                $name = $match['product_name'];
                $metadata = $match['metadata'] ?? [];
                $pid = $metadata['product_id'] ?? $match['product_id'] ?? null;

                $line = "- {$name} (ম্যাচ: {$score}%)";

                if (isset($metadata['product_price'])) {
                    $line .= ' — দাম: ৳'.number_format($metadata['product_price'], 2);
                }
                if (isset($metadata['stock_quantity']) && $metadata['stock_quantity'] !== null) {
                    $line .= ", স্টক: {$metadata['stock_quantity']}টি";
                }
                if (isset($metadata['description']) && $metadata['description']) {
                    $line .= ", বিবরণ: {$metadata['description']}";
                }
                if (isset($metadata['variant_attributes']) && ! empty($metadata['variant_attributes'])) {
                    $attrs = $metadata['variant_attributes'];
                    $attrStr = collect($attrs)->map(fn ($v, $k) => "{$k}: {$v}")->implode(', ');
                    $line .= ", বৈশিষ্ট্য: {$attrStr}";
                }

                $context .= $line."\n";

                // Load full product with all active variants from DB
                if ($pid) {
                    $product = Product::with('variants')->find($pid);
                    if ($product) {
                        $activeVariants = $product->variants->where('is_active', true);
                        if ($activeVariants->count() > 0) {
                            $context .= "  উপলব্ধ বিকল্পসমূহ:\n";
                            foreach ($activeVariants as $v) {
                                $vAttrs = collect($v->attributes ?? [])->map(fn ($val, $k) => "{$k}: {$val}")->implode(', ');
                                $vStock = ($v->stock_quantity ?? 0) > 0 ? "{$v->stock_quantity}টি স্টকে" : 'স্টক শেষ';
                                $context .= "    • {$vAttrs} — ৳".number_format($v->price ?? $product->discount_price ?? $product->base_price, 2)." [{$vStock}]\n";
                            }
                        }
                    }
                }
            }

            $context .= "\nউপরের প্রোডাক্টগুলোর তথ্য ব্যবহার করে কাস্টমারকে সংক্ষেপে উত্তর দিন। ভ্যারিয়েন্ট রুল: যদি variant থাকে (Size, Color, Weight ইত্যাদি), তাহলে অবশ্যই জিজ্ঞাসা করো — 'কোন [size/color/weight] নিতে চান?'। variant না থাকলে শুধু দাম বলো।";

            return [
                'context' => $context,
                'matches' => $results['matches'],
            ];
        } catch (\Exception $e) {
            Log::error('SendAiReplyJob: text product search failed', [
                'query' => mb_substr($query, 0, 50),
                'error' => $e->getMessage(),
            ]);

            return null;
        }
    }

    private function getFallbackReply(): ?array
    {
        $fallbackReply = count($this->imageUrls) > 1
            ? 'আমি '.count($this->imageUrls).'টি ছবি পেয়েছি। দুঃখিত, ছবি বিশ্লেষণ করতে সাময়িক সমস্যা হচ্ছে। আপনি কি কী জানতে চান সেটা লিখে পাঠাতে পারেন?'
            : 'আমি আপনার ছবিটি পেয়েছি। দুঃখিত, ছবি বিশ্লেষণ করতে সাময়িক সমস্যা হচ্ছে। আপনি কি কী জানতে চান সেটা লিখে পাঠাতে পারেন?';

        return ['reply' => $fallbackReply];
    }

    private function getConversationHistory(): array
    {
        $conversation = Conversation::where('sender_id', $this->senderId)->first();

        if (! $conversation) {
            return [];
        }

        $messages = Message::where('conversation_id', $conversation->id)
            ->orderBy('created_at', 'desc')
            ->limit(50)
            ->get()
            ->reverse()
            ->values();

        $history = [];

        foreach ($messages as $msg) {
            if ($msg->direction === 'outgoing') {
                $productInfoParts = [];

                // Image analysis product context
                // Handle both flat (new) and double-nested (old) structures
                $matchedProducts = $msg->image_analysis['matched_products']
                    ?? $msg->image_analysis['image_analysis']['matched_products']
                    ?? [];
                if (! empty($matchedProducts)) {
                    foreach ($matchedProducts as $product) {
                        $details = $product['full_details'] ?? [];
                        $name = $details['name'] ?? 'N/A';
                        $sku = $details['sku'] ?? 'N/A';
                        $price = $details['price'] ?? 0;
                        $line = "- {$name} (SKU: {$sku}, মূল্য: ৳".number_format($price, 2).')';
                        if (isset($details['stock'])) {
                            $stockText = $details['stock'] > 0 ? "{$details['stock']}টি স্টকে" : 'স্টক শেষ';
                            $line .= " [স্টক: {$stockText}]";
                        }
                        if (isset($details['category'])) {
                            $line .= " [ক্যাটাগরি: {$details['category']}]";
                        }
                        if (isset($details['brand'])) {
                            $line .= " [ব্র্যান্ড: {$details['brand']}]";
                        }
                        if (isset($details['attributes']) && ! empty($details['attributes'])) {
                            $attrs = collect($details['attributes'])->map(fn ($v, $k) => "{$k}: {$v}")->implode(', ');
                            $line .= " [বৈশিষ্ট্য: {$attrs}]";
                        }
                        // Show all available variants in history
                        if (isset($details['all_variants']) && ! empty($details['all_variants'])) {
                            $variantList = collect($details['all_variants'])->map(function ($v) {
                                $vAttrs = collect($v['attributes'] ?? [])->map(fn ($val, $k) => "{$k}: {$val}")->implode(', ');
                                $vStock = ($v['stock'] ?? 0) > 0 ? "{$v['stock']}টি" : 'শেষ';

                                return "    • {$vAttrs} — ৳".number_format($v['price'] ?? 0, 2)." [{$vStock}]";
                            })->implode("\n");
                            $line .= " [উপলব্ধ বিকল্প:\n{$variantList}]";
                        }
                        $productInfoParts[] = $line;
                    }
                }

                // Text search product context
                $textMatches = $msg->image_analysis['text_product_matches']
                    ?? $msg->image_analysis['image_analysis']['text_product_matches']
                    ?? [];
                if (! empty($textMatches)) {
                    foreach ($textMatches as $match) {
                        $metadata = $match['metadata'] ?? [];
                        $name = $metadata['product_name'] ?? $match['product_name'] ?? 'N/A';
                        $price = $metadata['product_price'] ?? 0;
                        $stock = $metadata['stock_quantity'] ?? null;
                        $line = "- {$name} (মূল্য: ৳".number_format($price, 2).')';
                        if ($stock !== null) {
                            $line .= " [স্টক: {$stock}টি]";
                        }
                        if (isset($metadata['variant_attributes']) && ! empty($metadata['variant_attributes'])) {
                            $attrs = collect($metadata['variant_attributes'])->map(fn ($v, $k) => "{$k}: {$v}")->implode(', ');
                            $line .= " [বৈশিষ্ট্য: {$attrs}]";
                        }
                        $productInfoParts[] = $line;
                    }
                }

                if (! empty($productInfoParts)) {
                    $productInfo = implode("\n", $productInfoParts);
                    $history[] = [
                        'role' => 'assistant',
                        'content' => "আমি আগে এই প্রোডাক্টগুলো সম্পর্কে জানিয়েছি:\n{$productInfo}\n\nআমার উত্তর: {$msg->content}",
                    ];
                } else {
                    $history[] = [
                        'role' => 'assistant',
                        'content' => $msg->content,
                    ];
                }
            } else {
                $history[] = [
                    'role' => 'user',
                    'content' => $msg->content,
                ];
            }
        }

        // Total size cap — boro conversation e history (sob variant list soho) request er
        // limit chole gele AI API 413 (request too large) dey. Agey (purano) message gulo
        // drop koro, sobcheye recent kichu rakho.
        $historyBudget = 50000;
        $totalLength = 0;
        $cappedHistory = [];

        foreach (array_reverse($history) as $entry) {
            $entryLength = mb_strlen($entry['content']);

            if ($totalLength + $entryLength > $historyBudget && $totalLength > 0) {
                break;
            }

            $totalLength += $entryLength;
            $cappedHistory[] = $entry;
        }

        if (! empty($cappedHistory)) {
            $history = array_reverse($cappedHistory);
        }

        return $history;
    }

    /**
     * Extract the most recently discussed product from conversation history.
     * Used when text search doesn't find a good match (follow-up questions like "stock koto?").
     */
    private function searchProductByKeyword(string $messageText): ?array
    {
        // Common Bangla/English filler words to ignore
        $fillers = ['ki', 'koto', 'ase', 'tomader', 'amar', 'toder', 'abar', 'eta', 'oit', 'itar', 'itaire',
            'kemon', 'kibhabe', 'kothay', 'kon', 'konta', 'hobe', 'lagbe', 'dite', 'paro', 'paben',
            'price', 'stock', 'color', 'size', 'nam', 'name', 'design', 'material', 'quality',
            'korse', 'korsen', 'korte', 'korbo', 'korben', 'chi', 'chai', 'chilan', 'chilam',
            'please', 'plz', 'amake', 'jonno', 'diben', 'pabo', 'hote', 'ase?', 'ki?',
            'dita', 'nai', 'ache', 'kina', 'kinte', 'kino', 'naki', 'hobe?', 'lagbo', 'lagbe?',
            'dite?', 'parbo?', 'pabo?', 'janen?', 'janina', 'bollen', 'bolo', 'bolun',
            'keno', 'ky', 'kivabe', 'kothata', 'shei', 'oi', 'eta', 'ota', 'ita', 'ota'];

        // Extract words from message
        $words = preg_split('/[\s,?.!]+/', mb_strtolower(trim($messageText)));
        $keywords = array_filter(array_diff($words, $fillers), fn ($w) => mb_strlen($w) >= 3);

        // Search products table: name LIKE keyword, with Bangla suffix stripping
        $conn = DB::connection('tenant');

        // Build search terms: original keyword + stripped versions
        $suffixes = ['ir', 'er', 'tar', 'te', 'e', 'r', 'gulo', 'gula', 'ta', 'ti', 'tar', 'der', 'dertype'];

        $searchKeywords = ! empty($keywords) ? $keywords : $words;
        $searchKeywords = array_filter($searchKeywords, fn ($w) => mb_strlen($w) >= 2);

        foreach ($searchKeywords as $keyword) {
            if (mb_strlen($keyword) < 2) {
                continue;
            }

            // Build variants: original + stripped
            $variants = [$keyword];
            foreach ($suffixes as $suffix) {
                if (mb_strlen($keyword) > mb_strlen($suffix) + 2 && str_ends_with($keyword, $suffix)) {
                    $variants[] = mb_substr($keyword, 0, -mb_strlen($suffix));
                }
            }

            foreach ($variants as $variant) {
                $product = $conn->table('products')
                    ->where('status', 'active')
                    ->where(function ($q) use ($variant) {
                        $q->where('name', 'like', '%'.$variant.'%')
                            ->orWhere('sku', 'like', '%'.$variant.'%');
                    })
                    ->orderByRaw('CASE WHEN name LIKE ? THEN 0 ELSE 1 END', ['%'.$variant.'%'])
                    ->first();

                if ($product) {
                    // Load all variants of this product
                    $allVariants = $conn->table('product_variants')
                        ->where('product_id', $product->id)
                        ->where('is_active', true)
                        ->get()
                        ->map(fn ($v) => [
                            'attributes' => json_decode($v->attributes, true) ?? [],
                            'price' => $v->price ?? $product->discount_price ?? $product->base_price,
                            'stock' => $v->stock_quantity,
                        ])
                        ->toArray();

                    return [
                        'product_id' => $product->id,
                        'name' => $product->name,
                        'price' => $product->discount_price ?? $product->base_price,
                        'stock' => $product->stock_quantity,
                        'description' => $product->description ?? '',
                        'sku' => $product->sku ?? '',
                        'all_variants' => $allVariants,
                    ];
                }
            }
        }

        return null;
    }

    /**
     * Extract the most recently discussed product from conversation history.
     * Used when text search doesn't find a good match (follow-up questions like "stock koto?").
     */
    private function getLastDiscussedProduct(): ?array
    {
        $conversation = Conversation::where('sender_id', $this->senderId)->first();

        if (! $conversation) {
            return null;
        }

        // Look at last 10 messages for product context
        $messages = Message::where('conversation_id', $conversation->id)
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();

        foreach ($messages as $msg) {
            if ($msg->direction !== 'outgoing' || empty($msg->image_analysis)) {
                continue;
            }

            // Check text_product_matches first (newer) — handle both flat and nested
            $textMatches = $msg->image_analysis['text_product_matches']
                ?? $msg->image_analysis['image_analysis']['text_product_matches']
                ?? [];
            if (! empty($textMatches)) {
                $bestMatch = null;
                $bestScore = 0;

                foreach ($textMatches as $match) {
                    if (($match['score'] ?? 0) > $bestScore) {
                        $bestScore = $match['score'];
                        $bestMatch = $match;
                    }
                }

                if ($bestMatch) {
                    $metadata = $bestMatch['metadata'] ?? [];

                    return [
                        'product_id' => $metadata['product_id'] ?? $bestMatch['product_id'] ?? null,
                        'name' => $metadata['product_name'] ?? $bestMatch['product_name'] ?? null,
                        'price' => $metadata['product_price'] ?? null,
                        'stock' => $metadata['stock_quantity'] ?? null,
                        'description' => $metadata['description'] ?? '',
                        'sku' => $metadata['product_sku'] ?? '',
                        'variant_attributes' => $metadata['variant_attributes'] ?? null,
                        'type' => 'text_search',
                    ];
                }
            }

            // Check image_analysis matched_products (older)
            // Check image_analysis matched_products (older) — handle both flat and nested
            $imgMatchedProducts = $msg->image_analysis['matched_products']
                ?? $msg->image_analysis['image_analysis']['matched_products']
                ?? [];
            if (! empty($imgMatchedProducts)) {
                foreach ($imgMatchedProducts as $product) {
                    $details = $product['full_details'] ?? [];

                    return [
                        'product_id' => $details['product_id'] ?? $product['product_id'] ?? null,
                        'name' => $details['name'] ?? null,
                        'price' => $details['price'] ?? null,
                        'stock' => $details['stock'] ?? null,
                        'description' => $details['description'] ?? '',
                        'sku' => $details['sku'] ?? '',
                        'variant_attributes' => $details['attributes'] ?? null,
                        'all_variants' => $details['all_variants'] ?? null,
                        'type' => 'image_analysis',
                    ];
                }
            }

            // Fallback: check if message content itself mentions a product (outgoing AI reply)
            if ($msg->direction === 'outgoing' && ! empty($msg->content)) {
                // Try to find any product mentioned in the AI reply text
                $conn = DB::connection('tenant');
                $products = $conn->table('products')
                    ->where('status', 'active')
                    ->get();

                foreach ($products as $product) {
                    if (mb_stripos($msg->content, $product->name) !== false) {
                        $allVariants = $conn->table('product_variants')
                            ->where('product_id', $product->id)
                            ->where('is_active', true)
                            ->get()
                            ->map(fn ($v) => [
                                'attributes' => json_decode($v->attributes, true) ?? [],
                                'price' => $v->price ?? $product->discount_price ?? $product->base_price,
                                'stock' => $v->stock_quantity,
                            ])
                            ->toArray();

                        return [
                            'name' => $product->name,
                            'price' => $product->discount_price ?? $product->base_price,
                            'stock' => $product->stock_quantity,
                            'description' => $product->description ?? '',
                            'sku' => $product->sku ?? '',
                            'all_variants' => $allVariants,
                            'type' => 'keyword_match',
                        ];
                    }
                }
            }
        }

        return null;
    }

    /**
     * Match a single image URL against the tenant catalog via CLIP and return
     * product info compatible with resolveReplyToProductContext's expectations.
     */
    private function matchImageUrlToProduct(string $imageUrl): ?array
    {
        try {
            $clipService = new ClipService;
            $catalogEmbeddings = $clipService->getCatalogEmbeddings();

            if (empty($catalogEmbeddings)) {
                Log::warning('SendAiReplyJob: no catalog embeddings for replied image match', [
                    'image_url' => substr($imageUrl, 0, 80),
                ]);

                return null;
            }

            $customerEmbedding = $clipService->getEmbeddingFromUrl($imageUrl);

            if (! $customerEmbedding || ! isset($customerEmbedding['embedding'])) {
                Log::warning('SendAiReplyJob: could not embed replied image', [
                    'image_url' => substr($imageUrl, 0, 80),
                ]);

                return null;
            }

            $matchResult = $clipService->matchImage(
                base64_encode(file_get_contents($imageUrl)),
                $catalogEmbeddings,
                5,
                config('services.clip.threshold', 0.7)
            );

            if (! $matchResult || ! isset($matchResult['best_match'])) {
                return null;
            }

            $bestMatch = $matchResult['best_match'];
            $score = round($bestMatch['score'] * 100, 1);

            if ($score < 80) {
                Log::info('SendAiReplyJob: replied image CLIP score too low', [
                    'score' => $score,
                    'product' => $bestMatch['product_name'] ?? 'unknown',
                ]);

                return null;
            }

            $catalogItem = collect($catalogEmbeddings)->first(function ($item) use ($bestMatch) {
                return $item['id'] == $bestMatch['id'] && $item['product_name'] == $bestMatch['product_name'];
            });

            if (! $catalogItem) {
                return null;
            }

            return [
                'product_id' => $catalogItem['product_id'],
                'variant_id' => $catalogItem['variant_id'] ?? null,
                'product_name' => $catalogItem['product_name'],
                'product_sku' => $catalogItem['product_sku'],
                'product_price' => $catalogItem['product_price'],
                'variant_attributes' => $catalogItem['variant_attributes'] ?? [],
                'full_details' => $this->getFullProductDetails($catalogItem),
                'match_score' => $score,
            ];
        } catch (\Exception $e) {
            Log::error('SendAiReplyJob: replied image CLIP match failed', [
                'image_url' => substr($imageUrl, 0, 80),
                'error' => $e->getMessage(),
            ]);

            return null;
        }
    }

    /**
     * Find the correct matched product based on the replied image URL.
     * When customer swipes left on a specific image among multiple, we need
     * to find the product that corresponds to THAT specific image, not just
     * take matched_products[0].
     */
    private function findCorrectMatchedProduct(array $matchedProducts, array $analysis, ?string $repliedImageUrl): ?array
    {
        if (empty($matchedProducts) || ! $repliedImageUrl) {
            return $matchedProducts[0] ?? null;
        }

        $originalImageUrls = $analysis['original_image_urls']
            ?? $analysis['image_analysis']['original_image_urls']
            ?? [];

        if (empty($originalImageUrls)) {
            return $matchedProducts[0] ?? null;
        }

        // Normalize URL: strip query params for comparison (FB URLs may differ by params)
        $normalizeUrl = fn ($url) => parse_url($url, PHP_URL_PATH) ?? $url;

        $repliedPath = $normalizeUrl($repliedImageUrl);

        // Find which index the replied image URL corresponds to
        $imageIndex = null;
        foreach ($originalImageUrls as $idx => $url) {
            $originalPath = $normalizeUrl($url);
            if ($repliedImageUrl === $url
                || $repliedPath === $originalPath
                || str_contains($repliedImageUrl, $url)
                || str_contains($url, $repliedImageUrl)
                || str_contains($repliedPath, $originalPath)
                || str_contains($originalPath, $repliedPath)
            ) {
                $imageIndex = $idx + 1; // image_index is 1-based
                break;
            }
        }

        if ($imageIndex === null) {
            Log::info('findCorrectMatchedProduct: could not match image URL to index', [
                'replied_image_url' => substr($repliedImageUrl, 0, 80),
                'original_urls' => array_map(fn ($u) => substr($u, 0, 80), $originalImageUrls),
            ]);

            return $matchedProducts[0] ?? null;
        }

        foreach ($matchedProducts as $product) {
            if (($product['image_index'] ?? null) == $imageIndex) {
                Log::info('findCorrectMatchedProduct: matched by image_index', [
                    'image_index' => $imageIndex,
                    'product' => $product['product_name'] ?? 'unknown',
                ]);

                return $product;
            }
        }

        Log::warning('findCorrectMatchedProduct: no product found for image_index', [
            'image_index' => $imageIndex,
            'available_indexes' => array_column($matchedProducts, 'image_index'),
        ]);

        return $matchedProducts[0] ?? null;
    }

    private function buildSystemPrompt(Tenant $tenant, ?int $userId = null): string
    {
        $cacheKey = 'system_prompt_'.$tenant->id.($userId ? '_'.$userId : '');

        return cache()->remember($cacheKey, 300, function () use ($tenant, $userId) {
            // 1. Get base prompt from admin panel (landlord DB)
            $row = DB::connection('mysql')->table('ai_system_prompts')->first();
            $basePrompt = $row->prompt_text ?? (new AiSystemPrompt)->defaultPrompt();
            $basePrompt = str_replace(
                ['{company_name}', '{owner_name}'],
                [$tenant->name ?? 'এই কোম্পানি', $tenant->data['owner_name'] ?? ''],
                $basePrompt
            );

            // 2. Append tenant-specific business settings if available
            if ($userId) {
                $businessSetting = BusinessSetting::where('user_id', $userId)->first();
                if ($businessSetting) {
                    $businessPrompt = $businessSetting->generateSystemPrompt();
                    $basePrompt .= "\n\n{$businessPrompt}";
                }
            }

            return $basePrompt;
        });
    }

    /**
     * Send a reply via the connected channel. Returns the platform message ids
     * of the (optional) image bubble and the text bubble so reply-to can resolve.
     *
     * @return array{image_mid: ?string, text_mid: ?string}
     */
    private function sendFacebookMessage(string $text, ?string $imageUrl = null): array
    {
        // If connected via Zernio, use Zernio API
        if ($this->zernioAccountId && $this->zernioApiKey) {
            return $this->sendZernioMessage($text, $imageUrl);
        }

        // Otherwise, use Facebook Graph API directly. Send the image as its own
        // bubble first, then the details text below it.
        $imageMid = null;

        if ($imageUrl) {
            $response = Http::withHeaders([
                'Content-Type' => 'application/json',
            ])->post('https://graph.facebook.com/v21.0/me/messages', [
                'access_token' => $this->pageAccessToken,
                'recipient' => ['id' => $this->senderId],
                'message' => [
                    'attachment' => [
                        'type' => 'image',
                        'payload' => ['url' => $imageUrl],
                    ],
                ],
            ]);

            if ($response->successful()) {
                $imageMid = $response->json('message_id');
            } else {
                Log::warning('Facebook: image send failed, sending text only', [
                    'sender_id' => $this->senderId,
                    'image_url' => substr($imageUrl, 0, 80),
                ]);
            }
        }

        $response = Http::withHeaders([
            'Content-Type' => 'application/json',
        ])->post('https://graph.facebook.com/v21.0/me/messages', [
            'access_token' => $this->pageAccessToken,
            'recipient' => ['id' => $this->senderId],
            'message' => ['text' => $text],
        ]);

        if ($response->failed()) {
            throw new \Exception('Facebook send message failed: '.$response->body());
        }

        return [
            'image_mid' => $imageMid,
            'text_mid' => $response->json('message_id'),
        ];
    }

    /**
     * Send message via Zernio API. Returns the platform ids of the image and
     * text bubbles so reply-to resolves the correct product.
     *
     * @return array{image_mid: ?string, text_mid: ?string}
     */
    private function sendZernioMessage(string $text, ?string $imageUrl = null): array
    {
        $zernio = new ZernioService($this->zernioApiKey);

        $conversationId = $this->resolveZernioConversationId();

        if (! $conversationId) {
            throw new \Exception('Zernio: No conversation ID for sender '.$this->senderId);
        }

        // Send the image as its own bubble first, then the details text below it.
        // Zernio delivers image + text as ONE platform message (text often dropped),
        // so splitting into two messages gives the customer image-then-details.
        $imageMid = null;
        if ($imageUrl) {
            $imageResult = $zernio->sendInboxMessage(
                $conversationId,
                $this->zernioAccountId,
                null,
                $imageUrl
            );

            if ($imageResult) {
                $imageMid = $this->extractZernioMid($imageResult);
            } else {
                Log::warning('Zernio: image send failed, sending text only', [
                    'conversation_id' => $conversationId,
                    'image_url' => substr($imageUrl, 0, 80),
                ]);
            }
        }

        $result = $zernio->sendInboxMessage(
            $conversationId,
            $this->zernioAccountId,
            $text
        );

        if (! $result) {
            throw new \Exception('Zernio: Failed to send message');
        }

        return [
            'image_mid' => $imageMid,
            'text_mid' => $this->extractZernioMid($result),
        ];
    }

    private function extractZernioMid(?array $response): ?string
    {
        if (empty($response)) {
            return null;
        }

        $data = $response['data'] ?? $response;

        return $data['platformMessageId']
            ?? $data['platform_message_id']
            ?? $data['messageId']
            ?? $data['message_id']
            ?? $data['id']
            ?? null;
    }

    private function sendTypingIndicator(bool $on): void
    {
        // If connected via Zernio, use Zernio API
        if ($this->zernioAccountId && $this->zernioApiKey) {
            $conversationId = $this->resolveZernioConversationId();

            if ($conversationId && $on) {
                $zernio = new ZernioService($this->zernioApiKey);
                $zernio->sendTypingIndicator($conversationId, $this->zernioAccountId);
            }

            return;
        }

        // Otherwise, use Facebook Graph API directly
        Http::withHeaders([
            'Content-Type' => 'application/json',
        ])->post('https://graph.facebook.com/v21.0/me/messages', [
            'access_token' => $this->pageAccessToken,
            'recipient' => ['id' => $this->senderId],
            'sender_action' => $on ? 'typing_on' : 'typing_off',
        ]);
    }

    private function sendMarkSeen(): void
    {
        // If connected via Zernio, use Zernio API
        if ($this->zernioAccountId && $this->zernioApiKey) {
            $conversationId = $this->resolveZernioConversationId();

            if ($conversationId) {
                $zernio = new ZernioService($this->zernioApiKey);
                $zernio->markSeen($conversationId, $this->zernioAccountId);
            }

            return;
        }

        // Otherwise, use Facebook Graph API directly (mark_seen sender_action)
        Http::withHeaders([
            'Content-Type' => 'application/json',
        ])->post('https://graph.facebook.com/v21.0/me/messages', [
            'access_token' => $this->pageAccessToken,
            'recipient' => ['id' => $this->senderId],
            'sender_action' => 'mark_seen',
        ]);
    }

    private function resolveZernioConversationId(): ?string
    {
        if ($this->zernioConversationId) {
            return $this->zernioConversationId;
        }

        $cacheKey = "zernio_conversation:{$this->zernioAccountId}:{$this->senderId}";
        $cached = cache()->get($cacheKey);
        if ($cached) {
            return $cached;
        }

        $zernio = new ZernioService($this->zernioApiKey);
        $conversations = $zernio->listConversations($this->zernioAccountId, 50);
        $conversation = collect($conversations)->first(function ($conv) {
            return ($conv['contactId'] ?? $conv['participantId'] ?? $conv['senderId'] ?? '') === $this->senderId;
        });

        $conversationId = $conversation['_id'] ?? $conversation['id'] ?? null;

        if ($conversationId) {
            cache()->put($cacheKey, $conversationId, now()->addMinutes(30));
        }

        return $conversationId;
    }
}
