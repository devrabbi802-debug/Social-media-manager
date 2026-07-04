<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class GeminiApiService
{
    private string $apiKey;

    private string $model;

    public function __construct(string $apiKey, ?string $model = null)
    {
        $this->apiKey = $apiKey;
        $this->model = $model ?? config('services.gemini.model', 'gemini-2.5-flash');
    }

    public function analyzeImage(string $imageBase64, string $prompt, ?string $systemPrompt = null): ?string
    {
        try {
            $parts = [
                ['text' => $systemPrompt ?? 'তুমি একজন পেশাদার সেলস ম্যানেজার এবং কাস্টমার সাপোর্ট এজেন্ট। বাংলায় উত্তর দাও।'],
                [
                    'inlineData' => [
                        'mimeType' => 'image/jpeg',
                        'data' => $imageBase64,
                    ],
                ],
                ['text' => $prompt],
            ];

            $response = Http::withHeaders([
                'Content-Type' => 'application/json',
            ])->timeout(60)->post(
                "https://generativelanguage.googleapis.com/v1beta/models/{$this->model}:generateContent?key={$this->apiKey}",
                [
                    'contents' => [
                        [
                            'parts' => $parts,
                        ],
                    ],
                ]
            );

            if ($response->status() === 429) {
                $retryAfter = $response->header('Retry-After', 60);
                throw new \Exception("Gemini API rate limited (429). Retry after: {$retryAfter}s");
            }

            if ($response->status() === 503) {
                throw new \Exception('Gemini API overloaded (503)');
            }

            if ($response->status() === 403) {
                throw new \Exception('Gemini API key forbidden (403)');
            }

            if ($response->failed()) {
                Log::error('Gemini image analysis error', [
                    'status' => $response->status(),
                    'body' => $response->body(),
                ]);
                return null;
            }

            $body = $response->json();
            $candidates = $body['candidates'] ?? [];

            if (empty($candidates)) {
                return null;
            }

            $parts = $candidates[0]['content']['parts'] ?? [];

            foreach ($parts as $part) {
                if (isset($part['text'])) {
                    return $part['text'];
                }
            }

            return null;
        } catch (\Exception $e) {
            if (str_contains($e->getMessage(), '429') || str_contains($e->getMessage(), '503')) {
                throw $e;
            }

            Log::error('Gemini image analysis exception', [
                'message' => $e->getMessage(),
            ]);

            return null;
        }
    }

    public function generateImage(string $prompt): ?string
    {
        try {
            $response = Http::withHeaders([
                'Content-Type' => 'application/json',
            ])->timeout(60)->post(
                "https://generativelanguage.googleapis.com/v1beta/models/{$this->model}:generateContent?key={$this->apiKey}",
                [
                    'contents' => [
                        [
                            'parts' => [
                                ['text' => $prompt],
                            ],
                        ],
                    ],
                    'generationConfig' => [
                        'responseModalities' => ['TEXT', 'IMAGE'],
                    ],
                ]
            );

            if ($response->status() === 429) {
                throw new \Exception('Gemini API rate limited (429)');
            }

            if ($response->failed()) {
                Log::error('Gemini API error', [
                    'status' => $response->status(),
                    'body' => $response->body(),
                ]);
                return null;
            }

            $body = $response->json();
            $candidates = $body['candidates'] ?? [];

            if (empty($candidates)) {
                return null;
            }

            $parts = $candidates[0]['content']['parts'] ?? [];

            foreach ($parts as $part) {
                if (isset($part['inlineData']['data'])) {
                    return $part['inlineData']['data'];
                }
            }

            return null;
        } catch (\Exception $e) {
            if (str_contains($e->getMessage(), '429')) {
                throw $e;
            }

            Log::error('Gemini API exception', [
                'message' => $e->getMessage(),
            ]);

            return null;
        }
    }

    public static function testConnection(string $apiKey): array
    {
        try {
            $response = Http::withHeaders([
                'Content-Type' => 'application/json',
            ])->timeout(15)->post(
                "https://generativelanguage.googleapis.com/v1beta/models/gemini-2.5-flash:generateContent?key={$apiKey}",
                [
                    'contents' => [
                        [
                            'parts' => [
                                ['text' => 'Hello, just testing connection. Reply with one word.'],
                            ],
                        ],
                    ],
                ]
            );

            if ($response->status() === 401 || $response->status() === 403) {
                return ['success' => false, 'message' => 'API key invalid'];
            }

            if ($response->status() === 429) {
                return ['success' => true, 'message' => 'Connected! (Rate limited but key is valid)'];
            }

            if ($response->failed()) {
                return ['success' => false, 'message' => 'API error: ' . $response->status()];
            }

            $body = $response->json();
            $text = $body['candidates'][0]['content']['parts'][0]['text'] ?? null;

            return ['success' => true, 'message' => 'Connected! Gemini replied: ' . substr($text ?? '', 0, 50)];
        } catch (\Exception $e) {
            return ['success' => false, 'message' => 'Error: ' . $e->getMessage()];
        }
    }

    public function getModelInfo(): array
    {
        return [
            'model' => $this->model,
            'rate_limit' => [
                'rpm' => 15,
                'rpd' => 1500,
            ],
            'timeout' => 60,
        ];
    }
}
