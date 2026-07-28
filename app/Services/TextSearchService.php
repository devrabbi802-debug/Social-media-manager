<?php

namespace App\Services;

use App\Models\Product;
use App\Models\ProductVariant;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class TextSearchService
{
    private string $baseUrl;

    private int $timeout;

    public function __construct(?string $baseUrl = null, int $timeout = 30)
    {
        $this->baseUrl = $baseUrl ?? config('services.clip.server_url', 'http://localhost:8089');
        $this->timeout = $timeout;
    }

    /**
     * Generate text embedding for a string.
     */
    public function getTextEmbedding(string $text): ?array
    {
        try {
            $response = Http::timeout($this->timeout)
                ->post("{$this->baseUrl}/text-embed", [
                    'text' => $text,
                ]);

            if ($response->failed()) {
                Log::error('CLIP text-embed failed', [
                    'status' => $response->status(),
                    'body' => $response->body(),
                ]);

                return null;
            }

            return $response->json();
        } catch (\Exception $e) {
            Log::error('CLIP text-embed exception', [
                'message' => $e->getMessage(),
            ]);

            return null;
        }
    }

    /**
     * Search products by text query using semantic similarity.
     */
    public function searchText(string $query, int $topK = 5, float $threshold = 0.3): ?array
    {
        try {
            $catalog = $this->getCatalogTextEmbeddings();

            if (empty($catalog)) {
                return ['matches' => [], 'best_match' => null, 'total' => 0];
            }

            $response = Http::timeout($this->timeout)
                ->post("{$this->baseUrl}/text-search", [
                    'query' => $query,
                    'catalog_embeddings' => $catalog,
                    'top_k' => $topK,
                    'threshold' => $threshold,
                ]);

            if ($response->failed()) {
                Log::error('CLIP text-search failed', [
                    'status' => $response->status(),
                    'body' => $response->body(),
                ]);

                return null;
            }

            return $response->json();
        } catch (\Exception $e) {
            Log::error('CLIP text-search exception', [
                'message' => $e->getMessage(),
            ]);

            return null;
        }
    }

    /**
     * Get all product + variant text embeddings for catalog search.
     */
    public function getCatalogTextEmbeddings(): array
    {
        $embeddings = [];

        // Resolve tenant DB: stancl/tenancy uses 'tenant' connection inside $tenant->run()
        // Fallback to config default, then to landlord if no tenant context
        $tenant = tenant();
        if ($tenant) {
            $conn = DB::connection('tenant');
        } else {
            $conn = DB::connection(config('database.default'));
        }

        // Product text embeddings
        $products = $conn->table('products')
            ->whereNotNull('text_embedding')
            ->get(['id', 'name', 'sku', 'slug', 'base_price', 'discount_price', 'text_embedding', 'category_id']);

        foreach ($products as $product) {
            $price = $product->discount_price ?? $product->base_price;
            $embeddings[] = [
                'id' => 'product_'.$product->id,
                'product_name' => $product->name,
                'metadata' => [
                    'product_id' => $product->id,
                    'product_name' => $product->name,
                    'product_sku' => $product->sku ?? '',
                    'product_slug' => $product->slug ?? '',
                    'product_price' => $price,
                    'type' => 'product',
                ],
                'embedding' => is_string($product->text_embedding) ? json_decode($product->text_embedding, true) : $product->text_embedding,
            ];
        }

        // Variant text embeddings
        $variants = $conn->table('product_variants')
            ->whereNotNull('text_embedding')
            ->get();

        // Get parent product info for variants
        $variantProductIds = collect($variants)->pluck('product_id')->unique()->toArray();
        $parentProducts = [];
        if (! empty($variantProductIds)) {
            $parents = $conn->table('products')
                ->whereIn('id', $variantProductIds)
                ->get(['id', 'name', 'sku', 'slug', 'base_price', 'discount_price']);
            foreach ($parents as $p) {
                $parentProducts[$p->id] = $p;
            }
        }

        foreach ($variants as $variant) {
            $product = $parentProducts[$variant->product_id] ?? null;
            $attrs = json_decode($variant->attributes, true) ?? [];
            $price = $variant->price ?? ($product->discount_price ?? ($product->base_price ?? 0));
            $name = $variant->name ?? $variant->sku ?? ($product->name ?? 'Unknown');
            $embeddings[] = [
                'id' => 'variant_'.$variant->id,
                'product_name' => $name,
                'metadata' => [
                    'variant_id' => $variant->id,
                    'product_id' => $variant->product_id,
                    'product_name' => $name,
                    'product_sku' => $variant->sku ?? '',
                    'product_price' => $price,
                    'variant_attributes' => $attrs,
                    'type' => 'variant',
                ],
                'embedding' => is_string($variant->text_embedding) ? json_decode($variant->text_embedding, true) : $variant->text_embedding,
            ];
        }

        return $embeddings;
    }

    /**
     * Build text content for embedding from product data.
     */
    public function buildProductText(Product $product): string
    {
        $parts = [$product->name];

        if ($product->sku) {
            $parts[] = "SKU: {$product->sku}";
        }
        if ($product->description) {
            $parts[] = $product->description;
        }

        return implode(' ', $parts);
    }

    /**
     * Build text content for embedding from variant data.
     */
    public function buildVariantText(ProductVariant $variant): string
    {
        $parts = [];

        if ($variant->name) {
            $parts[] = $variant->name;
        }
        if ($variant->sku) {
            $parts[] = "SKU: {$variant->sku}";
        }
        if ($variant->attributes && is_array($variant->attributes)) {
            foreach ($variant->attributes as $key => $value) {
                $parts[] = "{$key}: {$value}";
            }
        }

        // Also include parent product info
        $product = $variant->product;
        if ($product) {
            $parts[] = $product->name;
            if ($product->description) {
                $parts[] = $product->description;
            }
        }

        return implode(' ', $parts);
    }
}
