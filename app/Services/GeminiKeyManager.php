<?php

namespace App\Services;

use App\Models\AiSetting;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class GeminiKeyManager
{
    private string $userId;

    private ?string $currentKeyId = null;

    public function __construct(string $userId)
    {
        $this->userId = $userId;
    }

    public function getActiveKeys()
    {
        return AiSetting::where('user_id', $this->userId)
            ->where('type', 'image')
            ->where('is_active', true)
            ->orderBy('priority', 'desc')
            ->get();
    }

    public function getNextKey(): ?AiSetting
    {
        $keys = $this->getActiveKeys();

        if ($keys->isEmpty()) {
            return null;
        }

        $lastUsedKeyId = Cache::get("gemini_last_used_{$this->userId}");
        $currentIndex = $keys->search(fn ($key) => $key->id === $lastUsedKeyId);

        if ($currentIndex === false || $currentIndex === null) {
            $currentIndex = 0;
        } else {
            $currentIndex = ($currentIndex + 1) % $keys->count();
        }

        $key = $keys[$currentIndex];
        $this->currentKeyId = $key->id;

        Cache::put("gemini_last_used_{$this->userId}", $key->id, 60);

        return $key;
    }

    public function markKeyRateLimited(int $keyId, int $retryAfterSeconds = 60): void
    {
        Cache::put(
            "gemini_rate_limited_{$this->userId}_{$keyId}",
            true,
            $retryAfterSeconds
        );

        Log::warning('Gemini key marked as rate limited', [
            'user_id' => $this->userId,
            'key_id' => $keyId,
            'retry_after' => $retryAfterSeconds,
        ]);
    }

    public function isKeyRateLimited(int $keyId): bool
    {
        return Cache::has("gemini_rate_limited_{$this->userId}_{$keyId}");
    }

    public function getAvailableKey(): ?AiSetting
    {
        $keys = $this->getActiveKeys();

        if ($keys->isEmpty()) {
            return null;
        }

        foreach ($keys as $key) {
            if (! $this->isKeyRateLimited($key->id)) {
                return $key;
            }
        }

        return null;
    }

    public function getKeyHealth(): array
    {
        $keys = $this->getActiveKeys();
        $health = [];

        foreach ($keys as $key) {
            $rateLimited = $this->isKeyRateLimited($key->id);
            $lastUsed = Cache::get("gemini_last_used_{$this->userId}");
            $isCurrent = $lastUsed === $key->id;

            $health[] = [
                'id' => $key->id,
                'is_active' => $key->is_active,
                'is_rate_limited' => $rateLimited,
                'is_current' => $isCurrent,
                'priority' => $key->priority,
                'masked_key' => substr($key->api_key, 0, 8) . '...' . substr($key->api_key, -4),
            ];
        }

        return $health;
    }

    public function getAvailableKeysCount(): int
    {
        return $this->getActiveKeys()
            ->filter(fn ($key) => ! $this->isKeyRateLimited($key->id))
            ->count();
    }

    public function getBestKeyForBatch(int $batchSize): ?AiSetting
    {
        $availableKey = $this->getAvailableKey();

        if ($availableKey) {
            return $availableKey;
        }

        Log::warning('All Gemini keys rate limited, using priority key', [
            'user_id' => $this->userId,
        ]);

        return $this->getActiveKeys()->first();
    }

    public static function resetAllKeys(string $userId): void
    {
        $keys = AiSetting::where('user_id', $userId)
            ->where('type', 'image')
            ->get();

        foreach ($keys as $key) {
            Cache::forget("gemini_rate_limited_{$userId}_{$key->id}");
        }

        Cache::forget("gemini_last_used_{$userId}");

        Log::info('All Gemini keys reset for user', ['user_id' => $userId]);
    }

    public function getKeyStats(): array
    {
        $keys = $this->getActiveKeys();
        $total = $keys->count();
        $available = $this->getAvailableKeysCount();
        $rateLimited = $total - $available;

        return [
            'total' => $total,
            'available' => $available,
            'rate_limited' => $rateLimited,
            'requests_per_minute' => $total * 15,
            'requests_per_day' => $total * 1500,
            'images_per_minute' => $total * 15,
            'estimated_images_per_day' => $total * 1500,
        ];
    }
}
