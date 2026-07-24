<?php

namespace App\Http\Controllers\Storefront;

use App\Http\Controllers\Controller;
use App\Models\CustomerAddress;
use App\Models\Order;
use App\Models\Product;
use App\Models\Wishlist;
use App\Models\Review;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class CustomerController extends Controller
{
    private function customerId(Request $request): int
    {
        return $request->user()->id;
    }

    // ─── Orders ───────────────────────────────────────────────

    public function orders(Request $request): JsonResponse
    {
        $query = Order::where('customer_id', $this->customerId($request))
            ->with(['items.product', 'items.variant'])
            ->orderBy('created_at', 'desc');

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $perPage = $request->get('per_page', 10);
        $orders = $query->paginate($perPage);

        return response()->json($orders);
    }

    public function orderDetail(Request $request, $id): JsonResponse
    {
        $order = Order::where('customer_id', $this->customerId($request))
            ->with(['items.product.images', 'items.variant.images'])
            ->findOrFail($id);

        return response()->json(['data' => $order]);
    }

    public function orderTracking(Request $request, $id): JsonResponse
    {
        $order = Order::where('customer_id', $this->customerId($request))
            ->select('id', 'status', 'tracking_id', 'carrier', 'estimated_delivery', 'tracking_steps')
            ->findOrFail($id);

        return response()->json(['data' => $order]);
    }

    // ─── Addresses ────────────────────────────────────────────

    public function addresses(Request $request): JsonResponse
    {
        $addresses = CustomerAddress::where('customer_id', $this->customerId($request))
            ->orderBy('is_default', 'desc')
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json($addresses);
    }

    public function storeAddress(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'label' => 'nullable|string|max:50',
            'name' => 'required|string|max:255',
            'phone' => 'required|string|max:20',
            'address' => 'required|string|max:500',
            'city' => 'required|string|max:255',
            'district' => 'required|string|max:255',
            'zip' => 'nullable|string|max:20',
            'is_default' => 'boolean',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $data = $validator->validated();
        $data['customer_id'] = $this->customerId($request);

        if (!empty($data['is_default'])) {
            CustomerAddress::where('customer_id', $this->customerId($request))->update(['is_default' => false]);
        }

        $address = CustomerAddress::create($data);

        return response()->json(['message' => 'Address added.', 'data' => $address], 201);
    }

    public function updateAddress(Request $request, $id): JsonResponse
    {
        $address = CustomerAddress::where('customer_id', $this->customerId($request))->findOrFail($id);

        $validator = Validator::make($request->all(), [
            'label' => 'nullable|string|max:50',
            'name' => 'sometimes|string|max:255',
            'phone' => 'sometimes|string|max:20',
            'address' => 'sometimes|string|max:500',
            'city' => 'sometimes|string|max:255',
            'district' => 'sometimes|string|max:255',
            'zip' => 'nullable|string|max:20',
            'is_default' => 'boolean',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $data = $validator->validated();

        if (!empty($data['is_default'])) {
            CustomerAddress::where('customer_id', $this->customerId($request))->update(['is_default' => false]);
        }

        $address->update($data);

        return response()->json(['message' => 'Address updated.', 'data' => $address->fresh()]);
    }

    public function deleteAddress(Request $request, $id): JsonResponse
    {
        $address = CustomerAddress::where('customer_id', $this->customerId($request))->findOrFail($id);
        $address->delete();

        return response()->json(['message' => 'Address deleted.']);
    }

    // ─── Wishlist ─────────────────────────────────────────────

    public function wishlist(Request $request): JsonResponse
    {
        $items = Wishlist::where('customer_id', $this->customerId($request))
            ->with(['product.images', 'product.category', 'product.brand'])
            ->latest()
            ->get()
            ->map(fn($w) => $w->product);

        return response()->json($items);
    }

    public function addToWishlist(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'product_id' => 'required|exists:products,id',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $existing = Wishlist::where('customer_id', $this->customerId($request))
            ->where('product_id', $request->product_id)
            ->first();

        if ($existing) {
            return response()->json(['message' => 'Already in wishlist.']);
        }

        Wishlist::create([
            'customer_id' => $this->customerId($request),
            'product_id' => $request->product_id,
        ]);

        return response()->json(['message' => 'Added to wishlist.'], 201);
    }

    public function removeFromWishlist(Request $request, $id): JsonResponse
    {
        Wishlist::where('customer_id', $this->customerId($request))
            ->where('product_id', $id)
            ->delete();

        return response()->json(['message' => 'Removed from wishlist.']);
    }

    // ─── Reviews ──────────────────────────────────────────────

    public function reviews(Request $request): JsonResponse
    {
        $reviews = Review::where('customer_id', $this->customerId($request))
            ->with('product:id,name,slug')
            ->latest()
            ->get();

        return response()->json($reviews);
    }

    public function storeReview(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'product_id' => 'required|exists:products,id',
            'rating' => 'required|integer|min:1|max:5',
            'text' => 'nullable|string|max:1000',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $review = Review::updateOrCreate(
            [
                'customer_id' => $this->customerId($request),
                'product_id' => $request->product_id,
            ],
            [
                'rating' => $request->rating,
                'text' => $request->text,
            ]
        );

        return response()->json(['message' => 'Review submitted.', 'data' => $review], 201);
    }

    // ─── Dashboard Stats ──────────────────────────────────────

    public function dashboardStats(Request $request): JsonResponse
    {
        $customerId = $this->customerId($request);

        $totalOrders = Order::where('customer_id', $customerId)->count();
        $deliveredOrders = Order::where('customer_id', $customerId)->where('status', 'delivered')->count();
        $totalSpent = Order::where('customer_id', $customerId)->where('status', 'delivered')->sum('total');
        $wishlistCount = Wishlist::where('customer_id', $customerId)->count();

        return response()->json([
            'total_orders' => $totalOrders,
            'delivered_orders' => $deliveredOrders,
            'total_spent' => $totalSpent,
            'wishlist_count' => $wishlistCount,
        ]);
    }
}
