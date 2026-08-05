<?php

namespace App\Services\AiTools;

use App\Models\BusinessSetting;
use App\Models\Conversation;
use App\Models\Customer;
use App\Models\Message;
use App\Models\Order;
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
            'get_related_products' => $this->getRelatedProducts($arguments),
            'get_customer_orders' => $this->getCustomerOrders($arguments),
            'check_stock' => $this->checkStock($arguments),
            'send_multiple_products' => $this->sendMultipleProducts($arguments),
            'get_negotiation_rules' => $this->getNegotiationRules($arguments),
            'get_product_faq' => $this->getProductFaq($arguments),
            'get_recommendations' => $this->getRecommendations($arguments),
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
            $bestMatchImageQueued = false;

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
                    $fullProduct = Product::with('variants', 'images')->find($pid);
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

                        // AUTO-IMAGE-SEND: Queue best match image for sending
                        if (! $bestMatchImageQueued && $product['image_url']) {
                            $this->pendingImages[] = [
                                'product_id' => $fullProduct->id,
                                'product_name' => $fullProduct->name,
                                'image_url' => $product['image_url'],
                            ];
                            $bestMatchImageQueued = true;
                            $product['image_auto_sent'] = true;
                        }
                    }
                }

                $products[] = $product;
            }

            return [
                'found' => true,
                'count' => count($products),
                'products' => $products,
                'auto_image_sent' => $bestMatchImageQueued,
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
                    $mediaUrl = config('services.media_url', config('app.url'));
                    $image = $mediaUrl.'/storage/'.$variant->image_path;
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
                    $mediaUrl = config('services.media_url', config('app.url'));
                    $image = $mediaUrl.'/storage/'.$variant->image_path;
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

    // ═══════════════════════════════════════════════════════════════════
    // NEW ADVANCED TOOL IMPLEMENTATIONS
    // ═══════════════════════════════════════════════════════════════════

    private function getRelatedProducts(array $args): array
    {
        $productId = $args['product_id'] ?? null;
        $limit = $args['limit'] ?? 3;

        if (! $productId) {
            return ['error' => 'product_id is required'];
        }

        try {
            $product = Product::with(['category', 'brand', 'images', 'variants'])->find($productId);
            if (! $product) {
                return ['error' => 'Product not found'];
            }

            $relatedQuery = Product::where('id', '!=', $productId)
                ->where('status', 'active')
                ->with(['images', 'variants']);

            // Priority 1: Same category
            if ($product->category_id) {
                $relatedQuery->where('category_id', $product->category_id);
            }

            // Priority 2: Same brand (if available)
            if ($product->brand_id) {
                $relatedQuery->orWhere('brand_id', $product->brand_id);
            }

            $relatedProducts = $relatedQuery->limit($limit + 5)
                ->get()
                ->sortBy(function ($p) use ($product) {
                    // Sort by relevance: same category first, then same brand
                    $score = 0;
                    if ($p->category_id === $product->category_id) {
                        $score += 10;
                    }
                    if ($p->brand_id && $p->brand_id === $product->brand_id) {
                        $score += 5;
                    }
                    // Prefer products in similar price range
                    $priceDiff = abs(($p->discount_price ?? $p->base_price) - ($product->discount_price ?? $product->base_price));
                    $score -= $priceDiff / 1000;

                    return -$score;
                })
                ->take($limit)
                ->toArray();

            // Auto-queue best related product image
            $bestRelated = $relatedProducts[0] ?? null;
            if ($bestRelated) {
                $bestRelatedProduct = Product::with('images')->find($bestRelated['id'] ?? $bestRelated->id ?? null);
                if ($bestRelatedProduct && $bestRelatedProduct->images->first()) {
                    $this->pendingImages[] = [
                        'product_id' => $bestRelatedProduct->id,
                        'product_name' => $bestRelatedProduct->name,
                        'image_url' => $bestRelatedProduct->images->first()->image_url,
                    ];
                }
            }

            $relatedProducts = collect($relatedProducts)->map(fn ($p) => [
                'product_id' => $p->id ?? $p['id'] ?? null,
                'name' => $p->name ?? $p['name'] ?? '',
                'price' => $p->discount_price ?? $p->base_price ?? $p['discount_price'] ?? $p['base_price'] ?? 0,
                'category' => $p->category->name ?? $p['category'] ?? null,
                'brand' => $p->brand->name ?? $p['brand'] ?? null,
                'image_url' => $p->images->first()?->image_url ?? $p['image_url'] ?? null,
                'has_variants' => isset($p->variants) ? $p->variants->where('is_active', true)->count() > 0 : ($p['has_variants'] ?? false),
            ])->values()->toArray();

            return [
                'found' => count($relatedProducts) > 0,
                'source_product' => $product->name,
                'count' => count($relatedProducts),
                'products' => $relatedProducts,
            ];
        } catch (\Exception $e) {
            Log::error('ToolExecutor: get_related_products failed', [
                'product_id' => $productId,
                'error' => $e->getMessage(),
            ]);

            return ['error' => 'সম্পর্কিত প্রোডাক্ট খুঁজে পেতে সমস্যা হয়েছে।'];
        }
    }

    private function getCustomerOrders(array $args): array
    {
        $phone = $args['phone'] ?? '';

        if (empty($phone)) {
            return ['error' => 'phone is required'];
        }

        try {
            // Normalize phone
            $phone = preg_replace('/[\s\-\(\)]+/', '', $phone);
            $phone = ltrim($phone, '+');
            if (str_starts_with($phone, '880')) {
                $phone = substr($phone, 3);
            }
            if (! str_starts_with($phone, '0')) {
                $phone = '0'.$phone;
            }

            $customer = Customer::where('phone', $phone)->first();
            if (! $customer) {
                return [
                    'found' => false,
                    'message' => 'এই ফোন নম্বরে কোনো কাস্টমার পাওয়া যায়নি।',
                ];
            }

            $orders = Order::where('customer_id', $customer->id)
                ->with('items')
                ->orderBy('created_at', 'desc')
                ->limit(5)
                ->get()
                ->map(fn ($order) => [
                    'order_id' => $order->id,
                    'order_number' => $order->order_number,
                    'status' => $order->status,
                    'total' => $order->total,
                    'items_count' => $order->items->count(),
                    'items' => $order->items->map(fn ($item) => [
                        'name' => $item->name,
                        'quantity' => $item->quantity,
                        'unit_price' => $item->unit_price,
                    ])->toArray(),
                    'created_at' => $order->created_at->toDateString(),
                ])
                ->toArray();

            return [
                'found' => true,
                'customer_name' => $customer->name,
                'phone' => $phone,
                'total_orders' => $customer->orders()->count(),
                'recent_orders' => $orders,
            ];
        } catch (\Exception $e) {
            Log::error('ToolExecutor: get_customer_orders failed', [
                'phone' => $phone,
                'error' => $e->getMessage(),
            ]);

            return ['error' => 'কাস্টমারের অর্ডার ইতিহাস আনতে সমস্যা হয়েছে।'];
        }
    }

    private function checkStock(array $args): array
    {
        $productId = $args['product_id'] ?? null;
        $variantId = $args['variant_id'] ?? null;

        if (! $productId) {
            return ['error' => 'product_id is required'];
        }

        try {
            $product = Product::with('variants')->find($productId);
            if (! $product) {
                return ['error' => 'Product not found'];
            }

            if ($variantId) {
                $variant = ProductVariant::find($variantId);
                if (! $variant || $variant->product_id !== $product->id) {
                    return ['error' => 'Variant not found'];
                }

                return [
                    'product_id' => $product->id,
                    'product_name' => $product->name,
                    'variant_id' => $variant->id,
                    'variant_attributes' => $variant->attributes ?? [],
                    'stock_quantity' => $variant->stock_quantity,
                    'in_stock' => $variant->stock_quantity > 0,
                    'is_active' => $variant->is_active,
                ];
            }

            // Return all variants stock info
            $variantsStock = $product->variants
                ->where('is_active', true)
                ->map(fn ($v) => [
                    'variant_id' => $v->id,
                    'attributes' => $v->attributes ?? [],
                    'stock_quantity' => $v->stock_quantity,
                    'in_stock' => $v->stock_quantity > 0,
                ])
                ->values()
                ->toArray();

            return [
                'product_id' => $product->id,
                'product_name' => $product->name,
                'total_stock' => $product->total_stock,
                'in_stock' => $product->total_stock > 0,
                'has_variants' => count($variantsStock) > 0,
                'variants_stock' => $variantsStock,
            ];
        } catch (\Exception $e) {
            Log::error('ToolExecutor: check_stock failed', [
                'product_id' => $productId,
                'error' => $e->getMessage(),
            ]);

            return ['error' => 'স্টক পরীক্ষায় সমস্যা হয়েছে।'];
        }
    }

    private function sendMultipleProducts(array $args): array
    {
        $productIds = $args['product_ids'] ?? [];

        if (empty($productIds)) {
            return ['error' => 'product_ids is required'];
        }

        // Limit to 5 products
        $productIds = array_slice($productIds, 0, 5);

        try {
            $sentImages = [];
            $errors = [];

            foreach ($productIds as $productId) {
                $product = Product::with('images')->find($productId);
                if (! $product) {
                    $errors[] = "Product ID {$productId} not found";

                    continue;
                }

                $image = $product->images->first()?->image_url;
                if (! $image) {
                    $errors[] = "Product {$product->name} has no image";

                    continue;
                }

                $this->pendingImages[] = [
                    'product_id' => $product->id,
                    'product_name' => $product->name,
                    'image_url' => $image,
                ];

                $sentImages[] = [
                    'product_id' => $product->id,
                    'product_name' => $product->name,
                    'image_url' => $image,
                ];
            }

            return [
                'sent_count' => count($sentImages),
                'images' => $sentImages,
                'errors' => $errors,
                'message' => count($sentImages).'টি ছবি পাঠানো হচ্ছে।',
            ];
        } catch (\Exception $e) {
            Log::error('ToolExecutor: send_multiple_products failed', [
                'product_ids' => $productIds,
                'error' => $e->getMessage(),
            ]);

            return ['error' => 'ছবি পাঠাতে সমস্যা হয়েছে।'];
        }
    }

    private function getNegotiationRules(array $args): array
    {
        try {
            $userId = $this->conversation?->user_id;
            if (! $userId) {
                return ['error' => 'নেগোশিয়েশন নিয়ম পাওয়া যায়নি।'];
            }

            $businessSetting = BusinessSetting::where('user_id', $userId)->first();
            if (! $businessSetting) {
                return ['error' => 'বিজনেস সেটিংস পাওয়া যায়নি।'];
            }

            $rules = [
                'price_negotiation' => $businessSetting->price_negotiation,
                'negotiation_limit' => $businessSetting->negotiation_limit,
                'bulk_discount_rule' => $businessSetting->bulk_discount_rule,
                'current_promo' => $businessSetting->current_promo,
            ];

            // Build human-readable rules
            $rulesText = '';
            if ($businessSetting->price_negotiation) {
                $rulesText .= 'দরদাম করা যাবে। ';
                if ($businessSetting->negotiation_limit > 0) {
                    $rulesText .= "সর্বোচ্চ {$businessSetting->negotiation_limit}% পর্যন্ত ছাড় দেওয়া যাবে। ";
                }
            } else {
                $rulesText .= 'দরদাম করা যাবে না, প্রাইস ফিক্সড। ';
            }

            if ($businessSetting->bulk_discount_rule) {
                $rulesText .= "বাল্ক ডিসকাউন্ট: {$businessSetting->bulk_discount_rule} ";
            }

            if ($businessSetting->current_promo) {
                $rulesText .= "বর্তমান অফার: {$businessSetting->current_promo} ";
            }

            return [
                'has_rules' => true,
                'rules' => $rules,
                'rules_text' => $rulesText,
            ];
        } catch (\Exception $e) {
            Log::error('ToolExecutor: get_negotiation_rules failed', [
                'error' => $e->getMessage(),
            ]);

            return ['error' => 'নেগোশিয়েশন নিয়ম আনতে সমস্যা হয়েছে।'];
        }
    }

    private function getProductFaq(array $args): array
    {
        $query = $args['query'] ?? '';

        if (empty($query)) {
            return ['error' => 'query is required'];
        }

        try {
            $userId = $this->conversation?->user_id;
            if (! $userId) {
                return ['error' => 'FAQ পাওয়া যায়নি।'];
            }

            $businessSetting = BusinessSetting::where('user_id', $userId)->first();
            if (! $businessSetting || empty($businessSetting->faq)) {
                return [
                    'found' => false,
                    'message' => 'কোনো FAQ সেট করা নেই।',
                ];
            }

            $faq = $businessSetting->faq;
            $queryLower = mb_strtolower($query);
            $relevantFaq = [];
            $exactMatches = [];
            $partialMatches = [];

            foreach ($faq as $item) {
                $question = $item['question'] ?? '';
                $answer = $item['answer'] ?? '';
                $questionLower = mb_strtolower($question);

                // Exact match
                if (str_contains($questionLower, $queryLower) || str_contains(mb_strtolower($answer), $queryLower)) {
                    $exactMatches[] = ['question' => $question, 'answer' => $answer];
                }
                // Partial match (keywords)
                elseif (str_contains($questionLower, mb_substr($queryLower, 0, 5))) {
                    $partialMatches[] = ['question' => $question, 'answer' => $answer];
                }
            }

            $relevantFaq = array_merge($exactMatches, $partialMatches);

            // Also check order_process_message
            if (! empty($businessSetting->order_process_message) && ! empty($relevantFaq)) {
                array_unshift($relevantFaq, [
                    'question' => 'অর্ডার প্রসেস',
                    'answer' => $businessSetting->order_process_message,
                ]);
            }

            if (empty($relevantFaq)) {
                return [
                    'found' => false,
                    'message' => "'{$query}' সম্পর্কে কোনো FAQ পাওয়া যায়নি।",
                ];
            }

            return [
                'found' => true,
                'count' => count($relevantFaq),
                'faq' => array_slice($relevantFaq, 0, 3),
            ];
        } catch (\Exception $e) {
            Log::error('ToolExecutor: get_product_faq failed', [
                'query' => $query,
                'error' => $e->getMessage(),
            ]);

            return ['error' => 'FAQ খুঁজে পেতে সমস্যা হয়েছে।'];
        }
    }

    private function getRecommendations(array $args): array
    {
        $productId = $args['product_id'] ?? null;
        $limit = $args['limit'] ?? 3;

        if (! $productId) {
            return ['error' => 'product_id is required'];
        }

        try {
            $product = Product::with(['category', 'brand', 'variants', 'images'])->find($productId);
            if (! $product) {
                return ['error' => 'Product not found'];
            }

            $recommendations = [];

            // Method 1: Same category products (different from related - focuses on alternatives)
            $sameCategory = Product::where('category_id', $product->category_id)
                ->where('id', '!=', $productId)
                ->where('status', 'active')
                ->where('stock_quantity', '>', 0)
                ->with(['images', 'variants'])
                ->limit(5)
                ->get();

            foreach ($sameCategory as $p) {
                $price = $p->discount_price ?? $p->base_price;
                $productPrice = $product->discount_price ?? $product->base_price;

                // Only recommend if price is within 50% range
                if (abs($price - $productPrice) / max($productPrice, 1) < 0.5) {
                    $recommendations[] = [
                        'product_id' => $p->id,
                        'name' => $p->name,
                        'price' => $price,
                        'reason' => 'একই ক্যাটাগরির প্রোডাক্ট',
                        'image_url' => $p->images->first()?->image_url ?? null,
                    ];
                }
            }

            // Method 2: Featured products (if not enough recommendations)
            if (count($recommendations) < $limit) {
                $featured = Product::where('is_featured', true)
                    ->where('id', '!=', $productId)
                    ->where('status', 'active')
                    ->where('stock_quantity', '>', 0)
                    ->with(['images', 'variants'])
                    ->limit(3)
                    ->get();

                foreach ($featured as $p) {
                    if (count($recommendations) >= $limit) {
                        break;
                    }
                    // Avoid duplicates
                    if (collect($recommendations)->pluck('product_id')->contains($p->id)) {
                        continue;
                    }
                    $recommendations[] = [
                        'product_id' => $p->id,
                        'name' => $p->name,
                        'price' => $p->discount_price ?? $p->base_price,
                        'reason' => 'জনপ্রিয় প্রোডাক্ট',
                        'image_url' => $p->images->first()?->image_url ?? null,
                    ];
                }
            }

            $recommendations = array_slice($recommendations, 0, $limit);

            // Auto-queue best recommendation image
            $bestRecommendation = $recommendations[0] ?? null;
            if ($bestRecommendation && ! empty($bestRecommendation['image_url'])) {
                $this->pendingImages[] = [
                    'product_id' => $bestRecommendation['product_id'],
                    'product_name' => $bestRecommendation['name'],
                    'image_url' => $bestRecommendation['image_url'],
                ];
            }

            return [
                'found' => count($recommendations) > 0,
                'source_product' => $product->name,
                'count' => count($recommendations),
                'recommendations' => $recommendations,
            ];
        } catch (\Exception $e) {
            Log::error('ToolExecutor: get_recommendations failed', [
                'product_id' => $productId,
                'error' => $e->getMessage(),
            ]);

            return ['error' => 'সুপারিশ খুঁজে পেতে সমস্যা হয়েছে।'];
        }
    }
}
