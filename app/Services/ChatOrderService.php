<?php

namespace App\Services;

use App\Models\BusinessSetting;
use App\Models\Customer;
use App\Models\CustomerAddress;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\ProductVariant;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class ChatOrderService
{
    private const ORDER_DATA_START = '###ORDER_DATA_START###';

    private const ORDER_DATA_END = '###ORDER_DATA_END###';

    /**
     * AI reply theke order data extract koro.
     */
    public function extractOrderData(string $reply): ?array
    {
        $startPos = strpos($reply, self::ORDER_DATA_START);
        $endPos = strpos($reply, self::ORDER_DATA_END);

        if ($startPos === false || $endPos === false) {
            return null;
        }

        $jsonString = substr(
            $reply,
            $startPos + strlen(self::ORDER_DATA_START),
            $endPos - ($startPos + strlen(self::ORDER_DATA_START))
        );

        $jsonString = trim($jsonString);

        $data = json_decode($jsonString, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            Log::warning('ChatOrderService: Failed to parse order JSON', [
                'json' => $jsonString,
                'error' => json_last_error_msg(),
            ]);

            return null;
        }

        if (empty($data['customer_name']) || empty($data['phone']) || empty($data['items'])) {
            Log::warning('ChatOrderService: Missing required fields in order data', [
                'data' => $data,
            ]);

            return null;
        }

        return $data;
    }

    /**
     * Reply theke order data block remove koro — customer ke dekhabena.
     */
    public function removeOrderDataBlock(string $reply): string
    {
        $startPos = strpos($reply, self::ORDER_DATA_START);
        $endPos = strpos($reply, self::ORDER_DATA_END);

        if ($startPos === false || $endPos === false) {
            return $reply;
        }

        $before = substr($reply, 0, $startPos);
        $after = substr($reply, $endPos + strlen(self::ORDER_DATA_END));

        $result = trim($before.$after);

        return $result !== '' ? $result : '';
    }

    /**
     * Check if reply contains ORDER_DATA block markers.
     */
    public function containsOrderDataBlock(string $reply): bool
    {
        return str_contains($reply, self::ORDER_DATA_START) && str_contains($reply, self::ORDER_DATA_END);
    }

    /**
     * Chat order create koro — Customer + Address + Order + OrderItems.
     */
    public function createChatOrder(array $data, int $userId): ?Order
    {
        try {
            DB::beginTransaction();

            // ─── Find or create customer ───────────────────────
            $phone = $this->cleanPhone($data['phone']);
            $customer = Customer::firstOrCreate(
                ['phone' => $phone],
                [
                    'name' => $data['customer_name'],
                    'phone' => $phone,
                    'type' => 'guest',
                    'locale' => 'bn',
                ]
            );

            // ─── Save shipping address ─────────────────────────
            $address = CustomerAddress::create([
                'customer_id' => $customer->id,
                'name' => $data['customer_name'],
                'phone' => $phone,
                'address' => $data['address'] ?? '',
                'city' => $data['city'] ?? '',
                'district' => $data['district'] ?? '',
                'is_default' => true,
            ]);

            // ─── Validate and process items ────────────────────
            $subtotal = 0;
            $orderItems = [];

            foreach ($data['items'] as $item) {
                $product = null;
                $variant = null;
                $unitPrice = $item['unit_price'] ?? 0;
                $quantity = max(1, $item['quantity'] ?? 1);
                $name = $item['name'] ?? '';
                $sku = '';

                // Try to find product by ID first
                if (! empty($item['product_id'])) {
                    $product = Product::find($item['product_id']);
                }

                // If product not found, try by name from conversation history
                if (! $product && $name) {
                    $product = Product::where('name', 'LIKE', '%'.$name.'%')->first();
                }

                if ($product) {
                    $name = $product->name;
                    $sku = $product->sku ?? '';

                    // Resolve the specific variant the customer chose (price must match the quote).
                    // Without this, a product with variants always uses the product-level price,
                    // which ignores the variant price the AI quoted and the customer confirmed.
                    $variant = $this->resolveVariant($product, $item);
                    if ($variant) {
                        $unitPrice = (float) ($variant->price ?? $product->discount_price ?? $product->base_price);
                        $sku = $variant->sku ?? $sku;
                    } else {
                        $unitPrice = (float) ($product->discount_price ?? $product->base_price);
                    }
                }

                if ($unitPrice <= 0) {
                    Log::warning('ChatOrderService: Product price is 0', ['item' => $item]);
                }

                $totalPrice = $unitPrice * $quantity;
                $subtotal += $totalPrice;

                $orderItems[] = [
                    'product_id' => $product?->id,
                    'variant_id' => $variant?->id ?? $item['variant_id'] ?? null,
                    'name' => $name,
                    'sku' => $sku,
                    'quantity' => $quantity,
                    'unit_price' => $unitPrice,
                    'total_price' => $totalPrice,
                ];
            }

            if (empty($orderItems)) {
                DB::rollBack();
                Log::warning('ChatOrderService: No valid items to order');

                return null;
            }

            // ─── Calculate delivery charge ─────────────────────
            $shippingCost = 0;
            $deliveryAreaName = '';
            $businessSetting = BusinessSetting::first();

            if ($businessSetting && ! empty($businessSetting->delivery_areas) && is_array($businessSetting->delivery_areas)) {
                // Match customer's city or district against delivery_areas
                $customerCity = mb_strtolower($data['city'] ?? '');
                $customerDistrict = mb_strtolower($data['district'] ?? '');
                $customerAddress = mb_strtolower($data['address'] ?? '');

                foreach ($businessSetting->delivery_areas as $area) {
                    $areaName = mb_strtolower($area['name'] ?? '');
                    $areaPrice = (float) ($area['price'] ?? 0);

                    if (! $areaName) {
                        continue;
                    }

                    // Check if customer's city/district/address matches this area
                    if (str_contains($customerCity, $areaName)
                        || str_contains($areaName, $customerCity)
                        || str_contains($customerDistrict, $areaName)
                        || str_contains($areaName, $customerDistrict)
                        || str_contains($customerAddress, $areaName)) {
                        $shippingCost = $areaPrice;
                        $deliveryAreaName = $area['name'];
                        break;
                    }
                }

                // If no match found, try to detect Dhaka vs outside Dhaka
                if ($shippingCost === 0 && $deliveryAreaName === '') {
                    $dhakaAreas = ['ঢাকা', 'dhaka', 'mirpur', 'dhanmondi', 'uttara', 'banani', 'gulshan',
                        'motijheel', 'motijhil', 'farmgate', 'farm gate', 'new market', 'newmarket',
                        'elephant road', 'shahbag', 'shahbagh', 'dhanmondi', ' Mohammadpur',
                        'mohammadpur', 'lalmatia', 'lalmatia', 'kazipara', 'kazipara',
                        'technical', 'tikatuli', 'kamrangir char', 'old dhaka', 'puran dhaka'];

                    $isDhaka = false;
                    foreach ($dhakaAreas as $da) {
                        if (str_contains($customerCity, $da) || str_contains($customerDistrict, $da)
                            || str_contains($customerAddress, $da)) {
                            $isDhaka = true;
                            break;
                        }
                    }

                    // Find default prices from delivery_areas
                    $dhakaPrice = 0;
                    $outsidePrice = 0;
                    foreach ($businessSetting->delivery_areas as $area) {
                        $name = mb_strtolower($area['name'] ?? '');
                        $price = (float) ($area['price'] ?? 0);
                        if (str_contains($name, 'ঢাকা') || str_contains($name, 'dhaka')) {
                            if (str_contains($name, 'বাইরে') || str_contains($name, 'outside') || str_contains($name, 'out')) {
                                $outsidePrice = $price;
                            } else {
                                $dhakaPrice = $price;
                            }
                        }
                    }

                    if ($isDhaka) {
                        $shippingCost = $dhakaPrice;
                        $deliveryAreaName = 'ঢাকা';
                    } elseif ($outsidePrice > 0) {
                        $shippingCost = $outsidePrice;
                        $deliveryAreaName = 'ঢাকার বাইরে';
                    }
                }
            }

            // ─── Create order ──────────────────────────────────
            $orderNumber = 'ORD-'.strtoupper(Str::random(8));
            $total = $subtotal + $shippingCost;

            $order = Order::create([
                'customer_id' => $customer->id,
                'customer_name' => $data['customer_name'],
                'customer_phone' => $phone,
                'order_number' => $orderNumber,
                'status' => 'pending',
                'subtotal' => $subtotal,
                'shipping_cost' => $shippingCost,
                'total' => $total,
                'payment_method' => 'COD',
                'payment_status' => 'pending',
                'shipping_address_id' => $address->id,
                'notes' => 'Facebook Chat er maddhome order'.($deliveryAreaName ? " — Delivery: {$deliveryAreaName}" : ''),
            ]);

            foreach ($orderItems as $item) {
                OrderItem::create(array_merge($item, ['order_id' => $order->id]));
            }

            DB::commit();

            $order->load(['items', 'shippingAddress']);

            Log::info('ChatOrderService: Order created successfully', [
                'order_id' => $order->id,
                'order_number' => $order->order_number,
                'customer_id' => $customer->id,
                'subtotal' => $subtotal,
                'shipping_cost' => $shippingCost,
                'delivery_area' => $deliveryAreaName,
                'total' => $total,
            ]);

            return $order;

        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error('ChatOrderService: Order creation failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'data' => $data,
            ]);

            return null;
        }
    }

    /**
     * Resolve the specific product variant the customer chose.
     *
     * Priority:
     * 1. Explicit variant_id from the AI.
     * 2. Match by variant attributes (Size/Color/Weight etc.) — the AI includes
     *    these in the order JSON from the conversation context.
     * 3. Match by the quoted unit_price — if the AI quoted a price that belongs to
     *    exactly one variant, use that variant so the order price matches the quote.
     */
    private function resolveVariant(Product $product, array $item): ?ProductVariant
    {
        $variantId = $item['variant_id'] ?? null;
        $attributes = $item['variant_attributes'] ?? $item['attributes'] ?? null;

        // 1. Exact variant_id
        if ($variantId) {
            $variant = ProductVariant::find($variantId);
            if ($variant && $variant->product_id === $product->id) {
                return $variant;
            }
        }

        // 2. Match by attributes (lenient on keys, strict on values)
        $aiValues = $this->normalizeAttributeValues($attributes);
        if (! empty($aiValues)) {
            foreach ($product->variants->where('is_active', true) as $variant) {
                $variantValues = collect($variant->attributes ?? [])
                    ->map(fn ($v) => $this->normalizeValue($v))
                    ->filter(fn ($v) => $v !== '')
                    ->values()
                    ->all();

                $allMatch = true;
                foreach ($aiValues as $value) {
                    if (! in_array($value, $variantValues, true)) {
                        $allMatch = false;
                        break;
                    }
                }

                if ($allMatch) {
                    return $variant;
                }
            }
        }

        // 3. Match by quoted unit_price (exact match against variant price)
        $aiPrice = (float) ($item['unit_price'] ?? 0);
        if ($aiPrice > 0) {
            $priceMatches = [];
            foreach ($product->variants->where('is_active', true) as $variant) {
                $variantPrice = (float) ($variant->price ?? $product->discount_price ?? $product->base_price);
                if (abs($variantPrice - $aiPrice) < 0.01) {
                    $priceMatches[] = $variant;
                }
            }

            // Only use price match when it is unambiguous
            if (count($priceMatches) === 1) {
                return $priceMatches[0];
            }
        }

        return null;
    }

    private function normalizeAttributeValues($attributes): array
    {
        if (! is_array($attributes) || empty($attributes)) {
            return [];
        }

        $values = [];
        foreach ($attributes as $value) {
            if (is_array($value)) {
                foreach ($value as $v) {
                    $normalized = $this->normalizeValue($v);
                    if ($normalized !== '') {
                        $values[] = $normalized;
                    }
                }
            } else {
                $normalized = $this->normalizeValue($value);
                if ($normalized !== '') {
                    $values[] = $normalized;
                }
            }
        }

        return array_values(array_unique($values));
    }

    private function normalizeValue($value): string
    {
        return mb_strtolower(trim((string) $value));
    }

    /**
     * Bangladeshi phone number clean koro.
     */
    private function cleanPhone(string $phone): string
    {
        $phone = preg_replace('/[\s\-\(\)]+/', '', $phone);
        $phone = ltrim($phone, '+');

        // Country code 880 remove koro
        if (str_starts_with($phone, '880')) {
            $phone = substr($phone, 3);
        }

        // Ensure starts with 0
        if (! str_starts_with($phone, '0')) {
            $phone = '0'.$phone;
        }

        return $phone;
    }
}
