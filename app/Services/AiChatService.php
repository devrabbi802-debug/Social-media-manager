<?php

namespace App\Services;

use Illuminate\Database\Eloquent\Collection;
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

    public function chatWithHistory(string $message, $keys, array $history = [], ?string $fallbackProvider = null, $fallbackKeys = null, ?string $secondFallbackProvider = null, $secondFallbackKeys = null): ?string
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
                    'key_label' => $key->label ?? $key->id,
                    'key_id' => $key->id,
                ]);
            } catch (\Exception $e) {
                $lastException = $e;

                if (! str_contains($e->getMessage(), '429')) {
                    $allRateLimited = false;
                }

                Log::warning('AI key failed, trying next key', [
                    'key_label' => $key->label ?? $key->id,
                    'key_id' => $key->id,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        Log::error('All primary AI keys exhausted', [
            'keys_tried' => count($keys),
            'last_error' => $lastException?->getMessage(),
        ]);

        // Cerebras fallback — try if Cerebras keys available
        if ($fallbackProvider === 'cerebras' && $fallbackKeys && $fallbackKeys->isNotEmpty()) {
            Log::info('Trying Cerebras fallback keys', ['count' => $fallbackKeys->count()]);
            $cerebrasResult = $this->chatWithCerebrasFallback($message, $fallbackKeys, $history);
            if ($cerebrasResult !== null) {
                return $cerebrasResult;
            }
        }

        // Gemini fallback — try if Gemini keys available
        if ($secondFallbackProvider === 'gemini' && $secondFallbackKeys && $secondFallbackKeys->isNotEmpty()) {
            Log::info('Trying Gemini fallback keys', ['count' => $secondFallbackKeys->count()]);

            return $this->chatWithGeminiFallback($message, $secondFallbackKeys, $history);
        }

        // If only Gemini was provided as fallback (no Cerebras)
        if ($fallbackProvider === 'gemini' && $fallbackKeys && $fallbackKeys->isNotEmpty()) {
            Log::info('Trying Gemini fallback keys', ['count' => $fallbackKeys->count()]);

            return $this->chatWithGeminiFallback($message, $fallbackKeys, $history);
        }

        return null;
    }

    public function chatWithGeminiFallback(string $message, $fallbackKeys, array $history = []): ?string
    {
        foreach ($fallbackKeys as $key) {
            try {
                $result = $this->chatWithGemini($message, $key->api_key, $history);

                if ($result !== null) {
                    Log::info('Gemini fallback succeeded', [
                        'key_id' => $key->id,
                    ]);

                    return $result;
                }

                Log::warning('Gemini fallback key returned null', [
                    'key_id' => $key->id,
                ]);
            } catch (\Exception $e) {
                Log::warning('Gemini fallback key failed', [
                    'key_id' => $key->id,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        Log::error('All Gemini fallback keys exhausted');

        return null;
    }

    public function chatWithCerebrasFallback(string $message, $fallbackKeys, array $history = []): ?string
    {
        foreach ($fallbackKeys as $key) {
            try {
                $result = $this->chatWithCerebras($message, $key->api_key, $history);

                if ($result !== null) {
                    Log::info('Cerebras fallback succeeded', [
                        'key_id' => $key->id,
                    ]);

                    return $result;
                }

                Log::warning('Cerebras fallback key returned null', [
                    'key_id' => $key->id,
                ]);
            } catch (\Exception $e) {
                Log::warning('Cerebras fallback key failed', [
                    'key_id' => $key->id,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        Log::error('All Cerebras fallback keys exhausted');

        return null;
    }

    private function chatWithCerebras(string $message, string $apiKey, array $history = [], bool $isRetry = false): ?string
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
            ])->timeout(30)->post('https://api.cerebras.ai/v1/chat/completions', [
                'model' => config('services.cerebras.model', 'gpt-oss-120b'),
                'messages' => $messages,
                'temperature' => 0.7,
                'max_tokens' => 1024,
                'top_p' => 0.9,
            ]);

            if ($response->status() === 429) {
                Log::warning('Cerebras 429 rate limited');
                throw new \Exception('Cerebras API rate limited (429)');
            }

            if ($response->status() === 413) {
                if ($isRetry) {
                    Log::error('Cerebras API request too large (413), already retried with truncation');

                    return null;
                }
                Log::error('Cerebras API request too large (413)', [
                    'message_length' => mb_strlen($message),
                ]);
                $truncatedMessage = mb_substr($message, 0, 4000)."\n\n[বার্তা সংক্ষিপ্ত করা হয়েছে]";

                return $this->chatWithCerebras($truncatedMessage, $apiKey, $history, true);
            }

            if ($response->failed()) {
                Log::error('Cerebras API error', [
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
            Log::error('Cerebras API exception', [
                'message' => $e->getMessage(),
            ]);

            return null;
        }
    }

    private function chatWithGemini(string $message, string $apiKey, array $history = []): ?string
    {
        try {
            $contents = [];

            $normalizedHistory = $this->normalizeHistory($history);

            foreach ($normalizedHistory as $msg) {
                $contents[] = [
                    'role' => $msg['role'],
                    'parts' => $msg['parts'],
                ];
            }

            $contents[] = [
                'role' => 'user',
                'parts' => [['text' => $message]],
            ];

            $model = config('services.gemini.model', 'gemini-3.1-flash-lite');

            $response = Http::withHeaders([
                'Content-Type' => 'application/json',
            ])->timeout(30)->post(
                "https://generativelanguage.googleapis.com/v1beta/models/{$model}:generateContent?key={$apiKey}",
                [
                    'contents' => $contents,
                    'systemInstruction' => [
                        'parts' => [['text' => $this->systemPrompt]],
                    ],
                    'generationConfig' => [
                        'temperature' => 0.7,
                        'maxOutputTokens' => 1024,
                        'topP' => 0.9,
                    ],
                ]
            );

            if ($response->status() === 429) {
                Log::warning('Gemini 429 rate limited');
                throw new \Exception('Gemini API rate limited (429)');
            }

            if ($response->status() === 503) {
                throw new \Exception('Gemini API overloaded (503)');
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
                if (isset($part['text'])) {
                    return $part['text'];
                }
            }

            return null;
        } catch (\Exception $e) {
            if (str_contains($e->getMessage(), '429') || str_contains($e->getMessage(), '503')) {
                throw $e;
            }

            Log::error('Gemini chat exception', [
                'message' => $e->getMessage(),
            ]);

            return null;
        }
    }

    private function normalizeHistory(array $history): array
    {
        if (empty($history)) {
            return [];
        }

        $normalized = [];
        $lastRole = null;

        foreach ($history as $msg) {
            $role = $msg['role'] === 'assistant' ? 'model' : 'user';

            if ($role === $lastRole) {
                $lastIndex = count($normalized) - 1;
                $normalized[$lastIndex]['parts'][] = ['text' => $msg['content']];
            } else {
                $normalized[] = [
                    'role' => $role,
                    'parts' => [['text' => $msg['content']]],
                ];
                $lastRole = $role;
            }
        }

        while (! empty($normalized) && $normalized[0]['role'] === 'model') {
            array_shift($normalized);
        }

        return $normalized;
    }

    public function chat(string $message, string $apiKey): ?string
    {
        return $this->chatWithMessages($message, $apiKey, []);
    }

    public function chatWithMessages(string $message, string $apiKey, array $history = [], bool $isRetry = false): ?string
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
                'temperature' => 0.7,
                'max_tokens' => 1024,
                'top_p' => 0.9,
            ]);

            if ($response->status() === 429) {
                $retryAfter = (int) $response->header('Retry-After', 30);
                Log::warning('Groq 429 rate limited', ['retry_after' => $retryAfter]);
                throw new \Exception('AI API rate limited (429)');
            }

            if ($response->status() === 413) {
                if ($isRetry) {
                    Log::error('Groq API request too large (413), already retried with truncation');

                    return null;
                }
                Log::error('Groq API request too large (413)', [
                    'message_length' => mb_strlen($message),
                ]);
                $truncatedMessage = mb_substr($message, 0, 4000)."\n\n[বার্তা সংক্ষিপ্ত করা হয়েছে]";

                return $this->chatWithMessages($truncatedMessage, $apiKey, $history, true);
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

        ইমেজ বিশ্লেষণের তথ্য পেলে:
        - প্রোডাক্টের নাম, মূল্য, স্টক সহ স্বাভাবিক কথোপকথনের ধরনে উত্তর দিন।
        - শুধু দামের সংখ্যা তালিকা করবেন না। বরং এভাবে উত্তর দিন: "আপনার ছবিতে এই প্রোডাক্টটি ম্যাচ করেছে — [নাম], দাম [মূল্য]। স্টকে আছে/নেই। আপনি কি কিনতে চান?"

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

    public static function testGeminiConnection(string $apiKey): array
    {
        try {
            $model = config('services.gemini.model', 'gemini-3.1-flash-lite');

            $response = Http::withHeaders([
                'Content-Type' => 'application/json',
            ])->timeout(15)->post(
                "https://generativelanguage.googleapis.com/v1beta/models/{$model}:generateContent?key={$apiKey}",
                [
                    'contents' => [
                        [
                            'role' => 'user',
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
                return ['success' => false, 'message' => 'API error: '.$response->status()];
            }

            $body = $response->json();
            $text = $body['candidates'][0]['content']['parts'][0]['text'] ?? null;

            return ['success' => true, 'message' => 'Connected! Gemini replied: '.substr($text ?? '', 0, 50)];
        } catch (\Exception $e) {
            return ['success' => false, 'message' => 'Error: '.$e->getMessage()];
        }
    }

    public static function testCerebrasConnection(string $apiKey): array
    {
        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer '.$apiKey,
                'Content-Type' => 'application/json',
            ])->timeout(15)->post('https://api.cerebras.ai/v1/chat/completions', [
                'model' => config('services.cerebras.model', 'gpt-oss-120b'),
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

            return ['success' => true, 'message' => 'Connected! Cerebras replied: '.substr($reply ?? '', 0, 50)];
        } catch (\Exception $e) {
            return ['success' => false, 'message' => 'Error: '.$e->getMessage()];
        }
    }

    // ═══════════════════════════════════════════════════════════════════
    // FUNCTION CALLING SUPPORT (Agentic Loop)
    // ═══════════════════════════════════════════════════════════════════

    /**
     * Run the agentic loop: call AI with tools, execute tool calls, repeat until
     * the AI produces a final text reply or max iterations reached.
     *
     * @return array{reply: string, tool_calls_used: array}
     */
    public function chatWithToolsAndExecution(
        string $message,
        $keys,
        array $history,
        array $tools,
        \Closure $toolExecutor,
        ?string $fallbackProvider = null,
        $fallbackKeys = null,
        ?string $secondFallbackProvider = null,
        $secondFallbackKeys = null,
        int $maxIterations = 5,
    ): array {
        $keys = $keys instanceof Collection ? $keys->all() : (array) $keys;
        $fallbackKeys = $fallbackKeys instanceof Collection ? $fallbackKeys->all() : (array) $fallbackKeys;
        $secondFallbackKeys = $secondFallbackKeys instanceof Collection ? $secondFallbackKeys->all() : (array) $secondFallbackKeys;
        $allMessages = [
            ['role' => 'system', 'content' => $this->systemPrompt],
        ];

        foreach ($history as $msg) {
            $allMessages[] = [
                'role' => $msg['role'],
                'content' => $msg['content'],
            ];
        }

        $allMessages[] = ['role' => 'user', 'content' => $message];

        $toolCallsUsed = [];
        $iteration = 0;

        while ($iteration < $maxIterations) {
            $iteration++;

            // Try Cerebras first (Groq llama-3.3 doesn't support OpenAI tool format), then Gemini
            $response = null;
            $provider = null;

            if ($fallbackProvider === 'cerebras' && $fallbackKeys) {
                foreach ($fallbackKeys as $key) {
                    try {
                        $response = $this->chatWithCerebrasTools($allMessages, $key->api_key, $tools);
                        if ($response !== null) {
                            $provider = 'cerebras';
                            break;
                        }
                    } catch (\Exception $e) {
                        if (str_contains($e->getMessage(), '429')) {
                            continue;
                        }
                        Log::warning('Cerebras tool call failed', ['error' => $e->getMessage()]);
                    }
                }
            }

            // If Cerebras returned text-only (no tool calls), try Gemini as fallback
            // before accepting the text. Cerebras models often don't support tool
            // calling and return plain text, so Gemini must get a chance.
            if ($response !== null && empty($response['tool_calls']) && $fallbackProvider !== 'cerebras') {
                Log::info('AiChatService: Cerebras returned no tool calls, trying Gemini fallback', [
                    'iteration' => $iteration,
                    'content_preview' => mb_substr($response['content'] ?? '', 0, 80),
                ]);
                $geminiResponse = null;
                if ($fallbackKeys) {
                    foreach ($fallbackKeys as $key) {
                        try {
                            $geminiResponse = $this->chatWithGeminiTools($allMessages, $key->api_key, $tools);
                            if ($geminiResponse !== null) {
                                break;
                            }
                        } catch (\Exception $e) {
                            if (str_contains($e->getMessage(), '429')) {
                                continue;
                            }
                            Log::warning('Gemini tool call failed (fallback)', ['error' => $e->getMessage()]);
                        }
                    }
                }
                // If Gemini returned tool calls, use Gemini's response instead
                if ($geminiResponse !== null && ! empty($geminiResponse['tool_calls'])) {
                    Log::info('AiChatService: Gemini fallback produced tool calls, using Gemini', [
                        'iteration' => $iteration,
                    ]);
                    $response = $geminiResponse;
                    $provider = 'gemini';
                }
            }

            if ($response === null && $fallbackProvider === 'gemini' && $fallbackKeys) {
                foreach ($fallbackKeys as $key) {
                    try {
                        $response = $this->chatWithGeminiTools($allMessages, $key->api_key, $tools);
                        if ($response !== null) {
                            $provider = 'gemini';
                            break;
                        }
                    } catch (\Exception $e) {
                        if (str_contains($e->getMessage(), '429')) {
                            continue;
                        }
                        Log::warning('Gemini tool call failed', ['error' => $e->getMessage()]);
                    }
                }
            }

            if ($response === null) {
                Log::error('All providers failed for tool call', ['iteration' => $iteration]);
                break;
            }

            // Check if response has tool calls
            if (! empty($response['tool_calls'])) {
                // Add assistant message with tool calls to history
                $assistantMsg = ['role' => 'assistant', 'content' => $response['content'] ?? ''];
                if (! empty($response['tool_calls'])) {
                    $assistantMsg['tool_calls'] = $response['tool_calls'];
                }
                $allMessages[] = $assistantMsg;

                // Execute each tool call
                foreach ($response['tool_calls'] as $toolCall) {
                    $toolName = $toolCall['function']['name'] ?? '';
                    $toolArgs = json_decode($toolCall['function']['arguments'] ?? '{}', true) ?? [];

                    Log::info('AiChatService: executing tool call', [
                        'tool' => $toolName,
                        'args' => $toolArgs,
                        'provider' => $provider,
                        'iteration' => $iteration,
                    ]);

                    $toolResult = $toolExecutor($toolName, $toolArgs);
                    $toolCallsUsed[] = ['tool' => $toolName, 'args' => $toolArgs, 'result' => $toolResult];

                    // Add tool result to messages
                    $allMessages[] = [
                        'role' => 'tool',
                        'tool_call_id' => $toolCall['id'] ?? 'call_'.uniqid(),
                        'content' => json_encode($toolResult, JSON_UNESCAPED_UNICODE),
                    ];
                }

                // Continue loop — AI will process tool results
                continue;
            }

            // No tool calls — this is the final text reply
            return [
                'reply' => $response['content'] ?? '',
                'tool_calls_used' => $toolCallsUsed,
            ];
        }

        // Max iterations reached or all providers failed
        return [
            'reply' => 'দুঃখিত, এই মুহূর্তে আমি সঠিকভাবে উত্তর দিতে পারছি না। আবার চেষ্টা করুন।',
            'tool_calls_used' => $toolCallsUsed,
        ];
    }

    /**
     * Call Groq API with tools (OpenAI-compatible format).
     */
    private function chatWithGroqTools(array $messages, string $apiKey, array $tools): ?array
    {
        $response = Http::withHeaders([
            'Authorization' => 'Bearer '.$apiKey,
            'Content-Type' => 'application/json',
        ])->timeout(30)->post('https://api.groq.com/openai/v1/chat/completions', [
            'model' => config('services.groq.model', 'llama-3.3-70b-versatile'),
            'messages' => $messages,
            'tools' => $tools,
            'tool_choice' => 'auto',
            'temperature' => 0.7,
            'max_tokens' => 1024,
            'top_p' => 0.9,
        ]);

        if ($response->status() === 429) {
            throw new \Exception('Groq API rate limited (429)');
        }

        if ($response->failed()) {
            Log::error('Groq tools API error', [
                'status' => $response->status(),
                'body' => $response->body(),
            ]);

            return null;
        }

        $body = $response->json();
        $message = $body['choices'][0]['message'] ?? [];

        // Check for tool calls
        if (! empty($message['tool_calls'])) {
            return [
                'content' => $message['content'] ?? null,
                'tool_calls' => $message['tool_calls'],
            ];
        }

        return [
            'content' => $message['content'] ?? null,
            'tool_calls' => [],
        ];
    }

    /**
     * Call Cerebras API with tools (OpenAI-compatible format).
     */
    private function chatWithCerebrasTools(array $messages, string $apiKey, array $tools): ?array
    {
        $response = Http::withHeaders([
            'Authorization' => 'Bearer '.$apiKey,
            'Content-Type' => 'application/json',
        ])->timeout(30)->post('https://api.cerebras.ai/v1/chat/completions', [
            'model' => config('services.cerebras.model', 'gpt-oss-120b'),
            'messages' => $messages,
            'tools' => $tools,
            'tool_choice' => 'auto',
            'temperature' => 0.7,
            'max_tokens' => 1024,
            'top_p' => 0.9,
        ]);

        if ($response->status() === 429) {
            throw new \Exception('Cerebras API rate limited (429)');
        }

        if ($response->failed()) {
            Log::error('Cerebras tools API error', [
                'status' => $response->status(),
                'body' => $response->body(),
            ]);

            return null;
        }

        $body = $response->json();
        $message = $body['choices'][0]['message'] ?? [];

        if (! empty($message['tool_calls'])) {
            return [
                'content' => $message['content'] ?? null,
                'tool_calls' => $message['tool_calls'],
            ];
        }

        return [
            'content' => $message['content'] ?? null,
            'tool_calls' => [],
        ];
    }

    /**
     * Call Gemini API with tools (Google-native format).
     */
    private function chatWithGeminiTools(array $messages, string $apiKey, array $tools): ?array
    {
        $contents = [];
        $systemParts = [];

        foreach ($messages as $msg) {
            if ($msg['role'] === 'system') {
                $systemParts[] = ['text' => $msg['content']];
            } elseif ($msg['role'] === 'user') {
                $contents[] = [
                    'role' => 'user',
                    'parts' => [['text' => $msg['content']]],
                ];
            } elseif ($msg['role'] === 'assistant') {
                $parts = [];
                if (! empty($msg['content'])) {
                    $parts[] = ['text' => $msg['content']];
                }
                // Add function calls if present
                if (! empty($msg['tool_calls'])) {
                    foreach ($msg['tool_calls'] as $tc) {
                        $parts[] = [
                            'functionCall' => [
                                'name' => $tc['function']['name'],
                                'args' => json_decode($tc['function']['arguments'] ?? '{}', true),
                            ],
                        ];
                    }
                }
                if (! empty($parts)) {
                    $contents[] = ['role' => 'model', 'parts' => $parts];
                }
            } elseif ($msg['role'] === 'tool') {
                $result = json_decode($msg['content'], true) ?? ['error' => 'Invalid tool result'];
                // For Gemini, we need the tool name, not the tool_call_id
                // Extract tool name from tool_call_id (format: call_xxx)
                $toolName = $msg['tool_call_id'] ?? 'unknown';
                // Find the tool call in history to get the actual name
                foreach ($messages as $prevMsg) {
                    if ($prevMsg['role'] === 'assistant' && ! empty($prevMsg['tool_calls'])) {
                        foreach ($prevMsg['tool_calls'] as $tc) {
                            if ($tc['id'] === $msg['tool_call_id']) {
                                $toolName = $tc['function']['name'];
                                break 2;
                            }
                        }
                    }
                }
                $contents[] = [
                    'role' => 'user',
                    'parts' => [
                        [
                            'functionResponse' => [
                                'name' => $toolName,
                                'response' => $result,
                            ],
                        ],
                    ],
                ];
            }
        }

        $model = config('services.gemini.model', 'gemini-3.1-flash-lite');

        $payload = [
            'contents' => $contents,
            'tools' => $tools,
            'generationConfig' => [
                'temperature' => 0.7,
                'maxOutputTokens' => 1024,
                'topP' => 0.9,
            ],
        ];

        if (! empty($systemParts)) {
            $payload['systemInstruction'] = ['parts' => $systemParts];
        }

        $response = Http::withHeaders([
            'Content-Type' => 'application/json',
        ])->timeout(30)->post(
            "https://generativelanguage.googleapis.com/v1beta/models/{$model}:generateContent?key={$apiKey}",
            $payload
        );

        if ($response->status() === 429) {
            throw new \Exception('Gemini API rate limited (429)');
        }

        if ($response->failed()) {
            Log::error('Gemini tools API error', [
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
        $content = null;
        $toolCalls = [];

        foreach ($parts as $part) {
            if (isset($part['text'])) {
                $content = $part['text'];
            } elseif (isset($part['functionCall'])) {
                $toolCalls[] = [
                    'id' => 'call_'.uniqid(),
                    'type' => 'function',
                    'function' => [
                        'name' => $part['functionCall']['name'],
                        'arguments' => json_encode($part['functionCall']['args'] ?? []),
                    ],
                ];
            }
        }

        return [
            'content' => $content,
            'tool_calls' => $toolCalls,
        ];
    }
}
