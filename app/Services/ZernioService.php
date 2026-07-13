<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class ZernioService
{
    private string $apiKey;

    private string $baseUrl;

    public function __construct(string $apiKey)
    {
        $this->apiKey = $apiKey;
        $this->baseUrl = config('services.zernio.base_url', 'https://zernio.com/api/v1');
    }

    private function headers(): array
    {
        return [
            'Authorization' => "Bearer {$this->apiKey}",
            'Content-Type' => 'application/json',
            'Accept' => 'application/json',
        ];
    }

    /**
     * Verify API key — check if it's valid by listing accounts.
     */
    public function verifyKey(): bool
    {
        try {
            $response = Http::withHeaders($this->headers())
                ->timeout(15)
                ->get("{$this->baseUrl}/accounts");

            return $response->successful();
        } catch (\Exception $e) {
            Log::error('Zernio API key verification failed', ['error' => $e->getMessage()]);

            return false;
        }
    }

    /**
     * Create a new profile in Zernio.
     */
    public function createProfile(string $name, string $description = ''): ?array
    {
        try {
            $response = Http::withHeaders($this->headers())
                ->timeout(15)
                ->post("{$this->baseUrl}/profiles", [
                    'name' => $name,
                    'description' => $description,
                ]);

            if ($response->successful()) {
                return $response->json('profile');
            }

            Log::error('Zernio create profile failed', ['response' => $response->json()]);

            return null;
        } catch (\Exception $e) {
            Log::error('Zernio create profile error', ['error' => $e->getMessage()]);

            return null;
        }
    }

    /**
     * List all profiles.
     */
    public function listProfiles(): array
    {
        try {
            $response = Http::withHeaders($this->headers())
                ->timeout(15)
                ->get("{$this->baseUrl}/profiles");

            if ($response->successful()) {
                return $response->json('profiles', []);
            }

            return [];
        } catch (\Exception $e) {
            Log::error('Zernio list profiles error', ['error' => $e->getMessage()]);

            return [];
        }
    }

    /**
     * Get Facebook OAuth connect URL from Zernio.
     * User will open this URL in browser to authorize Facebook.
     */
    public function getFacebookConnectUrl(string $profileId, ?string $redirectUrl = null): ?array
    {
        try {
            $params = ['profileId' => $profileId];
            if ($redirectUrl) {
                $params['redirect_url'] = $redirectUrl;
            }

            $response = Http::withHeaders($this->headers())
                ->timeout(15)
                ->get("{$this->baseUrl}/connect/facebook", $params);

            if ($response->successful()) {
                return $response->json();
            }

            Log::error('Zernio Facebook connect URL failed', ['response' => $response->json()]);

            return null;
        } catch (\Exception $e) {
            Log::error('Zernio Facebook connect URL error', ['error' => $e->getMessage()]);

            return null;
        }
    }

    /**
     * Get Facebook pages available for selection via tempToken.
     * Called after OAuth callback — Zernio returns pages list.
     */
    public function getFacebookSelectPageUrl(string $profileId, string $tempToken): ?array
    {
        try {
            $response = Http::withHeaders($this->headers())
                ->timeout(15)
                ->get("{$this->baseUrl}/connect/facebook/select-page", [
                    'profileId' => $profileId,
                    'tempToken' => $tempToken,
                ]);

            if ($response->successful()) {
                return $response->json();
            }

            Log::error('Zernio Facebook select-page failed', [
                'response' => $response->json(),
                'status' => $response->status(),
            ]);

            return null;
        } catch (\Exception $e) {
            Log::error('Zernio Facebook select-page error', ['error' => $e->getMessage()]);

            return null;
        }
    }

    /**
     * Select a Facebook page after OAuth authorization.
     */
    public function selectFacebookPage(string $profileId, string $pageId, string $tempToken, ?string $redirectUrl = null): ?array
    {
        try {
            $body = [
                'profileId' => $profileId,
                'pageId' => $pageId,
                'tempToken' => $tempToken,
            ];
            if ($redirectUrl) {
                $body['redirect_url'] = $redirectUrl;
            }

            $response = Http::withHeaders($this->headers())
                ->timeout(15)
                ->post("{$this->baseUrl}/connect/facebook/select-page", $body);

            if ($response->successful()) {
                return $response->json();
            }

            Log::error('Zernio select Facebook page failed', ['response' => $response->json()]);

            return null;
        } catch (\Exception $e) {
            Log::error('Zernio select Facebook page error', ['error' => $e->getMessage()]);

            return null;
        }
    }

    /**
     * List connected social accounts.
     */
    public function listAccounts(?string $profileId = null): array
    {
        try {
            $params = [];
            if ($profileId) {
                $params['profileId'] = $profileId;
            }

            $response = Http::withHeaders($this->headers())
                ->timeout(15)
                ->get("{$this->baseUrl}/accounts", $params);

            if ($response->successful()) {
                return $response->json('accounts', []);
            }

            return [];
        } catch (\Exception $e) {
            Log::error('Zernio list accounts error', ['error' => $e->getMessage()]);

            return [];
        }
    }

    /**
     * List Facebook pages for a connected account.
     */
    public function listFacebookPages(string $accountId): array
    {
        try {
            $response = Http::withHeaders($this->headers())
                ->timeout(15)
                ->get("{$this->baseUrl}/accounts/{$accountId}/facebook-page");

            if ($response->successful()) {
                return $response->json('pages', $response->json() ?? []);
            }

            return [];
        } catch (\Exception $e) {
            Log::error('Zernio list Facebook pages error', ['error' => $e->getMessage()]);

            return [];
        }
    }

    /**
     * Set default Facebook page for an account.
     */
    public function setDefaultFacebookPage(string $accountId, string $pageId): bool
    {
        try {
            $response = Http::withHeaders($this->headers())
                ->timeout(15)
                ->put("{$this->baseUrl}/accounts/{$accountId}/facebook-page", [
                    'selectedPageId' => $pageId,
                ]);

            return $response->successful();
        } catch (\Exception $e) {
            Log::error('Zernio set default Facebook page error', ['error' => $e->getMessage()]);

            return false;
        }
    }

    /**
     * List inbox conversations.
     */
    public function listConversations(string $accountId, int $limit = 20): array
    {
        try {
            $response = Http::withHeaders($this->headers())
                ->timeout(15)
                ->get("{$this->baseUrl}/inbox/conversations", [
                    'accountId' => $accountId,
                    'limit' => $limit,
                ]);

            if ($response->successful()) {
                return $response->json('conversations', []);
            }

            return [];
        } catch (\Exception $e) {
            Log::error('Zernio list conversations error', ['error' => $e->getMessage()]);

            return [];
        }
    }

    /**
     * Get messages in a conversation.
     */
    public function getConversationMessages(string $conversationId, int $limit = 50): array
    {
        try {
            $response = Http::withHeaders($this->headers())
                ->timeout(15)
                ->get("{$this->baseUrl}/inbox/conversations/{$conversationId}/messages", [
                    'limit' => $limit,
                ]);

            if ($response->successful()) {
                return $response->json('messages', []);
            }

            return [];
        } catch (\Exception $e) {
            Log::error('Zernio get conversation messages error', ['error' => $e->getMessage()]);

            return [];
        }
    }

    /**
     * Send a message in an inbox conversation (reply to customer).
     */
    public function sendInboxMessage(string $conversationId, string $accountId, string $message): ?array
    {
        try {
            $response = Http::withHeaders($this->headers())
                ->timeout(30)
                ->post("{$this->baseUrl}/inbox/conversations/{$conversationId}/messages", [
                    'accountId' => $accountId,
                    'message' => $message,
                ]);

            if ($response->successful()) {
                return $response->json();
            }

            Log::error('Zernio send inbox message failed', [
                'conversation_id' => $conversationId,
                'response' => $response->json(),
            ]);

            return null;
        } catch (\Exception $e) {
            Log::error('Zernio send inbox message error', ['error' => $e->getMessage()]);

            return null;
        }
    }

    /**
     * Send a typing indicator.
     */
    public function sendTypingIndicator(string $conversationId, string $accountId): void
    {
        try {
            Http::withHeaders($this->headers())
                ->timeout(10)
                ->post("{$this->baseUrl}/inbox/conversations/{$conversationId}/typing", [
                    'accountId' => $accountId,
                ]);
        } catch (\Exception $e) {
            // Typing indicator failure is non-critical
            Log::debug('Zernio typing indicator failed', ['error' => $e->getMessage()]);
        }
    }

    /**
     * Disconnect a social account.
     */
    public function disconnectAccount(string $accountId): bool
    {
        try {
            $response = Http::withHeaders($this->headers())
                ->timeout(15)
                ->delete("{$this->baseUrl}/accounts/{$accountId}");

            return $response->successful();
        } catch (\Exception $e) {
            Log::error('Zernio disconnect account error', ['error' => $e->getMessage()]);

            return false;
        }
    }

    /**
     * Create a post (for future use — scheduling, publishing).
     */
    public function createPost(array $data): ?array
    {
        try {
            $response = Http::withHeaders($this->headers())
                ->timeout(30)
                ->post("{$this->baseUrl}/posts", $data);

            if ($response->successful()) {
                return $response->json('post');
            }

            Log::error('Zernio create post failed', ['response' => $response->json()]);

            return null;
        } catch (\Exception $e) {
            Log::error('Zernio create post error', ['error' => $e->getMessage()]);

            return null;
        }
    }
}
