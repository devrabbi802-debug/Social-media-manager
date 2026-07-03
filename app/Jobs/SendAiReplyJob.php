<?php

namespace App\Jobs;

use App\Models\AiSetting;
use App\Models\AiSystemPrompt;
use App\Models\FacebookSetting;
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

    public int $tries = 3;

    public int $backoff = 45;

    public function retryUntil(): \DateTimeInterface
    {
        return now()->addMinutes(3);
    }

    public function __construct(
        public string $tenantId,
        public string $senderId,
        public string $messageText,
        public string $pageAccessToken,
        public ?string $imageUrl = null,
    ) {
        $this->onQueue('facebook');
    }

    public function middleware(): array
    {
        return [new WithoutOverlapping('facebook_reply_'.$this->tenantId.'_'.$this->senderId)];
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

            $reply = $this->imageUrl
                ? $this->handleImageMessage($facebookSetting, $systemPrompt)
                : $this->handleTextMessage($facebookSetting, $systemPrompt);

            $this->sendTypingIndicator(false);

            if (! $reply) {
                return;
            }

            $this->sendFacebookMessage($reply);

            Log::info('AI reply sent via Facebook', [
                'tenant_id' => $tenant->id,
                'sender_id' => $this->senderId,
                'message' => $this->messageText,
                'has_image' => $this->imageUrl !== null,
                'reply' => $reply,
            ]);
        });
    }

    private function handleTextMessage(FacebookSetting $facebookSetting, string $systemPrompt): ?string
    {
        $aiKeys = AiSetting::where('user_id', $facebookSetting->user_id)
            ->active()
            ->byType('message')
            ->byPriority()
            ->get();

        if ($aiKeys->isEmpty()) {
            return null;
        }

        $aiService = new AiChatService($systemPrompt);

        return $aiService->chatWithRotation($this->messageText, $aiKeys);
    }

    private function handleImageMessage(FacebookSetting $facebookSetting, string $systemPrompt): ?string
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

        $imageData = $this->downloadImage($this->imageUrl);

        if (! $imageData) {
            return null;
        }

        $imageDescription = null;

        foreach ($imageKeys as $key) {
            try {
                $geminiService = new GeminiApiService($key->api_key);
                $imageDescription = $geminiService->analyzeImage(
                    $imageData,
                    'এই ইমেজটি বিস্তারিত ভাবে বর্ণনা করো। ইমেজে কি আছে, কী দেখতে পাচ্ছো — প্রোডাক্ট হলে কোন প্রোডাক্ট, রঙ কি, সাইজ কত, কোন ব্র্যান্ড। সব কিছু বিস্তারিত লেখো।',
                );

                if ($imageDescription !== null) {
                    Log::info('Gemini image analysis done', [
                        'key_id' => $key->id,
                        'description_length' => strlen($imageDescription),
                    ]);

                    break;
                }
            } catch (\Exception $e) {
                Log::warning('Image AI key failed', [
                    'key_id' => $key->id,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        if (! $imageDescription) {
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

        $userMessage = $this->messageText
            ? "কাস্টমারের বার্তা: {$this->messageText}"
            : 'কাস্টমার শুধু একটি ইমেজ পাঠিয়েছে।';

        $combinedMessage = "{$userMessage}\n\nইমেজ বিশ্লেষণ:\n{$imageDescription}";

        $aiService = new AiChatService($systemPrompt);

        return $aiService->chatWithRotation($combinedMessage, $groqKeys);
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
