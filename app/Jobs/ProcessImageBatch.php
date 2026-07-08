<?php

namespace App\Jobs;

use App\Services\ClipService;
use Illuminate\Bus\Batchable;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class ProcessImageBatch implements ShouldQueue
{
    use Batchable, Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;

    public int $backoff = 10;

    public int $timeout = 120;

    public function __construct(
        public string $userId,
        public string $imageUrl,
        public int $imageIndex,
        public string $trackingId,
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
            'batch_id' => $this->trackingId,
            'error' => $exception->getMessage(),
        ]);

        cache()->push("batch_{$this->trackingId}_errors", [
            'index' => $this->imageIndex,
            'error' => $exception->getMessage(),
        ]);

        $this->checkBatchCompletion();
    }

    public function handle(): void
    {
        try {
            $clipService = new ClipService();
            
            // Check CLIP server health first
            $health = $clipService->healthCheck();
            if ($health['status'] !== 'healthy') {
                throw new \Exception('CLIP server is not healthy: ' . ($health['details']['error'] ?? 'Unknown error'));
            }

            // Generate embedding for customer image
            $result = $clipService->getEmbeddingFromUrl($this->imageUrl);

            if ($result && isset($result['embedding'])) {
                // Get catalog embeddings for matching
                $catalogEmbeddings = $clipService->getCatalogEmbeddings();
                
                if (empty($catalogEmbeddings)) {
                    $description = "কোনো প্রোডাক্ট ক্যাটালগ পাওয়া যায়নি।";
                } else {
                    // Match customer image against catalog
                    $matchResult = $clipService->matchImage(
                        base64_encode(file_get_contents($this->imageUrl)),
                        $catalogEmbeddings,
                        5,
                        config('services.clip.threshold', 0.7)
                    );

                    if ($matchResult && isset($matchResult['best_match'])) {
                        $bestMatch = $matchResult['best_match'];
                        $score = round($bestMatch['score'] * 100, 1);
                        
                        $description = "প্রোডাক্ট ম্যাচ: {$bestMatch['product_name']} (স্কোর: {$score}%)";
                        
                        // Add alternative matches if any
                        if (count($matchResult['matches']) > 1) {
                            $alternatives = array_slice($matchResult['matches'], 1, 3);
                            $altNames = array_column($alternatives, 'product_name');
                            $description .= "\nঅন্যান্য সম্ভাব্য: " . implode(', ', $altNames);
                        }
                    } else {
                        $description = "দুঃখিত, এই ছবির সাথে কোনো প্রোডাক্ট ম্যাচ করা যায়নি।";
                    }
                }

                cache()->push("batch_{$this->trackingId}_results", [
                    'index' => $this->imageIndex,
                    'description' => $description,
                    'url' => $this->imageUrl,
                ]);

                Log::info('Image analysis done', [
                    'user_id' => $this->userId,
                    'image_index' => $this->imageIndex,
                    'batch_id' => $this->trackingId,
                    'description_length' => strlen($description),
                ]);

                $this->checkBatchCompletion();
            } else {
                throw new \Exception('CLIP returned empty embedding');
            }
        } catch (\Exception $e) {
            Log::error('ProcessImageBatch failed', [
                'user_id' => $this->userId,
                'image_index' => $this->imageIndex,
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    private function checkBatchCompletion(): void
    {
        $results = cache()->get("batch_{$this->trackingId}_results", []);
        $errors = cache()->get("batch_{$this->trackingId}_errors", []);
        $totalExpected = cache()->get("batch_{$this->trackingId}_total", 0);

        $completed = count($results) + count($errors);

        if ($completed >= $totalExpected) {
            Log::info('Batch completed', [
                'batch_id' => $this->trackingId,
                'successful' => count($results),
                'failed' => count($errors),
            ]);

            cache()->put("batch_{$this->trackingId}_done", true, 300);
        }
    }
}
