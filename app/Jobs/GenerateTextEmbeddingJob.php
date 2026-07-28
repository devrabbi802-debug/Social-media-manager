<?php

namespace App\Jobs;

use App\Models\Product;
use App\Services\TextSearchService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class GenerateTextEmbeddingJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;

    public int $timeout = 60;

    public int $backoff = 15;

    public function __construct(
        public Product $product,
    ) {
        $this->onQueue('facebook');
    }

    public function handle(): void
    {
        $textSearchService = new TextSearchService();

        // Build text from product name + description + category
        $text = $textSearchService->buildProductText($this->product);

        if (empty(trim($text))) {
            Log::warning('GenerateTextEmbeddingJob: empty text for product', [
                'product_id' => $this->product->id,
            ]);

            return;
        }

        try {
            $result = $textSearchService->getTextEmbedding($text);

            if ($result && isset($result['embedding'])) {
                $this->product->update(['text_embedding' => $result['embedding']]);

                Log::info('GenerateTextEmbeddingJob: text embedding generated', [
                    'product_id' => $this->product->id,
                    'dimension' => $result['dimension'] ?? 384,
                ]);
            } else {
                Log::error('GenerateTextEmbeddingJob: empty embedding returned', [
                    'product_id' => $this->product->id,
                ]);
            }
        } catch (\Exception $e) {
            Log::error('GenerateTextEmbeddingJob: failed', [
                'product_id' => $this->product->id,
                'error' => $e->getMessage(),
            ]);

            throw $e;
        }
    }

    public function failed(\Throwable $exception): void
    {
        Log::error('GenerateTextEmbeddingJob: permanently failed', [
            'product_id' => $this->product->id,
            'error' => $exception->getMessage(),
        ]);
    }
}
