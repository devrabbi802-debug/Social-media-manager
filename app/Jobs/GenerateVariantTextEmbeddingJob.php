<?php

namespace App\Jobs;

use App\Models\ProductVariant;
use App\Services\TextSearchService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class GenerateVariantTextEmbeddingJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;

    public int $timeout = 60;

    public int $backoff = 15;

    public function __construct(
        public ProductVariant $variant,
    ) {
        $this->onQueue('facebook');
    }

    public function handle(): void
    {
        $textSearchService = new TextSearchService();

        // Build text from variant name + attributes + parent product
        $text = $textSearchService->buildVariantText($this->variant);

        if (empty(trim($text))) {
            Log::warning('GenerateVariantTextEmbeddingJob: empty text for variant', [
                'variant_id' => $this->variant->id,
            ]);

            return;
        }

        try {
            $result = $textSearchService->getTextEmbedding($text);

            if ($result && isset($result['embedding'])) {
                $this->variant->update(['text_embedding' => $result['embedding']]);

                Log::info('GenerateVariantTextEmbeddingJob: text embedding generated', [
                    'variant_id' => $this->variant->id,
                    'dimension' => $result['dimension'] ?? 384,
                ]);
            } else {
                Log::error('GenerateVariantTextEmbeddingJob: empty embedding returned', [
                    'variant_id' => $this->variant->id,
                ]);
            }
        } catch (\Exception $e) {
            Log::error('GenerateVariantTextEmbeddingJob: failed', [
                'variant_id' => $this->variant->id,
                'error' => $e->getMessage(),
            ]);

            throw $e;
        }
    }

    public function failed(\Throwable $exception): void
    {
        Log::error('GenerateVariantTextEmbeddingJob: permanently failed', [
            'variant_id' => $this->variant->id,
            'error' => $exception->getMessage(),
        ]);
    }
}
