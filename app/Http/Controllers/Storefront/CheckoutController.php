<?php

namespace App\Http\Controllers\Storefront;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\CustomerAddress;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\ProductVariant;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class CheckoutController extends Controller
{
    public function placeOrder(Request $request): JsonResponse
    {
        $isGuest = !$request->user();

        $rules = [
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.variant_id' => 'nullable|exists:product_variants,id',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.name' => 'required|string',
            'items.*.unit_price' => 'required|numeric|min:0',

            'shipping_address.name' => 'required|string|max:255',
            'shipping_address.phone' => 'required|string|max:20',
            'shipping_address.address' => 'required|string|max:500',
            'shipping_address.city' => 'required|string|max:255',
            'shipping_address.district' => 'required|string|max:255',
            'shipping_address.zip' => 'nullable|string|max:20',
        ];

        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $validated = $validator->validated();

        $subtotal = 0;
        foreach ($validated['items'] as $item) {
            $subtotal += $item['unit_price'] * $item['quantity'];
        }

        $orderNumber = 'ORD-' . strtoupper(Str::random(8));

        try {
            DB::beginTransaction();

            // ─── Find or create customer ───────────────────────
            if ($isGuest) {
                $customer = Customer::firstOrCreate(
                    ['phone' => $validated['shipping_address']['phone']],
                    [
                        'name' => $validated['shipping_address']['name'],
                        'phone' => $validated['shipping_address']['phone'],
                        'type' => 'guest',
                        'locale' => app()->getLocale(),
                    ]
                );
            } else {
                $customer = $request->user();
            }

            // ─── Save shipping address ─────────────────────────
            $address = CustomerAddress::create([
                'customer_id' => $customer->id,
                'name' => $validated['shipping_address']['name'],
                'phone' => $validated['shipping_address']['phone'],
                'address' => $validated['shipping_address']['address'],
                'city' => $validated['shipping_address']['city'],
                'district' => $validated['shipping_address']['district'],
                'zip' => $validated['shipping_address']['zip'] ?? null,
            ]);

            // ─── Create order ──────────────────────────────────
            $order = Order::create([
                'customer_id' => $customer->id,
                'customer_name' => $validated['shipping_address']['name'],
                'customer_phone' => $validated['shipping_address']['phone'],
                'order_number' => $orderNumber,
                'status' => 'processing',
                'subtotal' => $subtotal,
                'total' => $subtotal,
                'payment_method' => $validated['payment_method'] ?? 'COD',
                'payment_status' => 'pending',
                'shipping_address_id' => $address->id,
            ]);

            foreach ($validated['items'] as $item) {
                OrderItem::create([
                    'order_id' => $order->id,
                    'product_id' => $item['product_id'],
                    'variant_id' => $item['variant_id'] ?? null,
                    'name' => $item['name'],
                    'sku' => $item['sku'] ?? '',
                    'quantity' => $item['quantity'],
                    'unit_price' => $item['unit_price'],
                    'total_price' => $item['unit_price'] * $item['quantity'],
                ]);
            }

            DB::commit();

            return response()->json([
                'message' => 'Order placed successfully!',
                'order' => [
                    'id' => $order->id,
                    'order_number' => $order->order_number,
                    'total' => $order->total,
                    'status' => $order->status,
                    'customer_phone' => $order->customer_phone,
                ],
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => 'Order failed. Please try again.'], 500);
        }
    }

    public function trackOrder(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'order_number' => 'required|string',
            'phone' => 'required|string|max:20',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $order = Order::where('order_number', $request->order_number)
            ->where(function ($q) use ($request) {
                $q->where('customer_phone', $request->phone)
                  ->orWhereHas('customer', fn($c) => $c->where('phone', $request->phone));
            })
            ->with(['items.product:id,name,slug,image', 'shippingAddress'])
            ->first();

        if (!$order) {
            return response()->json(['message' => 'Order not found.'], 404);
        }

        return response()->json(['data' => $order]);
    }
}
