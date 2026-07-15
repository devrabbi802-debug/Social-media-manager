<?php

namespace App\Services;

use App\Models\AiSetting;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class AudioTranscriptionService
{
    private const GROQ_WHISPER_URL = 'https://api.groq.com/openai/v1/audio/transcriptions';

    private const SUPPORTED_FORMATS = ['mp3', 'mp4', 'mpga', 'm4a', 'wav', 'ogg', 'webm'];

    private const MAX_FILE_SIZE = 25 * 1024 * 1024; // 25MB Groq limit

    /**
     * Transcribe audio file using Groq Whisper API with key rotation.
     *
     * @param  string  $audioPath  Local file path or URL
     * @param  int  $userId  User ID to find AI keys
     * @return string|null Transcribed text or null on failure
     */
    public function transcribe(string $audioPath, int $userId): ?string
    {
        $keys = AiSetting::where('user_id', $userId)
            ->active()
            ->byType('message')
            ->byPriority()
            ->get();

        if ($keys->isEmpty()) {
            Log::warning('AudioTranscriptionService: no Groq keys available', ['user_id' => $userId]);

            return null;
        }

        // Download if URL
        $localPath = $this->ensureLocalFile($audioPath);

        if (! $localPath) {
            Log::error('AudioTranscriptionService: failed to prepare audio file', ['audio_path' => $audioPath]);

            return null;
        }

        try {
            $fileSize = filesize($localPath);

            if ($fileSize > self::MAX_FILE_SIZE) {
                Log::warning('AudioTranscriptionService: file too large', [
                    'size' => $fileSize,
                    'max' => self::MAX_FILE_SIZE,
                ]);

                return null;
            }

            // Try each key until one works
            foreach ($keys as $key) {
                $result = $this->callWhisperApi($localPath, $key->api_key);

                if ($result !== null) {
                    Log::info('AudioTranscriptionService: transcription succeeded', [
                        'key_id' => $key->id,
                        'text_length' => mb_strlen($result),
                    ]);

                    return $result;
                }

                Log::warning('AudioTranscriptionService: key failed, trying next', [
                    'key_id' => $key->id,
                ]);
            }

            Log::error('AudioTranscriptionService: all keys exhausted', ['user_id' => $userId]);

            return null;
        } finally {
            // Clean up temp file if we downloaded it
            if ($localPath !== $audioPath && file_exists($localPath)) {
                @unlink($localPath);
            }
        }
    }

    private function callWhisperApi(string $filePath, string $apiKey): ?string
    {
        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer '.$apiKey,
            ])
                ->attach('file', file_get_contents($filePath), basename($filePath))
                ->timeout(30)
                ->post(self::GROQ_WHISPER_URL, [
                    'model' => 'whisper-large-v3-turbo',
                    'language' => 'bn',
                    'response_format' => 'json',
                ]);

            if ($response->status() === 429) {
                Log::warning('AudioTranscriptionService: 429 rate limited');

                return null;
            }

            if ($response->failed()) {
                Log::error('AudioTranscriptionService: API error', [
                    'status' => $response->status(),
                    'body' => $response->body(),
                ]);

                return null;
            }

            $body = $response->json();

            return $body['text'] ?? null;
        } catch (\Exception $e) {
            Log::error('AudioTranscriptionService: exception', [
                'message' => $e->getMessage(),
            ]);

            return null;
        }
    }

    private function ensureLocalFile(string $pathOrUrl): ?string
    {
        // Already local file
        if (file_exists($pathOrUrl)) {
            return $pathOrUrl;
        }

        // URL - download to temp
        if (str_starts_with($pathOrUrl, 'http')) {
            try {
                $response = Http::timeout(30)->get($pathOrUrl);

                if ($response->failed()) {
                    return null;
                }

                $ext = $this->guessExtension($pathOrUrl, $response->header('Content-Type'));
                $tempPath = storage_path('app/temp/audio_'.uniqid().".{$ext}");

                // Ensure directory exists
                $dir = dirname($tempPath);
                if (! is_dir($dir)) {
                    mkdir($dir, 0755, true);
                }

                file_put_contents($tempPath, $response->body());

                return $tempPath;
            } catch (\Exception $e) {
                Log::error('AudioTranscriptionService: download failed', [
                    'url' => $pathOrUrl,
                    'error' => $e->getMessage(),
                ]);

                return null;
            }
        }

        return null;
    }

    private function guessExtension(string $url, ?string $contentType): string
    {
        // Try URL extension first
        $path = parse_url($url, PHP_URL_PATH);
        if ($path) {
            $ext = strtolower(pathinfo($path, PATHINFO_EXTENSION));
            if (in_array($ext, self::SUPPORTED_FORMATS)) {
                return $ext;
            }
        }

        // Try content type
        if ($contentType) {
            $map = [
                'audio/mpeg' => 'mp3',
                'audio/mp3' => 'mp3',
                'audio/mp4' => 'm4a',
                'audio/x-m4a' => 'm4a',
                'audio/ogg' => 'ogg',
                'audio/wav' => 'wav',
                'audio/webm' => 'webm',
            ];

            foreach ($map as $mime => $ext) {
                if (str_contains($contentType, $mime)) {
                    return $ext;
                }
            }
        }

        return 'mp3'; // default
    }
}
