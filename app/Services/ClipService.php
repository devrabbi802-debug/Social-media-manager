<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class ClipService
{
    private string $baseUrl;

    private int $timeout;

    public function __construct(?string $baseUrl = null, int $timeout = 30)
    {
        $this->baseUrl = $baseUrl ?? config('services.clip.server_url', 'http://localhost:8089');
        $this->timeout = $timeout;
    }

    /**
     * Generate CLIP embedding for an image (base64 encoded).
     *
     * @param string $imageBase64 Base64 encoded image
     * @return array|null ['embedding' => [...], 'dimension' => 512, 'device' => 'cpu']
     */
    public function getEmbedding(string $imageBase64): ?array
    {
        try {
            $response = Http::timeout($this->timeout)
                ->post("{$this->baseUrl}/embed", [
                    'image_base64' => $imageBase64,
                ]);

            if ($response->failed()) {
                Log::error('CLIP embed failed', [
                    'status' => $response->status(),
                    'body' => $response->body(),
                ]);
                return null;
            }

            return $response->json();
        } catch (\Exception $e) {
            Log::error('CLIP embed exception', [
                'message' => $e->getMessage(),
            ]);
            return null;
        }
    }

    /**
     * Generate CLIP embedding for an image file path (storage disk).
     *
     * @param string $imagePath Path relative to storage disk
     * @param string $disk Storage disk name (default: 'public')
     * @return array|null
     */
    public function getEmbeddingFromPath(string $imagePath, string $disk = 'public'): ?array
    {
        if (!\Storage::disk($disk)->exists($imagePath)) {
            Log::error('CLIP: Image file not found', ['path' => $imagePath]);
            return null;
        }

        $imageContents = \Storage::disk($disk)->get($imagePath);
        $imageBase64 = base64_encode($imageContents);

        return $this->getEmbedding($imageBase64);
    }

    /**
     * Generate CLIP embedding for an image URL.
     *
     * @param string $imageUrl Public URL of the image
     * @return array|null
     */
    public function getEmbeddingFromUrl(string $imageUrl): ?array
    {
        try {
            $response = Http::timeout($this->timeout)
                ->post("{$this->baseUrl}/embed", [
                    'image_url' => $imageUrl,
                ]);

            if ($response->failed()) {
                Log::error('CLIP embed from URL failed', [
                    'status' => $response->status(),
                    'body' => $response->body(),
                ]);
                return null;
            }

            return $response->json();
        } catch (\Exception $e) {
            Log::error('CLIP embed from URL exception', [
                'message' => $e->getMessage(),
            ]);
            return null;
        }
    }

    /**
     * Match a customer image against catalog embeddings.
     *
     * @param string $customerImageBase64 Base64 encoded customer image
     * @param array $catalogItems Array of ['id' => int, 'embedding' => array, 'product_name' => string]
     * @param int $topK Number of top matches to return
     * @param float $threshold Minimum similarity score (0-1)
     * @return array|null ['matches' => [...], 'best_match' => [...], 'customer_embedding' => [...]]
     */
    public function matchImage(
        string $customerImageBase64,
        array $catalogItems,
        int $topK = 5,
        float $threshold = 0.7
    ): ?array {
        try {
            $response = Http::timeout($this->timeout)
                ->post("{$this->baseUrl}/match", [
                    'customer_image_base64' => $customerImageBase64,
                    'catalog_embeddings' => $catalogItems,
                    'top_k' => $topK,
                    'threshold' => $threshold,
                ]);

            if ($response->failed()) {
                Log::error('CLIP match failed', [
                    'status' => $response->status(),
                    'body' => $response->body(),
                ]);
                return null;
            }

            return $response->json();
        } catch (\Exception $e) {
            Log::error('CLIP match exception', [
                'message' => $e->getMessage(),
            ]);
            return null;
        }
    }

    /**
     * Match a customer image URL against catalog embeddings.
     *
     * @param string $customerImageUrl Public URL of customer image
     * @param array $catalogItems Array of ['id' => int, 'embedding' => array, 'product_name' => string]
     * @param int $topK Number of top matches to return
     * @param float $threshold Minimum similarity score (0-1)
     * @return array|null
     */
    public function matchImageFromUrl(
        string $customerImageUrl,
        array $catalogItems,
        int $topK = 5,
        float $threshold = 0.7
    ): ?array {
        try {
            $response = Http::timeout($this->timeout)
                ->post("{$this->baseUrl}/match", [
                    'customer_image_url' => $customerImageUrl,
                    'catalog_embeddings' => $catalogItems,
                    'top_k' => $topK,
                    'threshold' => $threshold,
                ]);

            if ($response->failed()) {
                Log::error('CLIP match from URL failed', [
                    'status' => $response->status(),
                    'body' => $response->body(),
                ]);
                return null;
            }

            return $response->json();
        } catch (\Exception $e) {
            Log::error('CLIP match from URL exception', [
                'message' => $e->getMessage(),
            ]);
            return null;
        }
    }

    /**
     * Batch generate embeddings for multiple images.
     *
     * @param array $images Array of ['image_base64' => string] or ['image_url' => string]
     * @return array|null
     */
    public function batchEmbed(array $images): ?array
    {
        try {
            $response = Http::timeout($this->timeout * 2)
                ->post("{$this->baseUrl}/batch/embed", $images);

            if ($response->failed()) {
                Log::error('CLIP batch embed failed', [
                    'status' => $response->status(),
                    'body' => $response->body(),
                ]);
                return null;
            }

            return $response->json();
        } catch (\Exception $e) {
            Log::error('CLIP batch embed exception', [
                'message' => $e->getMessage(),
            ]);
            return null;
        }
    }

    /**
     * Check if CLIP server is healthy.
     *
     * @return array ['status' => 'healthy'|'unhealthy', 'details' => [...]]
     */
    public function healthCheck(): array
    {
        try {
            $response = Http::timeout(5)->get("{$this->baseUrl}/health");

            if ($response->successful()) {
                return [
                    'status' => 'healthy',
                    'details' => $response->json(),
                ];
            }

            return [
                'status' => 'unhealthy',
                'details' => ['error' => 'Server returned ' . $response->status()],
            ];
        } catch (\Exception $e) {
            return [
                'status' => 'unhealthy',
                'details' => ['error' => $e->getMessage()],
            ];
        }
    }

    /**
     * Get all catalog embeddings for a tenant.
     * Used for matching customer images against entire catalog.
     *
     * @return array Array of ['id' => int, 'embedding' => array, 'product_name' => string, 'type' => 'product'|'variant']
     */
    public function getCatalogEmbeddings(): array
    {
        $embeddings = [];

        // Get product image embeddings
        $productImages = \App\Models\ProductImage::whereNotNull('embedding')
            ->with('product:id,name,sku,slug,base_price,discount_price')
            ->get();

        foreach ($productImages as $image) {
            $product = $image->product;
            $price = $product->discount_price ?? $product->base_price;
            $embeddings[] = [
                'id' => $image->id,
                'product_id' => $image->product_id,
                'product_name' => $product->name ?? 'Unknown',
                'product_sku' => $product->sku ?? '',
                'product_slug' => $product->slug ?? '',
                'product_price' => $price,
                'product_base_price' => $product->base_price,
                'product_discount_price' => $product->discount_price,
                'embedding' => $image->embedding,
                'type' => 'product',
                'image_type' => 'product_image',
            ];
        }

        // Get variant image embeddings
        $variantImages = \App\Models\VariantImage::whereNotNull('embedding')
            ->with('variant:id,product_id,name,sku,attributes,price')
            ->get();

        foreach ($variantImages as $image) {
            $variant = $image->variant;
            $product = $variant->product ?? null;
            $price = $variant->price ?? $product->discount_price ?? $product->base_price ?? 0;
            $embeddings[] = [
                'id' => $image->id,
                'variant_id' => $image->variant_id,
                'product_id' => $variant->product_id ?? null,
                'product_name' => $variant->name ?? $variant->sku ?? 'Unknown',
                'product_sku' => $variant->sku ?? '',
                'product_slug' => '',
                'product_price' => $price,
                'variant_attributes' => $variant->attributes ?? [],
                'embedding' => $image->embedding,
                'type' => 'variant',
                'image_type' => 'variant_image',
            ];
        }

        return $embeddings;
    }
}
