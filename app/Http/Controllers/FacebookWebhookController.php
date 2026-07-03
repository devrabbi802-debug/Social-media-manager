<?php

namespace App\Http\Controllers;

use App\Jobs\SendAiReplyJob;
use App\Models\FacebookSetting;
use App\Models\Tenant;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;

class FacebookWebhookController extends Controller
{
    public function verify(Request $request): Response
    {
        $mode = $request->query('hub_mode');
        $token = $request->query('hub_verify_token');
        $challenge = $request->query('hub_challenge');

        if ($mode !== 'subscribe') {
            return response('Invalid mode', 403);
        }

        $tenant = $this->findTenantByVerifyToken($token);

        if (! $tenant) {
            Log::warning('Facebook webhook verify failed: invalid token', ['token' => $token]);

            return response('Invalid verify token', 403);
        }

        Log::info('Facebook webhook verified', ['tenant_id' => $tenant->id]);

        return response($challenge, 200)->header('Content-Type', 'text/plain');
    }

    public function handle(Request $request): Response
    {
        $payload = $request->all();

        Log::info('Facebook webhook received', [
            'object' => $payload['object'] ?? null,
            'entry_count' => count($payload['entry'] ?? []),
        ]);

        if (($payload['object'] ?? '') !== 'page') {
            return response('Not a page event', 404);
        }

        foreach ($payload['entry'] ?? [] as $entry) {
            $pageId = $entry['id'] ?? null;
            $tenant = $this->findTenantByPageId($pageId);

            if (! $tenant) {
                Log::warning('Facebook webhook: no tenant for page', ['page_id' => $pageId]);

                continue;
            }

            $tenant->run(function () use ($entry, $tenant) {
                foreach ($entry['messaging'] ?? [] as $event) {
                    $this->handleMessagingEvent($event, $tenant);
                }
            });
        }

        return response('EVENT_RECEIVED', 200);
    }

    private function findTenantByVerifyToken(string $token): ?Tenant
    {
        return Tenant::all()->first(function (Tenant $tenant) use ($token) {
            return $tenant->run(function () use ($token) {
                return FacebookSetting::where('verify_token', $token)->exists();
            });
        });
    }

    private function findTenantByPageId(?string $pageId): ?Tenant
    {
        if (! $pageId) {
            return null;
        }

        return Tenant::all()->first(function (Tenant $tenant) use ($pageId) {
            return $tenant->run(function () use ($pageId) {
                return FacebookSetting::where('page_id', $pageId)->exists();
            });
        });
    }

    private function handleMessagingEvent(array $event, Tenant $tenant): void
    {
        $senderId = $event['sender']['id'] ?? null;
        $message = $event['message'] ?? null;

        if (! $message || ! $senderId) {
            return;
        }

        $text = $message['text'] ?? null;
        $attachments = $message['attachments'] ?? [];
        $imageUrl = null;

        foreach ($attachments as $attachment) {
            if (($attachment['type'] ?? '') === 'image' && isset($attachment['payload']['url'])) {
                $imageUrl = $attachment['payload']['url'];
                break;
            }
        }

        if (! $text && ! $imageUrl) {
            return;
        }

        Log::info('Facebook message received', [
            'tenant_id' => $tenant->id,
            'sender_id' => $senderId,
            'text' => $text,
            'has_image' => $imageUrl !== null,
        ]);

        $recipientId = $event['recipient']['id'] ?? null;
        $facebookSetting = FacebookSetting::where('page_id', $recipientId)->first()
            ?? FacebookSetting::first();

        if (! $facebookSetting) {
            Log::warning('No Facebook setting found for tenant', ['tenant_id' => $tenant->id]);

            return;
        }

        try {
            SendAiReplyJob::dispatch(
                tenantId: $tenant->id,
                senderId: $senderId,
                messageText: $text ?? '',
                pageAccessToken: $facebookSetting->page_access_token,
                imageUrl: $imageUrl,
            );

            Log::info('AI reply job dispatched', [
                'tenant_id' => $tenant->id,
                'sender_id' => $senderId,
                'has_image' => $imageUrl !== null,
            ]);
        } catch (\Throwable $e) {
            Log::error('Failed to dispatch AI reply job', [
                'tenant_id' => $tenant->id,
                'error' => $e->getMessage(),
            ]);
        }
    }
}
