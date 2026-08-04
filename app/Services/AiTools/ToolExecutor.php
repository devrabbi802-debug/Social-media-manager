<?php

namespace App\Services\AiTools;

use App\Models\BusinessSetting;
use App\Models\Conversation;
use App\Models\Message;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Services\ChatSelectionService;
use App\Services\ProductContextService;
use App\Services\TextSearchService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ToolExecutor
{
    private string $senderId;

    private ?Conversation $conversation;

    private ?string $senderName;

    private array $pendingImages = [];

    private ?string $pageAccessToken = null;

    public function __construct(string $senderId, ?Conversation $conversation = null, ?string $senderName = null, ?string $pageAccessToken = null)
    {
        $this->senderId = $senderId;
        $this->conversation = $conversation;
        $this->senderName = $senderName;
        $this->pageAccessToken = $pageAccessToken;
    }

    /**
     * Get any images that the AI requested to send.
     */
    public function getPendingImages(): array
    {
        return $this->pendingImages;
    }

    /**
     * Execute a tool call and return the result as an array.
     */
    public function execute(string $functionName, array $arguments): array
    {
        Log::info('ToolExecutor: executing tool', [
            'tool' => $functionName,
            'arguments' => $arguments,
            'sender_id' => $this->senderId,
        ]);

        return match ($functionName) {
            'search_products' => $this->searchProducts($arguments),
            'get_product_details' => $this->getProductDetails($arguments),
            'get_product_image' => $this->getProductImage($arguments),
            'send_product_image' => $this->sendProductImage($arguments),
            'get_current_context' => $this->getCurrentContext($arguments),
            'get_business_info' => $this->getBusinessInfo($arguments),
            'get_delivery_charge' => $this->getDeliveryCharge($arguments),
            'escalate_to_human' => $this->escalateToHuman($arguments),
            default => ['error' => "Unknown tool: {$functionName}"],
        };
    }

    private function searchProducts(array $args): array
    {
        $query = $args['query'] ?? '';
        $limit = $args['limit'] ?? 5;

        if (empty($query)) {
            return ['error' => 'Query is required'];
        }

        try {
            $textSearchService = new TextSearchService;
            $results = $textSearchService->searchText($query, topK: $limit, threshold: 0.3);

            if (! $results || empty($results['matches'])) {
                return [
                    'found' => false,
                    'message' => " '{$query}' এর জন্য কোনো প্রোডাক্ট পাওয়া যায়নি।",
                ];
            }

            $products = [];
            foreach ($results['matches'] as $match) {
                $metadata = $match['metadata'] ?? [];
                $pid = $metadata['product_id'] ?? null;
                $vid = $metadata['variant_id'] ?? null;

                $product = [
                    'product_id' => $pid,
                    'variant_id' => $vid,
                    'name' => $metadata['product_name'] ?? $match['product_name'] ?? 'Unknown',
                    'price' => $metadata['product_price'] ?? 0,
                    'stock' => $metadata['stock_quantity'] ?? null,
                    'description' => $metadata['description'] ?? '',
                    'sku' => $metadata['product_sku'] ?? '',
                    'score' => round(($match['score'] ?? 0) * 100),
                    'type' => $metadata['type'] ?? 'product',
                ];

                // Add variant attributes if available
                if (! empty($metadata['variant_attributes'])) {
                    $product['variant_attributes'] = $metadata['variant_attributes'];
                }

                // Fetch full product details with variants from DB
                if ($pid) {
                    $fullProduct = Product::with('variants')->find($pid);
                    if ($fullProduct) {
                        $product['base_price'] = $fullProduct->base_price;
                        $product['discount_price'] = $fullProduct->discount_price;
                        $product['image_url'] = $fullProduct->images->first()?->image_url ?? null;
                        $product['category'] = $fullProduct->category->name ?? null;
                        $product['brand'] = $fullProduct->brand->name ?? null;

                        $activeVariants = $fullProduct->variants->where('is_active', true);
                        if ($activeVariants->count() > 0) {
                            $product['variants'] = $activeVariants->map(fn ($v) => [
                                'variant_id' => $v->id,
                                'attributes' => $v->attributes,
                                'price' => $v->price ?? $fullProduct->discount_price ?? $fullProduct->base_price,
                                'stock' => $v->stock_quantity,
                                'sku' => $v->sku,
                            ])->values()->toArray();
                        }
                    }
                }

                $products[] = $product;
            }

            return [
                'found' => true,
                'count' => count($products),
                'products' => $products,
            ];
        } catch (\Exception $e) {
            Log::error('ToolExecutor: search_products failed', [
                'query' => $query,
                'error' => $e->getMessage(),
            ]);

            return ['error' => 'সার্চে সমস্যা হয়েছে। আবার চেষ্টা করুন।'];
        }
    }

    private function getProductDetails(array $args): array
    {
        $productId = $args['product_id'] ?? null;
        $variantId = $args['variant_id'] ?? null;

        if (! $productId) {
            return ['error' => 'product_id is required'];
        }

        try {
            if ($variantId) {
                $variant = ProductVariant::with('product')->find($variantId);
                if (! $variant) {
                    return ['error' => 'Variant not found'];
                }

                $product = $variant->product;
                $details = [
                    'product_id' => $product->id,
                    'variant_id' => $variant->id,
                    'name' => $product->name,
                    'sku' => $variant->sku ?? $product->sku,
                    'price' => $variant->price ?? $product->discount_price ?? $product->base_price,
                    'base_price' => $product->base_price,
                    'discount_price' => $product->discount_price,
                    'stock' => $variant->stock_quantity,
                    'description' => $product->description,
                    'category' => $product->category->name ?? null,
                    'brand' => $product->brand->name ?? null,
                    'attributes' => $variant->attributes,
                    'image_url' => $variant->image_url ?? $product->images->first()?->image_url ?? null,
                ];

                // Get sibling variants
                $siblingVariants = $product->variants->where('is_active', true)->where('id', '!=', $variantId);
                if ($siblingVariants->count() > 0) {
                    $details['variants'] = $siblingVariants->map(fn ($v) => [
                        'variant_id' => $v->id,
                        'attributes' => $v->attributes,
                        'price' => $v->price ?? $product->discount_price ?? $product->base_price,
                        'stock' => $v->stock_quantity,
                        'sku' => $v->sku,
                    ])->values()->toArray();
                }
            } else {
                $product = Product::with(['variants', 'images'])->find($productId);
                if (! $product) {
                    return ['error' => 'Product not found'];
                }

                $details = [
                    'product_id' => $product->id,
                    'name' => $product->name,
                    'sku' => $product->sku,
                    'price' => $product->discount_price ?? $product->base_price,
                    'base_price' => $product->base_price,
                    'discount_price' => $product->discount_price,
                    'stock' => $product->stock_quantity,
                    'description' => $product->description,
                    'category' => $product->category->name ?? null,
                    'brand' => $product->brand->name ?? null,
                    'image_url' => $product->images->first()?->image_url ?? null,
                ];

                $activeVariants = $product->variants->where('is_active', true);
                if ($activeVariants->count() > 0) {
                    $details['variants'] = $activeVariants->map(fn ($v) => [
                        'variant_id' => $v->id,
                        'attributes' => $v->attributes,
                        'price' => $v->price ?? $product->discount_price ?? $product->base_price,
                        'stock' => $v->stock_quantity,
                        'sku' => $v->sku,
                    ])->values()->toArray();
                }
            }

            return $details;
        } catch (\Exception $e) {
            Log::error('ToolExecutor: get_product_details failed', [
                'product_id' => $productId,
                'error' => $e->getMessage(),
            ]);

            return ['error' => 'প্রোডাক্টের তথ্য আনতে সমস্যা হয়েছে।'];
        }
    }

    private function getProductImage(array $args): array
    {
        $productId = $args['product_id'] ?? null;
        $variantId = $args['variant_id'] ?? null;

        if (! $productId) {
            return ['error' => 'product_id is required'];
        }

        try {
            $product = Product::with('images')->find($productId);
            if (! $product) {
                return ['error' => 'Product not found'];
            }

            $image = null;
            if ($variantId) {
                $variant = ProductVariant::find($variantId);
                if ($variant && $variant->image_path) {
                    $image = asset('storage/'.$variant->image_path);
                }
            }

            if (! $image) {
                $image = $product->images->first()?->image_url;
            }

            if (! $image) {
                return [
                    'found' => false,
                    'message' => 'এই প্রোডাক্টের কোনো ছবি নেই।',
                ];
            }

            return [
                'found' => true,
                'product_id' => $product->id,
                'product_name' => $product->name,
                'image_url' => $image,
            ];
        } catch (\Exception $e) {
            Log::error('ToolExecutor: get_product_image failed', [
                'product_id' => $productId,
                'error' => $e->getMessage(),
            ]);

            return ['error' => 'ছবি আনতে সমস্যা হয়েছে।'];
        }
    }

    private function sendProductImage(array $args): array
    {
        $productId = $args['product_id'] ?? null;
        $variantId = $args['variant_id'] ?? null;

        if (! $productId) {
            return ['error' => 'product_id is required'];
        }

        try {
            $product = Product::with('images')->find($productId);
            if (! $product) {
                return ['error' => 'Product not found'];
            }

            $image = null;
            if ($variantId) {
                $variant = ProductVariant::find($variantId);
                if ($variant && $variant->image_path) {
                    $image = asset('storage/'.$variant->image_path);
                }
            }

            if (! $image) {
                $image = $product->images->first()?->image_url;
            }

            if (! $image) {
                return [
                    'sent' => false,
                    'message' => 'এই প্রোডাক্টের কোনো ছবি নেই।',
                ];
            }

            // Queue the image to be sent after AI reply
            $this->pendingImages[] = [
                'product_id' => $product->id,
                'product_name' => $product->name,
                'image_url' => $image,
            ];

            return [
                'sent' => true,
                'product_id' => $product->id,
                'product_name' => $product->name,
                'image_url' => $image,
                'message' => "ছবিটি পাঠানো হচ্ছে: {$product->name}",
            ];
        } catch (\Exception $e) {
            Log::error('ToolExecutor: send_product_image failed', [
                'product_id' => $productId,
                'error' => $e->getMessage(),
            ]);

            return ['error' => 'ছবি পাঠাতে সমস্যা হয়েছে।'];
        }
    }

    private function getCurrentContext(array $args): array
    {
        if (! $this->conversation) {
            return [
                'has_context' => false,
                'message' => 'কোনো কথোপকথনের কন্টেক্সট নেই।',
            ];
        }

        try {
            // Get current product from ProductContextService
            $currentProduct = null;
            $productContextService = new ProductContextService;
            $currentProduct = $productContextService->getCurrentProductContext($this->conversation);

            // Get selected products (multi-product cart)
            $selectedProducts = null;
            $selectionService = new ChatSelectionService;
            $selectedProducts = $selectionService->getItems($this->conversation);

            // Get last few messages for context
            $recentMessages = Message::where('conversation_id', $this->conversation->id)
                ->orderBy('created_at', 'desc')
                ->limit(5)
                ->get()
                ->map(fn ($m) => [
                    'direction' => $m->direction,
                    'content' => mb_substr($m->content, 0, 100),
                    'type' => $m->type,
                    'created_at' => $m->created_at->toIso8601String(),
                ])
                ->reverse()
                ->values()
                ->toArray();

            return [
                'has_context' => $currentProduct !== null || $selectedProducts !== null,
                'current_product' => $currentProduct,
                'selected_products' => $selectedProducts,
                'recent_messages' => $recentMessages,
                'sender_id' => $this->senderId,
            ];
        } catch (\Exception $e) {
            Log::error('ToolExecutor: get_current_context failed', [
                'error' => $e->getMessage(),
            ]);

            return ['error' => 'কন্টেক্সট আনতে সমস্যা হয়েছে।'];
        }
    }

    private function getBusinessInfo(array $args): array
    {
        try {
            $userId = $this->conversation?->user_id;
            if (! $userId) {
                // Try to get from FacebookSetting by sender's page
                return ['error' => 'বিজনেস তথ্য পাওয়া যায়নি।'];
            }

            $businessSetting = BusinessSetting::where('user_id', $userId)->first();
            if (! $businessSetting) {
                return ['error' => 'বিজনেস সেটিংস পাওয়া যায়নি।'];
            }

            $info = [
                'business_name' => $businessSetting->business_name,
                'business_hours' => $businessSetting->business_hours,
                'off_hours_message' => $businessSetting->off_hours_message,
                'business_description' => $businessSetting->business_description,
                'formality_level' => $businessSetting->formality_level,
                'language_style' => $businessSetting->language_style,
                'price_negotiation' => $businessSetting->price_negotiation,
                'negotiation_limit' => $businessSetting->negotiation_limit,
                'current_promo' => $businessSetting->current_promo,
                'cod_available' => $businessSetting->cod_available,
                'accepted_payment_methods' => $businessSetting->accepted_payment_methods,
                'advance_payment_required' => $businessSetting->advance_payment_required,
                'advance_payment_percent' => $businessSetting->advance_payment_percent,
                'refund_policy' => $businessSetting->refund_policy,
                'exchange_policy' => $businessSetting->exchange_policy,
                'escalation_contact' => $businessSetting->escalation_contact,
                'delivery_time' => $businessSetting->delivery_time,
                'delivery_partner' => $businessSetting->delivery_partner,
            ];

            // Remove null values
            return array_filter($info, fn ($v) => $v !== null && $v !== '');
        } catch (\Exception $e) {
            Log::error('ToolExecutor: get_business_info failed', [
                'error' => $e->getMessage(),
            ]);

            return ['error' => 'বিজনেস তথ্য আনতে সমস্যা হয়েছে।'];
        }
    }

    private function getDeliveryCharge(array $args): array
    {
        $area = $args['area'] ?? '';

        if (empty($area)) {
            return ['error' => 'area is required'];
        }

        try {
            $userId = $this->conversation?->user_id;
            if (! $userId) {
                return ['error' => 'ডেলিভারি তথ্য পাওয়া যায়নি।'];
            }

            $businessSetting = BusinessSetting::where('user_id', $userId)->first();
            if (! $businessSetting) {
                return ['error' => 'বিজনেস সেটিংস পাওয়া যায়নি।'];
            }

            $deliveryAreas = $businessSetting->delivery_areas ?? [];
            $areaLower = mb_strtolower($area);

            foreach ($deliveryAreas as $deliveryArea) {
                $name = $deliveryArea['name'] ?? '';
                if (mb_strtolower($name) === $areaLower || str_contains(mb_strtolower($name), $areaLower)) {
                    return [
                        'found' => true,
                        'area' => $name,
                        'charge' => $deliveryArea['price'] ?? 0,
                        'delivery_time' => $businessSetting->delivery_time,
                        'cod_available' => $businessSetting->cod_available,
                    ];
                }
            }

            return [
                'found' => false,
                'message' => " '{$area}' এলাকায় ডেলিভারি তথ্য পাওয়া যায়নি।",
                'available_areas' => array_column($deliveryAreas, 'name'),
            ];
        } catch (\Exception $e) {
            Log::error('ToolExecutor: get_delivery_charge failed', [
                'area' => $area,
                'error' => $e->getMessage(),
            ]);

            return ['error' => 'ডেলিভারি তথ্য আনতে সমস্যা হয়েছে।'];
        }
    }

    private function escalateToHuman(array $args): array
    {
        $reason = $args['reason'] ?? 'Unknown reason';

        try {
            $userId = $this->conversation?->user_id;
            $businessSetting = $userId ? BusinessSetting::where('user_id', $userId)->first() : null;
            $contact = $businessSetting?->escalation_contact ?? null;

            Log::info('ToolExecutor: escalation triggered', [
                'sender_id' => $this->senderId,
                'reason' => $reason,
                'contact' => $contact,
            ]);

            return [
                'escalated' => true,
                'contact' => $contact,
                'message' => $contact
                    ? "আমাদের টিম আপনার সাথে যোগাযোগ করবে। অথবা আপনি এই নম্বরে কল করতে পারেন: {$contact}"
                    : 'আমাদের টিম শীঘ্রই আপনার সাথে যোগাযোগ করবে।',
            ];
        } catch (\Exception $e) {
            Log::error('ToolExecutor: escalate_to_human failed', [
                'error' => $e->getMessage(),
            ]);

            return ['error' => 'এসকালেশনে সমস্যা হয়েছে।'];
        }
    }
}
