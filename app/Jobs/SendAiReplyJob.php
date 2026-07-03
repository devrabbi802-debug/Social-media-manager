<?php

namespace App\Jobs;

use App\Models\AiSetting;
use App\Models\AiSystemPrompt;
use App\Models\FacebookSetting;
use App\Models\Tenant;
use App\Services\AiChatService;
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
    ) {
        $this->onQueue('facebook');
    }

    public function middleware(): array
    {
        return [new WithoutOverlapping('facebook_reply_' . $this->tenantId . '_' . $this->senderId)];
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

            $aiSetting = AiSetting::where('user_id', $facebookSetting->user_id)->first();

            if (! $aiSetting) {
                return;
            }

            $aiService = new AiChatService($aiSetting->api_key, $systemPrompt);
            $reply = $aiService->chat($this->messageText);

            if (! $reply) {
                return;
            }

            $this->sendFacebookMessage($reply);

            Log::info('AI reply sent via Facebook', [
                'tenant_id' => $tenant->id,
                'sender_id' => $this->senderId,
                'message' => $this->messageText,
                'reply' => $reply,
            ]);
        });
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
}
