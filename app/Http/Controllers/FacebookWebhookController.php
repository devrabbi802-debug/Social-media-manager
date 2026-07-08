<?php

namespace App\Http\Controllers;

use App\Jobs\SendAiReplyJob;
use App\Models\Conversation;
use App\Models\FacebookSetting;
use App\Models\Message;
use App\Models\Tenant;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Http;
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

        $mid = $message['mid'] ?? null;

        if ($mid) {
            $exists = Message::where('facebook_mid', $mid)->exists();
            if ($exists) {
                Log::info('Duplicate webhook ignored', [
                    'tenant_id' => $tenant->id,
                    'sender_id' => $senderId,
                    'mid' => $mid,
                ]);
                return;
            }
        }

        $text = $message['text'] ?? null;
        $attachments = $message['attachments'] ?? [];
        $imageUrls = [];

        foreach ($attachments as $attachment) {
            if (($attachment['type'] ?? '') === 'image' && isset($attachment['payload']['url'])) {
                $imageUrls[] = $attachment['payload']['url'];
            }
        }

        if (! $text && empty($imageUrls)) {
            return;
        }

        Log::info('Facebook message received', [
            'tenant_id' => $tenant->id,
            'sender_id' => $senderId,
            'text' => $text,
            'image_count' => count($imageUrls),
            'mid' => $mid,
        ]);

        $conversation = Conversation::updateOrCreate(
            ['sender_id' => $senderId],
            ['last_message_at' => now()]
        );

        $recipientId = $event['recipient']['id'] ?? null;
        $facebookSetting = FacebookSetting::where('page_id', $recipientId)->first()
            ?? FacebookSetting::first();

        if (! $conversation->sender_name && $facebookSetting) {
            $name = $this->fetchSenderName($senderId, $facebookSetting->page_access_token);
            if ($name) {
                $conversation->update(['sender_name' => $name]);
            }
        }

        if (! empty($imageUrls)) {
            foreach ($imageUrls as $imageUrl) {
                try {
                    Message::create([
                        'conversation_id' => $conversation->id,
                        'direction' => 'incoming',
                        'type' => 'image',
                        'content' => 'ইমেজ পাঠিয়েছে',
                        'image_path' => $imageUrl,
                        'facebook_mid' => $mid,
                    ]);
                } catch (\Throwable $e) {
                    Log::error('Failed to save incoming image message', [
                        'sender_id' => $senderId,
                        'error' => $e->getMessage(),
                    ]);
                }
            }
        } elseif ($text) {
            try {
                Message::create([
                    'conversation_id' => $conversation->id,
                    'direction' => 'incoming',
                    'type' => 'text',
                    'content' => $text,
                    'facebook_mid' => $mid,
                ]);
            } catch (\Throwable $e) {
                Log::error('Failed to save incoming message', [
                    'sender_id' => $senderId,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        if (! $facebookSetting) {
            Log::warning('No Facebook setting found for tenant', ['tenant_id' => $tenant->id]);
            return;
        }

        $aiEnabled = $facebookSetting->ai_auto_reply_enabled ?? true;

        if (! $aiEnabled) {
            Log::info('AI auto-reply disabled for tenant', ['tenant_id' => $tenant->id]);
            return;
        }

        try {
            $finalImageUrls = $imageUrls;
            $finalText = $text;
            $delay = 0;

            if (! empty($imageUrls)) {
                // Image arrived - check for recent text (last 5 sec)
                $recentText = Message::where('conversation_id', $conversation->id)
                    ->where('direction', 'incoming')
                    ->where('type', 'text')
                    ->where('created_at', '>=', now()->subSeconds(5))
                    ->where('facebook_mid', '!=', $mid)
                    ->latest()
                    ->value('content');

                if ($recentText) {
                    $finalText = $recentText;
                    Log::info('Combined image with recent text', [
                        'sender_id' => $senderId,
                        'text' => $recentText,
                    ]);
                }
            } elseif ($text) {
                // Text only - check for recent images (last 5 sec)
                $recentImages = Message::where('conversation_id', $conversation->id)
                    ->where('direction', 'incoming')
                    ->where('type', 'image')
                    ->where('created_at', '>=', now()->subSeconds(5))
                    ->where('facebook_mid', '!=', $mid)
                    ->pluck('image_path')
                    ->toArray();

                if (!empty($recentImages)) {
                    $finalImageUrls = $recentImages;
                    Log::info('Combined text with recent images', [
                        'sender_id' => $senderId,
                        'image_count' => count($recentImages),
                    ]);
                } else {
                    $delay = 0;
                }
            }

            SendAiReplyJob::dispatch(
                tenantId: $tenant->id,
                senderId: $senderId,
                messageText: $finalText ?? '',
                pageAccessToken: $facebookSetting->page_access_token,
                imageUrls: $finalImageUrls,
            )->delay(now()->addSeconds($delay));

            Log::info('AI reply job dispatched', [
                'tenant_id' => $tenant->id,
                'sender_id' => $senderId,
                'image_count' => count($finalImageUrls),
                'delay' => $delay,
            ]);
        } catch (\Throwable $e) {
            Log::error('Failed to dispatch AI reply job', [
                'tenant_id' => $tenant->id,
                'error' => $e->getMessage(),
            ]);
        }
    }

    private function fetchSenderName(string $senderId, string $pageAccessToken): ?string
    {
        try {
            $response = Http::timeout(5)->get("https://graph.facebook.com/v21.0/{$senderId}", [
                'fields' => 'first_name,last_name',
                'access_token' => $pageAccessToken,
            ]);

            if ($response->failed()) {
                Log::warning('Facebook Graph API failed for sender name', [
                    'sender_id' => $senderId,
                    'status' => $response->status(),
                    'body' => $response->body(),
                ]);
                return null;
            }

            $data = $response->json();

            Log::info('Facebook Graph API sender name response', [
                'sender_id' => $senderId,
                'data' => $data,
            ]);

            $firstName = $data['first_name'] ?? '';
            $lastName = $data['last_name'] ?? '';

            $name = trim("{$firstName} {$lastName}");

            return $name !== '' ? $name : null;
        } catch (\Exception $e) {
            Log::warning('Failed to fetch sender name', [
                'sender_id' => $senderId,
                'error' => $e->getMessage(),
            ]);

            return null;
        }
    }
}
