<?php

namespace App\Jobs;

use App\Models\AiSetting;
use App\Models\AiSystemPrompt;
use App\Models\Conversation;
use App\Models\FacebookSetting;
use App\Models\Message;
use App\Models\Tenant;
use App\Services\AiChatService;
use App\Services\ClipService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class SendAiReplyJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 5;

    public int $backoff = 15;

    public int $timeout = 90;

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
        ]);

        $tenant = Tenant::find($this->tenantId);

        if (! $tenant) {
            Log::warning('SendAiReplyJob: tenant not found', ['tenant_id' => $this->tenantId]);
            return;
        }

        $systemPrompt = $this->buildSystemPrompt($tenant);

        $tenant->run(function () use ($tenant, $systemPrompt) {
            $facebookSetting = FacebookSetting::where('page_access_token', $this->pageAccessToken)->first();

            if (! $facebookSetting) {
                Log::warning('SendAiReplyJob: facebookSetting not found', [
                    'tenant_id' => $tenant->id,
                    'page_access_token' => substr($this->pageAccessToken, 0, 20) . '...',
                ]);
                return;
            }

            $this->sendTypingIndicator(true);

            $hasImages = ! empty($this->imageUrls);

            try {
                Log::info('SendAiReplyJob: starting AI processing', [
                    'tenant_id' => $tenant->id,
                    'sender_id' => $this->senderId,
                    'has_images' => $hasImages,
                ]);
                $result = $hasImages
                    ? $this->handleImageMessage($facebookSetting, $systemPrompt)
                    : $this->handleTextMessage($facebookSetting, $systemPrompt);
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

            $reply = $result['reply'];
            $imageAnalysis = $result['image_analysis'] ?? null;

            $this->sendFacebookMessage($reply);

            try {
                $conversation = Conversation::where('sender_id', $this->senderId)->first();

                if ($conversation) {
                    $messageType = $hasImages ? 'ai_reply' : 'text';
                    $extra = [];

                    if ($imageAnalysis) {
                        $extra['image_analysis'] = $imageAnalysis;
                    }

                    if ($hasImages) {
                        $extra['original_image_urls'] = $this->imageUrls;
                    }

                    Message::create([
                        'conversation_id' => $conversation->id,
                        'direction' => 'outgoing',
                        'type' => $messageType,
                        'content' => $reply,
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
                'tenant_id' => $tenant->id,
                'sender_id' => $this->senderId,
                'message' => $this->messageText,
                'image_count' => count($this->imageUrls ?? []),
                'reply' => $reply,
            ]);
        });
    }

    private function handleTextMessage(FacebookSetting $facebookSetting, string $systemPrompt): ?array
    {
        $aiKeys = AiSetting::where('user_id', $facebookSetting->user_id)
            ->active()
            ->byType('message')
            ->byPriority()
            ->get();

        Log::info('SendAiReplyJob: handleTextMessage', [
            'user_id' => $facebookSetting->user_id,
            'ai_keys_count' => $aiKeys->count(),
            'message_text' => mb_substr($this->messageText, 0, 50),
        ]);

        if ($aiKeys->isEmpty()) {
            Log::warning('SendAiReplyJob: no AI keys for text message', ['user_id' => $facebookSetting->user_id]);
            return null;
        }

        $history = $this->getConversationHistory();
        $aiService = new AiChatService($systemPrompt);
        $reply = $aiService->chatWithHistory($this->messageText, $aiKeys, $history);

        return $reply ? ['reply' => $reply] : null;
    }

    private function handleImageMessage(FacebookSetting $facebookSetting, string $systemPrompt): ?array
    {
        $groqKeys = AiSetting::where('user_id', $facebookSetting->user_id)
            ->active()
            ->byType('message')
            ->byPriority()
            ->get();

        if ($groqKeys->isEmpty()) {
            Log::warning('No message AI keys available for image reply', [
                'user_id' => $facebookSetting->user_id,
            ]);
            return null;
        }

        // Check CLIP server health
        $clipService = new ClipService();
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

        $allDescriptions = [];
        
        // Process each image
        foreach ($this->imageUrls as $index => $imageUrl) {
            try {
                // Get customer image embedding
                $customerEmbedding = $clipService->getEmbeddingFromUrl($imageUrl);
                
                if (!$customerEmbedding || !isset($customerEmbedding['embedding'])) {
                    $allDescriptions[] = "ছবি " . ($index + 1) . ": ছবি বিশ্লেষণ করা যায়নি।";
                    continue;
                }

                // Match against catalog
                $matchResult = $clipService->matchImage(
                    base64_encode(file_get_contents($imageUrl)),
                    $catalogEmbeddings,
                    5,
                    config('services.clip.threshold', 0.7)
                );

                if ($matchResult && isset($matchResult['best_match'])) {
                    $bestMatch = $matchResult['best_match'];
                    $score = round($bestMatch['score'] * 100, 1);
                    
                    $description = "প্রোডাক্ট ম্যাচ: {$bestMatch['product_name']} (স্কোর: {$score}%)";
                    
                    // Add alternative matches if any
                    if (count($matchResult['matches']) > 1) {
                        $alternatives = array_slice($matchResult['matches'], 1, 3);
                        $altNames = array_column($alternatives, 'product_name');
                        $description .= "\nঅন্যান্য সম্ভাব্য: " . implode(', ', $altNames);
                    }
                    
                    $allDescriptions[] = "ছবি " . ($index + 1) . ": " . $description;
                } else {
                    $allDescriptions[] = "ছবি " . ($index + 1) . ": এই ছবির সাথে কোনো প্রোডাক্ট ম্যাচ করা যায়নি।";
                }
            } catch (\Exception $e) {
                Log::error('Image processing failed', [
                    'image_url' => $imageUrl,
                    'error' => $e->getMessage(),
                ]);
                $allDescriptions[] = "ছবি " . ($index + 1) . ": ছবি প্রক্রিয়াকরণে সমস্যা হয়েছে।";
            }
        }

        if (empty($allDescriptions)) {
            return $this->getFallbackReply();
        }

        $combinedDescriptions = implode("\n\n", $allDescriptions);

        $imageCount = count($allDescriptions);
        $imageWord = $imageCount > 1 ? "{$imageCount}টি ইমেজ" : 'একটি ইমেজ';

        $userMessage = $this->messageText
            ? "কাস্টমারের বার্তা: {$this->messageText}"
            : "কাস্টমার {$imageWord} পাঠিয়েছে।";

        $combinedMessage = "{$userMessage}\n\nইমেজ বিশ্লেষণ:\n{$combinedDescriptions}";

        $history = $this->getConversationHistory();
        $aiService = new AiChatService($systemPrompt);
        $reply = $aiService->chatWithHistory($combinedMessage, $groqKeys, $history);

        return $reply ? [
            'reply' => $reply,
            'image_analysis' => [
                'descriptions' => $allDescriptions,
                'image_urls' => $this->imageUrls,
                'image_count' => $imageCount,
            ],
        ] : null;
    }

    private function getFallbackReply(): ?array
    {
        $fallbackReply = count($this->imageUrls) > 1
            ? "আমি " . count($this->imageUrls) . "টি ছবি পেয়েছি। দুঃখিত, ছবি বিশ্লেষণ করতে সাময়িক সমস্যা হচ্ছে। আপনি কি কী জানতে চান সেটা লিখে পাঠাতে পারেন?"
            : "আমি আপনার ছবিটি পেয়েছি। দুঃখিত, ছবি বিশ্লেষণ করতে সাময়িক সমস্যা হচ্ছে। আপনি কি কী জানতে চান সেটা লিখে পাঠাতে পারেন?";

        return ['reply' => $fallbackReply];
    }

    private function getConversationHistory(): array
    {
        $conversation = Conversation::where('sender_id', $this->senderId)->first();

        if (! $conversation) {
            return [];
        }

        return Message::where('conversation_id', $conversation->id)
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get()
            ->reverse()
            ->map(fn (Message $msg) => [
                'role' => $msg->direction === 'outgoing' ? 'assistant' : 'user',
                'content' => $msg->content,
            ])
            ->toArray();
    }

    private function buildSystemPrompt(Tenant $tenant): string
    {
        $cacheKey = 'system_prompt_' . $tenant->id;

        return cache()->remember($cacheKey, 300, function () use ($tenant) {
            $row = DB::connection('mysql')->table('ai_system_prompts')->first();

            if (! $row) {
                return (new AiSystemPrompt)->defaultPrompt();
            }

            $prompt = $row->prompt_text ?? (new AiSystemPrompt)->defaultPrompt();

            return str_replace(
                ['{company_name}', '{owner_name}'],
                [$tenant->name ?? 'এই কোম্পানি', $tenant->data['owner_name'] ?? ''],
                $prompt
            );
        });
    }

    private function sendFacebookMessage(string $text): void
    {
        $response = Http::withHeaders([
            'Content-Type' => 'application/json',
        ])->post('https://graph.facebook.com/v21.0/me/messages', [
            'access_token' => $this->pageAccessToken,
            'recipient' => ['id' => $this->senderId],
            'message' => ['text' => $text],
        ]);

        if ($response->failed()) {
            throw new \Exception('Facebook send message failed: ' . $response->body());
        }
    }

    private function sendTypingIndicator(bool $on): void
    {
        Http::withHeaders([
            'Content-Type' => 'application/json',
        ])->post('https://graph.facebook.com/v21.0/me/messages', [
            'access_token' => $this->pageAccessToken,
            'recipient' => ['id' => $this->senderId],
            'sender_action' => $on ? 'typing_on' : 'typing_off',
        ]);
    }
}
