<?php

namespace App\Jobs;

use App\Models\ProductImage;
use App\Services\ClipService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class AnalyzeProductImageJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;

    public int $timeout = 90;

    public int $backoff = 30;

    public function __construct(
        public ProductImage $productImage,
        public int $userId,
    ) {
        $this->onQueue('facebook');
    }

    public function handle(): void
    {
        $image = $this->productImage;

        if (!empty($image->embedding)) {
            Log::info('Product image already has embedding', ['id' => $image->id]);
            return;
        }

        $imagePath = $image->image_path;
        if (!\Storage::disk('public')->exists($imagePath)) {
            Log::error('Product image file not found', [
                'product_image_id' => $image->id,
                'path' => $imagePath,
            ]);
            return;
        }

        try {
            $clipService = new ClipService();
            
            // Check CLIP server health first
            $health = $clipService->healthCheck();
            if ($health['status'] !== 'healthy') {
                Log::error('CLIP server is not healthy', ['health' => $health]);
                throw new \Exception('CLIP server is not healthy: ' . ($health['details']['error'] ?? 'Unknown error'));
            }

            // Generate embedding
            $result = $clipService->getEmbeddingFromPath($imagePath);

            if ($result && isset($result['embedding'])) {
                $image->update(['embedding' => $result['embedding']]);

                Log::info('Product image embedding generated successfully', [
                    'product_image_id' => $image->id,
                    'product_id' => $image->product_id,
                    'dimension' => $result['dimension'] ?? 512,
                    'device' => $result['device'] ?? 'unknown',
                ]);
            } else {
                Log::error('CLIP returned empty embedding', [
                    'product_image_id' => $image->id,
                ]);
            }
        } catch (\Exception $e) {
            Log::error('Product image embedding failed', [
                'product_image_id' => $image->id,
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    public function failed(\Throwable $exception): void
    {
        Log::error('AnalyzeProductImageJob failed permanently', [
            'product_image_id' => $this->productImage->id,
            'error' => $exception->getMessage(),
        ]);
    }
}
