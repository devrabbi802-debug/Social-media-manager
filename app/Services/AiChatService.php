<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class AiChatService
{
    private string $systemPrompt;

    public function __construct(?string $systemPrompt = null)
    {
        $this->systemPrompt = $systemPrompt ?? $this->defaultPrompt();
    }

    public function chatWithRotation(string $message, $keys): ?string
    {
        return $this->chatWithHistory($message, $keys, []);
    }

    public function chatWithHistory(string $message, $keys, array $history = []): ?string
    {
        $lastException = null;
        $allRateLimited = true;

        foreach ($keys as $key) {
            try {
                $result = $this->chatWithMessages($message, $key->api_key, $history);

                if ($result !== null) {
                    return $result;
                }

                $allRateLimited = false;

                Log::warning('AI key returned null, trying next key', [
                    'key_label' => $key->label,
                    'key_id' => $key->id,
                ]);
            } catch (\Exception $e) {
                $lastException = $e;

                if (! str_contains($e->getMessage(), '429')) {
                    $allRateLimited = false;
                }

                Log::warning('AI key failed, trying next key', [
                    'key_label' => $key->label,
                    'key_id' => $key->id,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        Log::error('All AI keys exhausted or failed', [
            'keys_tried' => count($keys),
            'last_error' => $lastException?->getMessage(),
        ]);

        if ($allRateLimited && $lastException) {
            throw $lastException;
        }

        return null;
    }

    public function chat(string $message, string $apiKey): ?string
    {
        return $this->chatWithMessages($message, $apiKey, []);
    }

    public function chatWithMessages(string $message, string $apiKey, array $history = []): ?string
    {
        try {
            $messages = [
                ['role' => 'system', 'content' => $this->systemPrompt],
            ];

            foreach ($history as $msg) {
                $messages[] = [
                    'role' => $msg['role'],
                    'content' => $msg['content'],
                ];
            }

            $messages[] = ['role' => 'user', 'content' => $message];

            $response = Http::withHeaders([
                'Authorization' => 'Bearer '.$apiKey,
                'Content-Type' => 'application/json',
            ])->timeout(15)->post('https://api.groq.com/openai/v1/chat/completions', [
                'model' => config('services.groq.model', 'llama-3.3-70b-versatile'),
                'messages' => $messages,
            ]);

            if ($response->status() === 429) {
                $retryAfter = (int) $response->header('Retry-After', 30);
                Log::warning('Groq 429 rate limited', ['retry_after' => $retryAfter]);
                throw new \Exception('AI API rate limited (429)');
            }

            if ($response->status() === 413) {
                Log::error('Groq API request too large (413)', [
                    'message_length' => mb_strlen($message),
                ]);

                $truncatedMessage = mb_substr($message, 0, 4000) . "\n\n[বার্তা সংক্ষিপ্ত করা হয়েছে]";

                return $this->chatWithMessages($truncatedMessage, $apiKey, $history);
            }

            if ($response->failed()) {
                Log::error('Groq API error', [
                    'status' => $response->status(),
                    'body' => $response->body(),
                ]);

                return null;
            }

            $body = $response->json();

            return $body['choices'][0]['message']['content'] ?? null;
        } catch (\Exception $e) {
            if (str_contains($e->getMessage(), '429')) {
                throw $e;
            }
            Log::error('Groq API exception', [
                'message' => $e->getMessage(),
            ]);

            return null;
        }
    }

    public function defaultPrompt(): string
    {
        return <<<'PROMPT'
        তুমি একজন পেশাদার সেলস ম্যানেজার এবং কাস্টমার সাপোর্ট এজেন্ট।
        তোমার কাজ হলো কাস্টমারদের Facebook Messenger এ সাহায্য করা।

        গুরুত্বপূর্ণ: তোমার কাছে আগের কথোপকথনের ইতিহাস দেওয়া আছে। সেখানে প্রোডাক্টের নাম, মূল্য, স্টক, ক্যাটাগরি, ব্র্যান্ড সম্পর্কে তথ্য থাকতে পারে। কাস্টমার যদি আগে দেওয়া প্রোডাক্ট সম্পর্কে জিজ্ঞাসা করে (যেমন "last product er price koto?", "ওই প্রোডাক্টটার দাম কত?", "আগে যে ছবি দিলাম সেটার স্টক আছে?"), তাহলে ইতিহাস থেকে সঠিক তথ্য দিয়ে উত্তর দাও।

        নিয়মাবলী:
        - সবসময় বাংলায় কথা বলবে।
        - সংক্ষিপ্ত এবং সুন্দর উত্তর দেবে। অনেক বেশি লিখবে না।
        - কাস্টমার যা জানতে চায় শুধু তাই উত্তর দেবে।
        - যদি কোনো প্রোডাক্ট সম্পর্কে জিজ্ঞাসা করে, তাহলে সেটার সংক্ষিপ্ত তথ্য দেবে।
        - যদি কোনো দাম জানতে চায় এবং তোমার কাছে সেই প্রোডাক্টের মূল্যের তথ্য থাকে, তাহলে মূল্য বলো। মূল্যের তথ্য না থাকলে অফিসিয়াল পেজে যোগাযোগ করতে বলো।
        - অতিরিক্ত কথা বলবে না। শুধু প্রয়োজনীয় তথ্য দেবে।
        - যদি কোনো প্রশ্নের উত্তর না জানো, তাহলে বলবে এই বিষয়ে আমাদের পেজে যোগাযোগ করুন।
        - গালিবাজি বা অশোভনীয় আচরণ করলে ভদ্রভাবে জানাবে যে আপনি সাহায্য করতে পারবেন না।
        PROMPT;
    }

    public static function testConnection(string $apiKey): array
    {
        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer '.$apiKey,
                'Content-Type' => 'application/json',
            ])->timeout(10)->post('https://api.groq.com/openai/v1/chat/completions', [
                'model' => config('services.groq.model', 'llama-3.3-70b-versatile'),
                'messages' => [
                    ['role' => 'user', 'content' => 'Hello, just testing connection. Reply with one word.'],
                ],
            ]);

            if ($response->status() === 401) {
                return ['success' => false, 'message' => 'API key invalid'];
            }

            if ($response->status() === 429) {
                return ['success' => true, 'message' => 'Connected! (Rate limited but key is valid)'];
            }

            if ($response->failed()) {
                return ['success' => false, 'message' => 'API error: '.$response->status()];
            }

            $body = $response->json();
            $reply = $body['choices'][0]['message']['content'] ?? null;

            return ['success' => true, 'message' => 'Connected! AI replied: '.substr($reply, 0, 50)];
        } catch (\Exception $e) {
            return ['success' => false, 'message' => 'Error: '.$e->getMessage()];
        }
    }
}
