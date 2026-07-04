<?php

namespace App\Jobs;

use App\Models\AiSetting;
use App\Models\AiSystemPrompt;
use App\Models\Conversation;
use App\Models\FacebookSetting;
use App\Models\Message;
use App\Models\Tenant;
use App\Services\AiChatService;
use App\Services\GeminiApiService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\Middleware\WithoutOverlapping;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class SendAiReplyJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 5;

    public int $backoff = 30;

    public int $timeout = 1800;

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

    public function middleware(): array
    {
        return [new WithoutOverlapping('ai-reply-' . $this->tenantId . '-' . $this->senderId)];
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
        $tenant = Tenant::find($this->tenantId);

        if (! $tenant) {
            return;
        }

        $systemPrompt = $this->buildSystemPrompt($tenant);

        $tenant->run(function () use ($tenant, $systemPrompt) {
            $facebookSetting = FacebookSetting::where('page_access_token', $this->pageAccessToken)->first();

            if (! $facebookSetting) {
                return;
            }

            $this->sendTypingIndicator(true);

            $hasImages = ! empty($this->imageUrls);

            $result = $hasImages
                ? $this->handleImageMessage($facebookSetting, $systemPrompt)
                : $this->handleTextMessage($facebookSetting, $systemPrompt);

            $this->sendTypingIndicator(false);

            if (! $result) {
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

        if ($aiKeys->isEmpty()) {
            return null;
        }

        $history = $this->getConversationHistory();

        $aiService = new AiChatService($systemPrompt);

        $reply = $aiService->chatWithHistory($this->messageText, $aiKeys, $history);

        return $reply ? ['reply' => $reply] : null;
    }

    private function handleImageMessage(FacebookSetting $facebookSetting, string $systemPrompt): ?array
    {
        $imageKeys = AiSetting::where('user_id', $facebookSetting->user_id)
            ->active()
            ->byType('image')
            ->byPriority()
            ->get();

        if ($imageKeys->isEmpty()) {
            Log::warning('No image AI keys available', [
                'user_id' => $facebookSetting->user_id,
            ]);

            return null;
        }

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

        $allDescriptions = [];
        $geminiCallCount = 0;

        foreach ($this->imageUrls as $index => $imageUrl) {
            if ($geminiCallCount > 0) {
                sleep(3);
            }

            $imageData = $this->downloadImage($imageUrl);

            if (! $imageData) {
                continue;
            }

            $imageDescription = null;

            foreach ($imageKeys as $key) {
                $maxRetries = 3;
                for ($attempt = 1; $attempt <= $maxRetries; $attempt++) {
                    try {
                        $geminiService = new GeminiApiService($key->api_key);
                        $imageDescription = $geminiService->analyzeImage(
                            $imageData,
                            'তুমি একটি প্রোডাক্ট ইমেজ বিশ্লেষণ করছো। নিচের নিয়মগুলো কঠোরভাবে মেনে চলো: ১) ইমেজের উপর থাকা ads, banner, watermark, text overlay, price tag, discount sticker, বা যেকোনো UI element সম্পর্কে কিছু লিখবে না। ২) শুধুমাত্র মূল প্রোডাক্টটি বর্ণনা করো — প্রোডাক্টের ধরন, রঙ, ডিজাইন, ম্যাটেরিয়াল, আনুমানিক সাইজ। ৩) প্রোডাক্ট ছাড়া অন্য কিছু (মানুষ, গাড়ি, রুম, প্রকৃতি) দেখতে পারলে তা বর্ণনা করো। ৪) ১৫০ শব্দের বেশি না লিখে সংক্ষেপে লেখো।',
                        );

                        $geminiCallCount++;

                        if ($imageDescription !== null) {
                            if (mb_strlen($imageDescription) > 800) {
                                $imageDescription = mb_substr($imageDescription, 0, 800) . '...';
                            }

                            Log::info('Gemini image analysis done', [
                                'key_id' => $key->id,
                                'image_index' => $index,
                                'description_length' => strlen($imageDescription),
                            ]);

                            break 2;
                        }
                    } catch (\Exception $e) {
                        if (str_contains($e->getMessage(), '429') && $attempt < $maxRetries) {
                            $wait = $attempt * 5;
                            Log::warning('Gemini 429, retrying after delay', [
                                'image_index' => $index,
                                'attempt' => $attempt,
                                'wait_seconds' => $wait,
                            ]);
                            sleep($wait);
                            continue;
                        }

                        Log::warning('Image AI key failed', [
                            'key_id' => $key->id,
                            'error' => $e->getMessage(),
                        ]);
                        break;
                    }
                }
            }

            if ($imageDescription) {
                $imageNum = count($allDescriptions) + 1;
                $allDescriptions[] = "ছবি {$imageNum}: {$imageDescription}";
            }
        }

        if (empty($allDescriptions)) {
            $fallbackReply = count($this->imageUrls) > 1
                ? "আমি ".count($this->imageUrls)."টি ছবি পেয়েছি। দুঃখিত, ছবি বিশ্লেষণ করতে সাময়িক সমস্যা হচ্ছে। আপনি কি কী জানতে চান সেটা লিখে পাঠাতে পারেন?"
                : "আমি আপনার ছবিটি পেয়েছি। দুঃখিত, ছবি বিশ্লেষণ করতে সাময়িক সমস্যা হচ্ছে। আপনি কি কী জানতে চান সেটা লিখে পাঠাতে পারেন?";

            return ['reply' => $fallbackReply];
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

    private function getConversationHistory(): array
    {
        $conversation = Conversation::where('sender_id', $this->senderId)->first();

        if (! $conversation) {
            return [];
        }

        return Message::where('conversation_id', $conversation->id)
            ->orderBy('created_at')
            ->get()
            ->map(fn (Message $msg) => [
                'role' => $msg->direction === 'outgoing' ? 'assistant' : 'user',
                'content' => $msg->content,
            ])
            ->toArray();
    }

    private function downloadImage(?string $url): ?string
    {
        try {
            $response = Http::timeout(30)->get($url);

            if ($response->failed()) {
                Log::error('Failed to download image', ['url' => $url]);

                return null;
            }

            return base64_encode($response->body());
        } catch (\Exception $e) {
            Log::error('Image download exception', [
                'url' => $url,
                'error' => $e->getMessage(),
            ]);

            return null;
        }
    }

    private function buildSystemPrompt(Tenant $tenant): string
    {
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
            throw new \Exception('Facebook send message failed: '.$response->body());
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
