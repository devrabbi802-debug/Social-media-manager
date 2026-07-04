<?php

namespace App\Jobs;

use App\Models\AiSetting;
use App\Models\AiSystemPrompt;
use App\Models\Conversation;
use App\Models\FacebookSetting;
use App\Models\Message;
use App\Models\Tenant;
use App\Services\AiChatService;
use App\Services\GeminiKeyManager;
use Illuminate\Bus\Batchable;
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
    use Batchable, Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;

    public int $backoff = 15;

    public int $timeout = 1800;

    public function retryUntil(): \DateTimeInterface
    {
        return now()->addMinutes(5);
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

        $keyManager = new GeminiKeyManager($facebookSetting->user_id);
        $keyStats = $keyManager->getKeyStats();

        if ($keyStats['total'] === 0) {
            Log::warning('No Gemini keys available', [
                'user_id' => $facebookSetting->user_id,
            ]);
            return $this->getFallbackReply();
        }

        $allDescriptions = $this->processImagesInBatches($facebookSetting->user_id);

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

    private function processImagesInBatches(string $userId): array
    {
        $allDescriptions = [];
        $batchSize = 10;
        $imageChunks = array_chunk($this->imageUrls, $batchSize);

        foreach ($imageChunks as $chunkIndex => $chunk) {
            $batchId = uniqid('batch_', true);
            $totalImages = count($chunk);

            cache()->put("batch_{$batchId}_results", [], 300);
            cache()->put("batch_{$batchId}_errors", [], 300);
            cache()->put("batch_{$batchId}_total", $totalImages, 300);
            cache()->put("batch_{$batchId}_done", false, 300);

            $jobs = [];
            foreach ($chunk as $index => $imageUrl) {
                $globalIndex = ($chunkIndex * $batchSize) + $index;
                $jobs[] = new ProcessImageBatch(
                    userId: $userId,
                    imageUrl: $imageUrl,
                    imageIndex: $globalIndex,
                    batchId: $batchId,
                );
            }

            \Bus::batch($jobs)->onQueue('facebook')->dispatch();

            $startTime = now();
            $timeout = 90;

            while (true) {
                if (now()->diffInSeconds($startTime) > $timeout) {
                    Log::warning('Batch processing timeout', [
                        'batch_id' => $batchId,
                        'elapsed' => now()->diffInSeconds($startTime),
                    ]);
                    break;
                }

                $done = cache()->get("batch_{$batchId}_done", false);
                if ($done) {
                    break;
                }

                usleep(500000);
            }

            $results = cache()->get("batch_{$batchId}_results", []);
            $errors = cache()->get("batch_{$batchId}_errors", []);

            usort($results, fn ($a, $b) => $a['index'] <=> $b['index']);

            foreach ($results as $result) {
                $allDescriptions[] = "ছবি " . ($result['index'] + 1) . ": " . $result['description'];
            }

            if ($chunkIndex < count($imageChunks) - 1) {
                sleep(2);
            }
        }

        return $allDescriptions;
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
            ->orderBy('created_at')
            ->get()
            ->map(fn (Message $msg) => [
                'role' => $msg->direction === 'outgoing' ? 'assistant' : 'user',
                'content' => $msg->content,
            ])
            ->toArray();
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
