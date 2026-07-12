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
            'has_images' => !empty($this->imageUrls),
            'image_urls_count' => count($this->imageUrls ?? []),
        ]);

        if (empty($this->imageUrls)) {
            sleep(3);

            $conversation = Conversation::where('sender_id', $this->senderId)->first();

            if ($conversation) {
                $recentImages = Message::where('conversation_id', $conversation->id)
                    ->where('direction', 'incoming')
                    ->where('type', 'image')
                    ->where('created_at', '>=', now()->subSeconds(10))
                    ->pluck('image_path')
                    ->toArray();

                if (!empty($recentImages)) {
                    Log::info('SendAiReplyJob: text job found recent images after wait', [
                        'sender_id' => $this->senderId,
                        'image_count' => count($recentImages),
                    ]);
                    $this->imageUrls = $recentImages;
                } else {
                    $hasRecentReply = Message::where('conversation_id', $conversation->id)
                        ->where('direction', 'outgoing')
                        ->where('created_at', '>=', now()->subSeconds(5))
                        ->exists();

                    if ($hasRecentReply) {
                        Log::info('SendAiReplyJob: SKIPPED text job - reply already sent', [
                            'sender_id' => $this->senderId,
                        ]);
                        return;
                    }
                }
            }
        }

        $this->processReply();
    }

    private function processReply(): void
    {
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

        $geminiKeys = AiSetting::where('user_id', $facebookSetting->user_id)
            ->active()
            ->byType('image')
            ->byPriority()
            ->get();

        Log::info('SendAiReplyJob: handleTextMessage', [
            'user_id' => $facebookSetting->user_id,
            'groq_keys_count' => $aiKeys->count(),
            'gemini_keys_count' => $geminiKeys->count(),
            'message_text' => mb_substr($this->messageText, 0, 50),
        ]);

        // Groq key nai + Gemini o nai = kono reply jabe na
        if ($aiKeys->isEmpty() && $geminiKeys->isEmpty()) {
            Log::warning('SendAiReplyJob: no AI keys (groq or gemini) for text message', ['user_id' => $facebookSetting->user_id]);
            return null;
        }

        // Check if there are recent images (text + image scenario)
        $conversation = Conversation::where('sender_id', $this->senderId)->first();
        if ($conversation) {
            $recentImages = Message::where('conversation_id', $conversation->id)
                ->where('direction', 'incoming')
                ->where('type', 'image')
                ->where('created_at', '>=', now()->subSeconds(10))
                ->pluck('image_path')
                ->toArray();

            if (!empty($recentImages)) {
                Log::info('SendAiReplyJob: text job found recent images, switching to image handler', [
                    'sender_id' => $this->senderId,
                    'image_count' => count($recentImages),
                ]);
                $this->imageUrls = $recentImages;
                return $this->handleImageMessage($facebookSetting, $systemPrompt);
            }
        }

        $history = $this->getConversationHistory();

        // Jodi Groq key thake → Groq try koro, Gemini fallback
        // Jodi Groq na thake → directly Gemini diye reply koro
        if ($aiKeys->isNotEmpty()) {
            $aiService = new AiChatService($systemPrompt);
            $reply = $aiService->chatWithHistory($this->messageText, $aiKeys, $history, 'gemini', $geminiKeys);
        } else {
            Log::info('SendAiReplyJob: no Groq keys, using Gemini directly', ['user_id' => $facebookSetting->user_id]);
            $aiService = new AiChatService($systemPrompt);
            $reply = $aiService->chatWithGeminiFallback($this->messageText, $geminiKeys, $history);
        }

        return $reply ? ['reply' => $reply] : null;
    }

    private function handleImageMessage(FacebookSetting $facebookSetting, string $systemPrompt): ?array
    {
        // Check if a reply was already sent recently (within 10 sec) to avoid duplicate
        $conversation = Conversation::where('sender_id', $this->senderId)->first();
        if ($conversation) {
            $recentReply = Message::where('conversation_id', $conversation->id)
                ->where('direction', 'outgoing')
                ->where('created_at', '>=', now()->subSeconds(10))
                ->exists();

            if ($recentReply) {
                Log::info('SendAiReplyJob: skipping image job - reply already sent recently', [
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

        $geminiKeys = AiSetting::where('user_id', $facebookSetting->user_id)
            ->active()
            ->byType('image')
            ->byPriority()
            ->get();

        if ($groqKeys->isEmpty() && $geminiKeys->isEmpty()) {
            Log::warning('No AI keys (groq or gemini) available for image reply', [
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

        // Step 1: Process all images and collect matched products
        $matchedProducts = [];
        $processedImages = [];
        
        foreach ($this->imageUrls as $index => $imageUrl) {
            try {
                $customerEmbedding = $clipService->getEmbeddingFromUrl($imageUrl);
                
                if (!$customerEmbedding || !isset($customerEmbedding['embedding'])) {
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

        // Step 2: Create grouped product context
        $productContext = $this->buildProductContext($matchedProducts);

        // Step 3: Build message for AI
        $imageCount = count($processedImages);
        $matchedCount = count($matchedProducts);
        $imageWord = $imageCount > 1 ? "{$imageCount}টি ইমেজ" : 'একটি ইমেজ';
        $productWord = $matchedCount > 1 ? "{$matchedCount}টি প্রোডাক্ট" : 'একটি প্রোডাক্ট';

        $userMessage = $this->messageText
            ? "কাস্টমারের বার্তা: {$this->messageText}"
            : "কাস্টমার {$imageWord} পাঠিয়েছে।";

        $combinedMessage = "{$userMessage}\n\nইমেজ বিশ্লেষণ:\n{$productContext}";

        $history = $this->getConversationHistory();
        $aiService = new AiChatService($systemPrompt);

        if ($groqKeys->isNotEmpty()) {
            $reply = $aiService->chatWithHistory($combinedMessage, $groqKeys, $history, 'gemini', $geminiKeys);
        } else {
            Log::info('SendAiReplyJob: no Groq keys for image reply, using Gemini directly');
            $reply = $aiService->chatWithGeminiFallback($combinedMessage, $geminiKeys, $history);
        }

        return $reply ? [
            'reply' => $reply,
            'image_analysis' => [
                'matched_products' => $matchedProducts,
                'processed_images' => $processedImages,
                'image_count' => $imageCount,
                'matched_count' => $matchedCount,
            ],
        ] : null;
    }

    private function getFullProductDetails(array $catalogItem): array
    {
        $details = [
            'name' => $catalogItem['product_name'],
            'sku' => $catalogItem['product_sku'],
            'price' => $catalogItem['product_price'],
        ];

        if ($catalogItem['type'] === 'product' && isset($catalogItem['product_id'])) {
            $product = \App\Models\Product::with(['category', 'brand'])->find($catalogItem['product_id']);
            if ($product) {
                $details['description'] = $product->description;
                $details['category'] = $product->category->name ?? null;
                $details['brand'] = $product->brand->name ?? null;
                $details['stock'] = $product->stock_quantity;
                $details['status'] = $product->status;
                $details['base_price'] = $product->base_price;
                $details['discount_price'] = $product->discount_price;
            }
        } elseif (isset($catalogItem['variant_id'])) {
            $variant = \App\Models\ProductVariant::with('product')->find($catalogItem['variant_id']);
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
            }
        }

        return $details;
    }

    private function buildProductContext(array $matchedProducts): string
    {
        $context = "ম্যাচ করা প্রোডাক্টসমূহ:\n\n";
        
        foreach ($matchedProducts as $index => $product) {
            $details = $product['full_details'];
            $context .= "**প্রোডাক্ট " . ($index + 1) . ":**\n";
            $context .= "- নাম: {$details['name']}\n";
            $context .= "- SKU: {$details['sku']}\n";
            $context .= "- মূল্য: ৳" . number_format($details['price'], 2) . "\n";
            
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
                $stockText = $details['stock'] > 0 ? "{$details['stock']}টি স্টকে আছে" : "স্টক শেষ";
                $context .= "- স্টক: {$stockText}\n";
            }
            if (isset($details['attributes']) && !empty($details['attributes'])) {
                $attrs = collect($details['attributes'])->map(fn($v, $k) => "{$k}: {$v}")->implode(', ');
                $context .= "- বিকল্প: {$attrs}\n";
            }
            if (isset($details['variant_price']) && $details['variant_price']) {
                $context .= "- ভ্যারিয়েন্ট মূল্য: ৳" . number_format($details['variant_price'], 2) . "\n";
            }
            
            $context .= "- ম্যাচ স্কোর: {$product['match_score']}%\n";
            
            if (!empty($product['alternatives'])) {
                $altNames = collect($product['alternatives'])->pluck('product_name')->implode(', ');
                $context .= "- অন্যান্য সম্ভাব্য: {$altNames}\n";
            }
            
            $context .= "\n";
        }
        
        $context .= "উপরের তথ্য ব্যবহার করে কাস্টমারকে একটি সুন্দর এবং স্বাভাবিক কথোপকথনের ধরনে উত্তর দিন। শুধু দাম বা সংখ্যা তালিকাভুক্ত করবেন না। বরং এভাবে উত্তর দিন: 'আপনার ছবিতে এই প্রোডাক্টটি ম্যাচ করেছে — [প্রোডাক্টের নাম], যার দাম [মূল্য]। এটি স্টকে আছে/নেই। আপনি কি কিনতে চান?' এভাবে স্বাভাবিক ভাষায় উত্তর দিন।";
        
        return $context;
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

        $messages = Message::where('conversation_id', $conversation->id)
            ->orderBy('created_at', 'desc')
            ->limit(20)
            ->get()
            ->reverse()
            ->values();

        $history = [];

        foreach ($messages as $msg) {
            if ($msg->direction === 'outgoing' && $msg->image_analysis && !empty($msg->image_analysis['matched_products'])) {
                $productLines = [];
                foreach ($msg->image_analysis['matched_products'] as $product) {
                    $details = $product['full_details'] ?? [];
                    $name = $details['name'] ?? 'N/A';
                    $sku = $details['sku'] ?? 'N/A';
                    $price = $details['price'] ?? 0;
                    $line = "- {$name} (SKU: {$sku}, মূল্য: ৳" . number_format($price, 2) . ")";
                    if (isset($details['stock'])) {
                        $stockText = $details['stock'] > 0 ? "{$details['stock']}টি স্টকে" : "স্টক শেষ";
                        $line .= " [স্টক: {$stockText}]";
                    }
                    if (isset($details['category'])) {
                        $line .= " [ক্যাটাগরি: {$details['category']}]";
                    }
                    if (isset($details['brand'])) {
                        $line .= " [ব্র্যান্ড: {$details['brand']}]";
                    }
                    $productLines[] = $line;
                }

                $productInfo = implode("\n", $productLines);
                $history[] = [
                    'role' => 'assistant',
                    'content' => "আমি আগে এই প্রোডাক্টগুলো সম্পর্কে জানিয়েছি:\n{$productInfo}\n\nআমার উত্তর: {$msg->content}",
                ];
            } else {
                $history[] = [
                    'role' => $msg->direction === 'outgoing' ? 'assistant' : 'user',
                    'content' => $msg->content,
                ];
            }
        }

        return $history;
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
