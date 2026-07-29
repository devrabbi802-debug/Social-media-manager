<?php

namespace App\Services;

use App\Models\Customer;
use App\Models\CustomerAddress;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
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

                // If product found, use its actual price and details
                if ($product) {
                    $name = $product->name;
                    $unitPrice = (float) $product->price;
                    $sku = $product->sku ?? '';
                } else {
                    // Try to find by name from conversation history
                    $product = Product::where('name', 'LIKE', '%'.$name.'%')->first();
                    if ($product) {
                        $name = $product->name;
                        $unitPrice = (float) $product->price;
                        $sku = $product->sku ?? '';
                    }
                }

                if ($unitPrice <= 0) {
                    Log::warning('ChatOrderService: Product price is 0', ['item' => $item]);
                }

                $totalPrice = $unitPrice * $quantity;
                $subtotal += $totalPrice;

                $orderItems[] = [
                    'product_id' => $product?->id,
                    'variant_id' => $item['variant_id'] ?? null,
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

            // ─── Create order ──────────────────────────────────
            $orderNumber = 'ORD-'.strtoupper(Str::random(8));

            $order = Order::create([
                'customer_id' => $customer->id,
                'customer_name' => $data['customer_name'],
                'customer_phone' => $phone,
                'order_number' => $orderNumber,
                'status' => 'pending',
                'subtotal' => $subtotal,
                'total' => $subtotal,
                'payment_method' => 'COD',
                'payment_status' => 'pending',
                'shipping_address_id' => $address->id,
                'notes' => 'Facebook Chat er maddhome order',
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
                'total' => $subtotal,
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
