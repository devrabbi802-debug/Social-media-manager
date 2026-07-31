<?php

namespace App\Services;

use App\Models\Conversation;
use App\Models\Product;
use App\Models\ProductVariant;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ProductContextService
{
    /**
     * Conversation er current product context return koro.
     */
    public function getCurrentProductContext(Conversation $conversation): ?array
    {
        $data = $conversation->current_product_data;

        if (empty($data) || empty($data['product_id'])) {
            return null;
        }

        // Verify product still exists and is active
        $product = Product::find($data['product_id']);
        if (! $product || $product->status !== 'active') {
            $conversation->update(['current_product_data' => null]);

            return null;
        }

        // Refresh data from DB (price/stock might have changed)
        return $this->buildProductData($product, $data['variant_id'] ?? null);
    }

    /**
     * Conversation e current product save koro.
     */
    public function saveCurrentProduct(Conversation $conversation, $productId, ?int $variantId = null): void
    {
        $productData = $this->buildProductData($productId, $variantId);

        if ($productData) {
            $conversation->update(['current_product_data' => $productData]);

            Log::info('ProductContextService: saved current product', [
                'conversation_id' => $conversation->id,
                'product_id' => $productId,
                'variant_id' => $variantId,
            ]);
        }
    }

    /**
     * AI context string build koro for current product.
     */
    public function buildContextString(Conversation $conversation, string $customerQuestion): ?string
    {
        $productData = $this->getCurrentProductContext($conversation);

        if (! $productData) {
            return null;
        }

        $context = "কাস্টমার বর্তমানে এই প্রোডাক্টটি নিয়ে কথা বলছে:\n\n";
        $context .= "প্রোডাক্ট: {$productData['name']}";
        if (! empty($productData['sku'])) {
            $context .= " (SKU: {$productData['sku']})";
        }
        $context .= "\n";
        $context .= '- দাম: ৳'.number_format($productData['price'], 2)."\n";

        if ($productData['stock'] > 0) {
            $context .= "- স্টক: {$productData['stock']}টি আছে\n";
        } else {
            $context .= "- স্টক: শেষ\n";
        }

        if (! empty($productData['description'])) {
            $context .= "- বিবরণ: {$productData['description']}\n";
        }

        // Show current variant info
        if (! empty($productData['current_variant'])) {
            $cv = $productData['current_variant'];
            $attrs = collect($cv['attributes'])->map(fn ($v, $k) => "{$k}: {$v}")->implode(', ');
            $context .= "- বর্তমান বিকল্প: {$attrs}\n";
        }

        // Show all available variants
        if (! empty($productData['all_variants'])) {
            $context .= "\nউপলব্ধ সব বিকল্প:\n";
            foreach ($productData['all_variants'] as $v) {
                $vAttrs = collect($v['attributes'])->map(fn ($val, $k) => "{$k}: {$val}")->implode(', ');
                $vStock = $v['stock'] > 0 ? "{$v['stock']}টি স্টকে" : 'স্টক শেষ';
                $context .= "• {$vAttrs} — ৳".number_format($v['price'], 2)." [{$vStock}]\n";
            }
        }

        $context .= "\nকাস্টমারের প্রশ্ন: {$customerQuestion}\n";
        $context .= 'উপরের তথ্য ব্যবহার করে উত্তর দিন।';

        return $context;
    }

    /**
     * Product data array build koro — fresh from DB.
     */
    private function buildProductData($productId, ?int $variantId = null): ?array
    {
        $product = is_numeric($productId) ? Product::with('variants')->find($productId) : $productId;

        if (! $product) {
            return null;
        }

        $allVariants = $product->variants
            ->where('is_active', true)
            ->map(fn ($v) => [
                'variant_id' => $v->id,
                'name' => $v->name,
                'sku' => $v->sku,
                'attributes' => $v->attributes ?? [],
                'price' => $v->price ?? $product->discount_price ?? $product->base_price,
                'stock' => $v->stock_quantity,
            ])
            ->values()
            ->toArray();

        $currentVariant = null;
        if ($variantId) {
            $variant = ProductVariant::find($variantId);
            if ($variant) {
                $currentVariant = [
                    'variant_id' => $variant->id,
                    'attributes' => $variant->attributes ?? [],
                    'price' => $variant->price ?? $product->discount_price ?? $product->base_price,
                    'stock' => $variant->stock_quantity,
                ];
            }
        }

        return [
            'product_id' => $product->id,
            'variant_id' => $variantId,
            'name' => $product->name,
            'sku' => $product->sku ?? '',
            'price' => $currentVariant['price'] ?? $product->discount_price ?? $product->base_price,
            'stock' => $currentVariant['stock'] ?? $product->stock_quantity,
            'description' => $product->description ?? '',
            'current_variant' => $currentVariant,
            'all_variants' => $allVariants,
        ];
    }
}
