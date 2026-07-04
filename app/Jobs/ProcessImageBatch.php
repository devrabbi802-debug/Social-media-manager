<?php

namespace App\Jobs;

use App\Models\AiSetting;
use App\Services\GeminiApiService;
use App\Services\GeminiKeyManager;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class ProcessImageBatch implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;

    public int $backoff = 10;

    public int $timeout = 120;

    public function __construct(
        public string $userId,
        public string $imageUrl,
        public int $imageIndex,
        public string $batchId,
    ) {
        $this->onQueue('facebook');
    }

    public function middleware(): array
    {
        return [];
    }

    public function failed(\Throwable $exception): void
    {
        Log::error('ProcessImageBatch failed permanently', [
            'user_id' => $this->userId,
            'image_index' => $this->imageIndex,
            'batch_id' => $this->batchId,
            'error' => $exception->getMessage(),
        ]);

        cache()->push("batch_{$this->batchId}_errors", [
            'index' => $this->imageIndex,
            'error' => $exception->getMessage(),
        ]);

        $this->checkBatchCompletion();
    }

    public function handle(): void
    {
        $keyManager = new GeminiKeyManager($this->userId);
        $key = $keyManager->getAvailableKey();

        if (! $key) {
            $key = $keyManager->getActiveKeys()->first();
        }

        if (! $key) {
            throw new \Exception('No Gemini API key available');
        }

        $imageData = $this->downloadImage($this->imageUrl);

        if (! $imageData) {
            throw new \Exception('Failed to download image');
        }

        $geminiService = new GeminiApiService($key->api_key);

        $prompt = 'তুমি একটি প্রোডাক্ট ইমেজ বিশ্লেষণ করছো। নিচের নিয়মগুলো কঠোরভাবে মেনে চলো: ১) ইমেজের উপর থাকা ads, banner, watermark, text overlay, price tag, discount sticker, বা যেকোনো UI element সম্পর্কে কিছু লিখবে না। ২) শুধুমাত্র মূল প্রোডাক্টটি বর্ণনা করো — প্রোডাক্টের ধরন, রঙ, ডিজাইন, ম্যাটেরিয়াল, আনুমানিক সাইজ। ৩) প্রোডাক্ট ছাড়া অন্য কিছু (মানুষ, গাড়ি, রুম, প্রকৃতি) দেখতে পারলে তা বর্ণনা করো। ৪) ১৫০ শব্দের বেশি না লিখে সংক্ষেপে লেখো।';

        try {
            $description = $geminiService->analyzeImage($imageData, $prompt);

            if ($description !== null) {
                if (mb_strlen($description) > 800) {
                    $description = mb_substr($description, 0, 800) . '...';
                }

                cache()->push("batch_{$this->batchId}_results", [
                    'index' => $this->imageIndex,
                    'description' => $description,
                    'url' => $this->imageUrl,
                ]);

                Log::info('Image analysis done', [
                    'user_id' => $this->userId,
                    'image_index' => $this->imageIndex,
                    'batch_id' => $this->batchId,
                    'description_length' => strlen($description),
                ]);

                $this->checkBatchCompletion();
            } else {
                throw new \Exception('Gemini returned null');
            }
        } catch (\Exception $e) {
            if (str_contains($e->getMessage(), '429')) {
                $keyManager->markKeyRateLimited($key->id, 60);
                throw $e;
            }

            throw $e;
        }
    }

    private function downloadImage(string $url): ?string
    {
        try {
            $response = Http::timeout(30)->get($url);

            if ($response->failed()) {
                Log::error('Failed to download image', ['url' => $url]);
                return null;
            }

            return base64_encode($response->body());
        } catch (\Exception $e) {
            Log::error('Image download exception', [
                'url' => $url,
                'error' => $e->getMessage(),
            ]);
            return null;
        }
    }

    private function checkBatchCompletion(): void
    {
        $results = cache()->get("batch_{$this->batchId}_results", []);
        $errors = cache()->get("batch_{$this->batchId}_errors", []);
        $totalExpected = cache()->get("batch_{$this->batchId}_total", 0);

        $completed = count($results) + count($errors);

        if ($completed >= $totalExpected) {
            Log::info('Batch completed', [
                'batch_id' => $this->batchId,
                'successful' => count($results),
                'failed' => count($errors),
            ]);

            cache()->put("batch_{$this->batchId}_done", true, 300);
        }
    }
}
