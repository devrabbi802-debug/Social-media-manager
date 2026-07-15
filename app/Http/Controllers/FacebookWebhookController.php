<?php

namespace App\Http\Controllers;

use App\Jobs\SendAiReplyJob;
use App\Models\Conversation;
use App\Models\FacebookSetting;
use App\Models\Message;
use App\Models\Tenant;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class FacebookWebhookController extends Controller
{
    /**
     * Facebook webhook verification (for direct Facebook App connections).
     */
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

    /**
     * Handle Facebook webhook (for direct Facebook App connections).
     */
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

    /**
     * Handle Zernio webhook (for Zernio-connected accounts).
     * Zernio sends events like: message.received, message.sent, conversation.started, etc.
     */
    public function handleZernio(Request $request): Response
    {
        $payload = $request->all();
        $eventName = $payload['event'] ?? $payload['type'] ?? null;
        $eventId = $payload['id'] ?? $request->header('X-Zernio-Event-Id', null);

        Log::info('Zernio webhook received', [
            'event' => $eventName,
            'event_id' => $eventId,
        ]);

        // Verify signature if secret is configured
        $this->verifyZernioSignature($request);

        // Handle different event types
        match ($eventName) {
            'message.received' => $this->handleZernioMessageReceived($payload),
            'conversation.started' => $this->handleZernioConversationStarted($payload),
            'account.connected' => $this->handleZernioAccountConnected($payload),
            'account.disconnected' => $this->handleZernioAccountDisconnected($payload),
            default => Log::info('Zernio webhook: unhandled event', ['event' => $eventName]),
        };

        return response('OK', 200);
    }

    /**
     * Handle incoming message from Zernio webhook.
     */
    private function handleZernioMessageReceived(array $payload): void
    {
        $data = $payload['data'] ?? $payload;
        $messageData = $data['message'] ?? null;
        $accountData = $data['account'] ?? null;
        $senderData = $messageData['sender'] ?? null;

        // Zernio payload: accountId is in data.account.id, message text in data.message.text
        $accountId = $accountData['id'] ?? $data['accountId'] ?? null;
        $conversationId = $data['conversation']['id'] ?? $messageData['conversationId'] ?? null;
        $messageText = is_string($messageData) ? $messageData : ($messageData['text'] ?? $data['text'] ?? null);
        $senderId = $senderData['contactId'] ?? $senderData['id'] ?? $data['senderId'] ?? null;
        $senderName = $senderData['name'] ?? $data['conversation']['participantName'] ?? null;
        $messageType = $messageData['type'] ?? $data['type'] ?? 'text';
        $attachments = $messageData['attachments'] ?? $data['attachments'] ?? $data['media'] ?? [];

        if (! $accountId) {
            Log::warning('Zernio webhook: missing accountId', ['data' => $data]);

            return;
        }

        // Allow image-only messages (messageText can be null)
        if (! $messageText && empty($attachments)) {
            Log::warning('Zernio webhook: no message text and no attachments', ['data' => $data]);

            return;
        }

        // Find tenant by Zernio account ID
        $tenant = $this->findTenantByZernioAccountId($accountId);

        if (! $tenant) {
            Log::warning('Zernio webhook: no tenant for account', ['account_id' => $accountId]);

            return;
        }

        $tenant->run(function () use ($tenant, $data, $accountId, $messageText, $senderId, $senderName, $conversationId, $attachments) {
            // Extract image and audio URLs from attachments
            $imageUrls = [];
            $audioUrl = null;
            if (is_array($attachments)) {
                foreach ($attachments as $attachment) {
                    $url = $attachment['url'] ?? null;
                    $type = $attachment['type'] ?? '';

                    if (! $url) {
                        continue;
                    }

                    if (str_starts_with($type, 'image')) {
                        $imageUrls[] = $url;
                    } elseif (str_starts_with($type, 'audio')) {
                        $audioUrl = $url;
                    }
                }
            }

            // Get or create conversation
            $conversation = Conversation::updateOrCreate(
                ['sender_id' => $senderId],
                ['last_message_at' => now()]
            );

            // Fetch sender name if not set
            if (! $conversation->sender_name && $senderName) {
                $conversation->update(['sender_name' => $senderName]);
            }

            // Save incoming message(s)
            $messageId = $data['message']['id'] ?? $data['messageId'] ?? null;
            if (! empty($imageUrls)) {
                foreach ($imageUrls as $imageUrl) {
                    try {
                        Message::create([
                            'conversation_id' => $conversation->id,
                            'direction' => 'incoming',
                            'type' => 'image',
                            'content' => 'ইমেজ পাঠিয়েছে',
                            'image_path' => $imageUrl,
                            'facebook_mid' => $messageId,
                        ]);
                    } catch (\Throwable $e) {
                        Log::error('Zernio: Failed to save incoming image', ['error' => $e->getMessage()]);
                    }
                }
            } elseif ($audioUrl) {
                try {
                    Message::create([
                        'conversation_id' => $conversation->id,
                        'direction' => 'incoming',
                        'type' => 'audio',
                        'content' => 'ভয়েস মেসেজ পাঠিয়েছে',
                        'audio_path' => $audioUrl,
                        'facebook_mid' => $messageId,
                    ]);
                } catch (\Throwable $e) {
                    Log::error('Zernio: Failed to save incoming audio', ['error' => $e->getMessage()]);
                }
            } elseif ($messageText) {
                try {
                    Message::create([
                        'conversation_id' => $conversation->id,
                        'direction' => 'incoming',
                        'type' => 'text',
                        'content' => $messageText,
                        'facebook_mid' => $messageId,
                    ]);
                } catch (\Throwable $e) {
                    Log::error('Zernio: Failed to save incoming message', ['error' => $e->getMessage()]);
                }
            }

            // Check if AI auto-reply is enabled
            $facebookSetting = FacebookSetting::where('connection_type', 'zernio')
                ->where('zernio_account_id', $accountId)
                ->first();

            if (! $facebookSetting) {
                Log::warning('Zernio: No Facebook setting found', ['account_id' => $accountId]);

                return;
            }

            $aiEnabled = $facebookSetting->ai_auto_reply_enabled ?? true;
            if (! $aiEnabled) {
                Log::info('Zernio: AI auto-reply disabled', ['account_id' => $accountId]);

                return;
            }

            // Debounce: skip if already dispatched recently
            $dispatchKey = "zernio_job_dispatched:{$senderId}";
            if (Cache::get($dispatchKey)) {
                Log::info('Zernio webhook debounce: skipping duplicate dispatch', ['sender_id' => $senderId]);

                return;
            }

            // Combine text + images (same logic as Facebook webhook)
            $finalImageUrls = $imageUrls;
            $finalText = $messageText;

            if (! empty($imageUrls) && $messageText) {
                // Text + image together — good
            } elseif (! empty($imageUrls)) {
                // Image only — check for recent text
                $recentText = Message::where('conversation_id', $conversation->id)
                    ->where('direction', 'incoming')
                    ->where('type', 'text')
                    ->where('created_at', '>=', now()->subSeconds(5))
                    ->latest()
                    ->value('content');
                if ($recentText) {
                    $finalText = $recentText;
                }
            } elseif ($messageText) {
                // Text only — check for recent images
                $recentImages = Message::where('conversation_id', $conversation->id)
                    ->where('direction', 'incoming')
                    ->where('type', 'image')
                    ->where('created_at', '>=', now()->subSeconds(5))
                    ->pluck('image_path')
                    ->toArray();
                if (! empty($recentImages)) {
                    $finalImageUrls = $recentImages;
                }
            }

            Cache::put($dispatchKey, true, now()->addSeconds(8));

            // Dispatch AI reply job
            SendAiReplyJob::dispatch(
                tenantId: $tenant->id,
                senderId: $senderId,
                messageText: $finalText ?? '',
                pageAccessToken: $facebookSetting->page_access_token ?? '',
                imageUrls: $finalImageUrls,
                audioUrl: $audioUrl,
                zernioAccountId: $facebookSetting->zernio_account_id,
                zernioApiKey: $facebookSetting->zernio_api_key,
                zernioConversationId: $conversationId,
            )->delay(now()->addSeconds(0));

            Log::info('Zernio: AI reply job dispatched', [
                'tenant_id' => $tenant->id,
                'sender_id' => $senderId,
            ]);
        });
    }

    private function handleZernioConversationStarted(array $payload): void
    {
        Log::info('Zernio: conversation started', ['payload' => $payload]);
    }

    private function handleZernioAccountConnected(array $payload): void
    {
        Log::info('Zernio: account connected', ['payload' => $payload]);
    }

    private function handleZernioAccountDisconnected(array $payload): void
    {
        $accountId = $payload['data']['accountId'] ?? $payload['accountId'] ?? null;

        if ($accountId) {
            $facebookSetting = FacebookSetting::where('zernio_account_id', $accountId)->first();
            if ($facebookSetting) {
                $facebookSetting->update([
                    'zernio_account_id' => null,
                    'page_id' => null,
                    'page_name' => null,
                ]);
                Log::info('Zernio: account disconnected, settings cleared', ['account_id' => $accountId]);
            }
        }
    }

    private function verifyZernioSignature(Request $request): void
    {
        // Optional: verify X-Zernio-Signature header if webhook secret is configured
        // For now, we'll skip signature verification
    }

    // --- Helper methods (existing) ---

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

    /**
     * Find tenant by Zernio account ID.
     */
    private function findTenantByZernioAccountId(string $accountId): ?Tenant
    {
        return Tenant::all()->first(function (Tenant $tenant) use ($accountId) {
            return $tenant->run(function () use ($accountId) {
                return FacebookSetting::where('zernio_account_id', $accountId)->exists();
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
        $audioUrl = null;

        foreach ($attachments as $attachment) {
            $type = $attachment['type'] ?? '';
            $url = $attachment['payload']['url'] ?? null;

            if (! $url) {
                continue;
            }

            if ($type === 'image') {
                $imageUrls[] = $url;
            } elseif ($type === 'audio') {
                $audioUrl = $url;
            }
        }

        if (! $text && empty($imageUrls) && ! $audioUrl) {
            return;
        }

        Log::info('Facebook message received', [
            'tenant_id' => $tenant->id,
            'sender_id' => $senderId,
            'text' => $text,
            'image_count' => count($imageUrls),
            'has_audio' => $audioUrl !== null,
            'mid' => $mid,
        ]);

        $conversation = Conversation::updateOrCreate(
            ['sender_id' => $senderId],
            ['last_message_at' => now()]
        );

        $recipientId = $event['recipient']['id'] ?? null;
        $facebookSetting = FacebookSetting::where('page_id', $recipientId)->first()
            ?? FacebookSetting::first();

        if (! $conversation->sender_name && $facebookSetting && $facebookSetting->page_access_token) {
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
        } elseif ($audioUrl) {
            try {
                Message::create([
                    'conversation_id' => $conversation->id,
                    'direction' => 'incoming',
                    'type' => 'audio',
                    'content' => 'ভয়েস মেসেজ পাঠিয়েছে',
                    'audio_path' => $audioUrl,
                    'facebook_mid' => $mid,
                ]);
            } catch (\Throwable $e) {
                Log::error('Failed to save incoming audio message', [
                    'sender_id' => $senderId,
                    'error' => $e->getMessage(),
                ]);
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

            $dispatchKey = "job_dispatched:{$senderId}";
            $alreadyDispatched = Cache::get($dispatchKey);

            if ($alreadyDispatched) {
                Log::info('Webhook debounce: skipping duplicate dispatch', [
                    'sender_id' => $senderId,
                    'has_images' => ! empty($imageUrls),
                ]);

                return;
            }

            if (! empty($imageUrls)) {
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
                $recentImages = Message::where('conversation_id', $conversation->id)
                    ->where('direction', 'incoming')
                    ->where('type', 'image')
                    ->where('created_at', '>=', now()->subSeconds(5))
                    ->where('facebook_mid', '!=', $mid)
                    ->pluck('image_path')
                    ->toArray();

                if (! empty($recentImages)) {
                    $finalImageUrls = $recentImages;
                    Log::info('Combined text with recent images', [
                        'sender_id' => $senderId,
                        'image_count' => count($recentImages),
                    ]);
                }
            }

            Cache::put($dispatchKey, true, now()->addSeconds(8));

            SendAiReplyJob::dispatch(
                tenantId: $tenant->id,
                senderId: $senderId,
                messageText: $finalText ?? '',
                pageAccessToken: $facebookSetting->page_access_token,
                imageUrls: $finalImageUrls,
                audioUrl: $audioUrl,
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
