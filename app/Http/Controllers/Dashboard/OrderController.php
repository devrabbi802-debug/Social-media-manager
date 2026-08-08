<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\AccountingSetting;
use App\Models\Category;
use App\Models\ChartOfAccount;
use App\Models\CompanySetting;
use App\Models\Customer;
use App\Models\CustomerAddress;
use App\Models\JournalEntry;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\OrderReturn;
use App\Models\OrderReturnItem;
use App\Models\PosSetting;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\StockMovement;
use App\Models\StorefrontSettings;
use App\Models\Warehouse;
use App\Services\AccountingService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class OrderController extends Controller
{
    public function index(Request $request)
    {
        $query = Order::with(['items', 'customer']);

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('order_number', 'like', "%{$search}%")
                    ->orWhere('customer_name', 'like', "%{$search}%")
                    ->orWhere('customer_phone', 'like', "%{$search}%");
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('payment_status')) {
            $query->where('payment_status', $request->payment_status);
        }

        if ($request->filled('payment_method')) {
            $query->where('payment_method', $request->payment_method);
        }

        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        $sortField = $request->get('sort', 'created_at');
        $sortDir = $request->get('dir', 'desc');
        $allowedSorts = ['created_at', 'order_number', 'total', 'status'];
        if (in_array($sortField, $allowedSorts)) {
            $query->orderBy($sortField, $sortDir === 'asc' ? 'asc' : 'desc');
        } else {
            $query->latest();
        }

        $orders = $query->paginate(20)->withQueryString();

        $totalOrders = Order::count();
        $pendingCount = Order::where('status', 'pending')->count();
        $processingCount = Order::where('status', 'processing')->count();
        $deliveredCount = Order::where('status', 'delivered')->count();
        $cancelledCount = Order::where('status', 'cancelled')->count();
        $totalRevenue = Order::whereNotIn('status', ['cancelled', 'refunded'])->sum('total');

        return view('tenant.orders.index', compact(
            'orders', 'totalOrders', 'pendingCount', 'processingCount',
            'deliveredCount', 'cancelledCount', 'totalRevenue'
        ));
    }

    public function show(Request $request, Order $order)
    {
        $order->load(['items.product.primaryImage', 'items.variant', 'customer', 'shippingAddress', 'returns.items', 'childOrders', 'parentOrder']);
        $paymentMethods = ['cash', 'bkash', 'nagad', 'rocket', 'upay', 'bank', 'card'];

        return view('tenant.orders.show', compact('order', 'paymentMethods'));
    }

    /**
     * List all returns and exchanges across orders.
     */
    public function returns(Request $request)
    {
        $query = OrderReturn::with(['order', 'items', 'user'])
            ->orderByDesc('created_at');

        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('return_number', 'like', "%{$search}%")
                    ->orWhereHas('order', fn ($oq) => $oq->where('order_number', 'like', "%{$search}%"));
            });
        }

        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        $returns = $query->paginate(25)->withQueryString();

        return view('tenant.orders.returns', compact('returns'));
    }

    /**
     * Show the "create manual order" page.
     */
    public function create()
    {
        $categories = Category::orderBy('name')->get();
        $customers = Customer::orderByDesc('id')->with('addresses')->limit(50)->get();
        $warehouses = Warehouse::where('is_active', true)->orderBy('name')->get();
        $companySettings = CompanySetting::current();
        $paymentAccounts = $this->getPaymentAccounts();

        return view('tenant.orders.create', compact('categories', 'customers', 'warehouses', 'companySettings', 'paymentAccounts'));
    }

    /**
     * JSON product search for the manual order picker.
     */
    public function products(Request $request)
    {
        $search = $request->query('search');
        $categoryId = $request->query('category_id');
        $warehouseId = $request->query('warehouse_id');

        $query = Product::with(['category', 'variants' => fn ($q) => $q->active()])->active();

        if ($categoryId) {
            $query->where('category_id', $categoryId);
        }
        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('sku', 'like', "%{$search}%")
                    ->orWhere('barcode', 'like', "%{$search}%");
            });
        }

        $products = $query->orderBy('name')->paginate(50);
        $baseUrl = request()->getSchemeAndHttpHost();

        $data = $products->map(function (Product $product) use ($baseUrl, $warehouseId) {
            $image = $product->primaryImage;

            $stock = $warehouseId
                ? $this->warehouseStock($product->id, null, $warehouseId)
                : (int) $product->stock_quantity;

            $variants = $product->variants->map(function (ProductVariant $v) use ($warehouseId) {
                $stock = $warehouseId
                    ? $this->warehouseStock($v->product_id, $v->id, $warehouseId)
                    : (int) $v->stock_quantity;

                return [
                    'id' => $v->id,
                    'name' => $v->display,
                    'sku' => $v->sku,
                    'price' => (float) ($v->price ?? $product->price),
                    'stock' => $stock,
                ];
            })->values();

            return [
                'id' => $product->id,
                'name' => $product->name,
                'sku' => $product->sku,
                'price' => (float) $product->price,
                'stock' => $stock,
                'image' => $image ? $baseUrl.'/storage/'.$image->image_path : null,
                'has_variants' => $product->has_variants,
                'variants' => $variants,
            ];
        });

        return response()->json([
            'data' => $data,
            'next_page' => $products->currentPage() < $products->lastPage() ? $products->currentPage() + 1 : null,
        ]);
    }

    /**
     * Calculate stock for a specific warehouse from StockMovement records.
     */
    protected function warehouseStock(int $productId, ?int $variantId, int $warehouseId): int
    {
        $query = StockMovement::where('warehouse_id', $warehouseId)
            ->where('product_id', $productId);

        if ($variantId) {
            $query->where('variant_id', $variantId);
        } else {
            $query->whereNull('variant_id');
        }

        $in = (clone $query)->where('type', 'in')->sum('quantity');
        $out = (clone $query)->where('type', 'out')->sum('quantity');
        $adjustment = (clone $query)->where('type', 'adjustment')->sum('quantity');

        return max(0, (int) ($in - $out + $adjustment));
    }

    protected function getPaymentAccounts()
    {
        $settings = AccountingSetting::current();
        $ids = array_filter([$settings->default_cash_account_id, $settings->default_bank_account_id]);

        return ChartOfAccount::whereIn('id', $ids)
            ->orWhere('is_pos_payment', true)
            ->orderBy('code')
            ->get();
    }

    protected function resolveWarehouse(?int $warehouseId = null): ?Warehouse
    {
        if ($warehouseId) {
            return Warehouse::find($warehouseId);
        }

        // 1. Check Company Settings (primary)
        $companyWarehouse = CompanySetting::current()->default_warehouse_id;
        if ($companyWarehouse) {
            $wh = Warehouse::find($companyWarehouse);
            if ($wh) {
                return $wh;
            }
        }

        // 2. Fallback to POS Settings
        $settings = PosSetting::current();
        if ($settings->default_warehouse_id) {
            $wh = Warehouse::find($settings->default_warehouse_id);
            if ($wh) {
                return $wh;
            }
        }

        // 3. First active warehouse
        return Warehouse::where('is_active', true)->first();
    }

    protected function validateOrderStock(array $items): void
    {
        foreach ($items as $item) {
            if (! empty($item['variant_id'])) {
                $variant = ProductVariant::find($item['variant_id']);
                if (! $variant || $variant->stock_quantity < (int) $item['quantity']) {
                    $label = $variant?->display ?? 'ভেরিয়েন্ট';
                    $stock = $variant?->stock_quantity ?? 0;
                    throw new \Exception("পর্যাপ্ত স্টক নেই: {$label} (স্টক: {$stock})");
                }
            } else {
                $product = Product::find($item['product_id']);
                if (! $product || $product->stock_quantity < (int) $item['quantity']) {
                    $label = $product?->name ?? 'পণ্য';
                    $stock = $product?->stock_quantity ?? 0;
                    throw new \Exception("পর্যাপ্ত স্টক নেই: {$label} (স্টক: {$stock})");
                }
            }
        }
    }

    public function deductStock(Product $product, ?ProductVariant $variant, int $quantity, Warehouse $warehouse, string $reference, string $notes = 'Manual Order'): void
    {
        if ($variant) {
            $variant->decrement('stock_quantity', $quantity);
            $variant->refresh();
            $product->recalculateStock();
        } else {
            $product->decrement('stock_quantity', $quantity);
            $product->refresh();
        }

        if ($product->stock_quantity <= 0 && $product->status !== 'inactive') {
            $product->updateQuietly(['status' => 'out_of_stock']);
        }

        StockMovement::create([
            'product_id' => $product->id,
            'variant_id' => $variant?->id,
            'warehouse_id' => $warehouse->id,
            'type' => 'out',
            'quantity' => $quantity,
            'reference' => $reference,
            'notes' => $notes,
            'created_by' => auth()->id(),
        ]);
    }

    public function restockItem(OrderItem $item, int $quantity, Warehouse $warehouse, string $reference, string $notes = 'Order Return'): void
    {
        if (! $item->product_id) {
            return;
        }

        $product = $item->product;
        if (! $product) {
            return;
        }

        if ($item->variant_id) {
            $item->variant?->increment('stock_quantity', $quantity);
            $product->recalculateStock();
        } else {
            $product->increment('stock_quantity', $quantity);
            $product->refresh();
        }

        StockMovement::create([
            'product_id' => $product->id,
            'variant_id' => $item->variant_id,
            'warehouse_id' => $warehouse->id,
            'type' => 'in',
            'quantity' => $quantity,
            'reference' => $reference,
            'notes' => $notes,
            'created_by' => auth()->id(),
        ]);
    }

    /**
     * Create a manual (admin) order: items + customer + shipping + payment.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'items_json' => 'required|json',
            'customer_id' => 'nullable|exists:customers,id',
            'customer_name' => 'nullable|string|max:255',
            'customer_phone' => 'nullable|string|max:50',
            'shipping_cost' => 'nullable|numeric|min:0',
            'discount' => 'nullable|numeric|min:0',
            'tax' => 'nullable|numeric|min:0',
            'payment_method' => 'nullable|string|max:50',
            'payment_status' => 'required|in:pending,paid,partial',
            'received_amount' => 'nullable|numeric|min:0',
            'status' => 'required|in:pending,processing,shipped,delivered',
            'carrier' => 'nullable|string|max:255',
            'tracking_id' => 'nullable|string|max:255',
            'estimated_delivery' => 'nullable|date',
            'address' => 'nullable|string|max:500',
            'city' => 'nullable|string|max:255',
            'district' => 'nullable|string|max:255',
            'zip' => 'nullable|string|max:20',
            'notes' => 'nullable|string|max:2000',
            'warehouse_id' => 'nullable|exists:warehouses,id',
            'payments_json' => 'nullable|json',
        ]);

        $items = json_decode($validated['items_json'], true);
        $payments = json_decode($validated['payments_json'] ?? '[]', true) ?: [];
        if (empty($items) || ! is_array($items)) {
            return back()->with('error', 'অর্ডারে কোনো পণ্য যোগ করা হয়নি।');
        }

        foreach ($items as $key => $item) {
            if (! isset($item['product_id'], $item['name'], $item['quantity'], $item['unit_price'])) {
                return back()->with('error', 'পণ্যের তথ্য অসম্পূর্ণ।');
            }
            $items[$key]['quantity'] = (int) $item['quantity'];
            $items[$key]['unit_price'] = (float) $item['unit_price'];
        }

        $warehouse = $this->resolveWarehouse($validated['warehouse_id'] ?? null);
        if (! $warehouse) {
            return back()->with('error', 'কোনো ওয়ারহাউস পাওয়া যায়নি। আগে একটি ওয়ারহাউস তৈরি করুন।');
        }

        try {
            $order = DB::transaction(function () use ($items, $validated, $warehouse) {
                $this->validateOrderStock($items);

                // ── Customer ──
                if (! empty($validated['customer_id'])) {
                    $customer = Customer::find($validated['customer_id']);
                } else {
                    $customer = Customer::firstOrCreate(
                        ['phone' => $validated['customer_phone'] ?? null],
                        [
                            'name' => $validated['customer_name'] ?? null,
                            'phone' => $validated['customer_phone'] ?? null,
                            'type' => 'guest',
                            'locale' => app()->getLocale(),
                        ]
                    );
                }

                // ── Shipping address ──
                $address = null;
                if ($request->filled('address')) {
                    $address = CustomerAddress::create([
                        'customer_id' => $customer->id,
                        'name' => $validated['customer_name'] ?? $customer->name,
                        'phone' => $validated['customer_phone'] ?? $customer->phone,
                        'address' => $validated['address'],
                        'city' => $validated['city'] ?? null,
                        'district' => $validated['district'] ?? null,
                        'zip' => $validated['zip'] ?? null,
                    ]);
                }

                // ── Totals ──
                $subtotal = collect($items)->sum(fn ($i) => $i['unit_price'] * $i['quantity']);
                $shipping = (float) ($validated['shipping_cost'] ?? 0);
                $discount = (float) ($validated['discount'] ?? 0);
                $tax = (float) ($validated['tax'] ?? 0);
                $total = round($subtotal + $shipping + $tax - $discount, 2);

                // ── Order ──
                $order = Order::create([
                    'customer_id' => $customer->id,
                    'customer_name' => $validated['customer_name'] ?? $customer->name,
                    'customer_phone' => $validated['customer_phone'] ?? $customer->phone,
                    'order_number' => 'ORD-'.strtoupper(Str::random(8)),
                    'status' => $validated['status'],
                    'subtotal' => $subtotal,
                    'shipping_cost' => $shipping,
                    'tax' => $tax,
                    'discount' => $discount,
                    'total' => $total,
                    'payment_method' => $validated['payment_method'],
                    'payment_status' => $validated['payment_status'],
                    'carrier' => $validated['carrier'] ?? null,
                    'tracking_id' => $validated['tracking_id'] ?? null,
                    'estimated_delivery' => $validated['estimated_delivery'] ?? null,
                    'shipping_address_id' => $address?->id,
                    'notes' => $validated['notes'] ?? null,
                ]);

                // ── Items + stock ──
                foreach ($items as $item) {
                    $product = Product::find($item['product_id']);
                    $variant = ! empty($item['variant_id']) ? ProductVariant::find($item['variant_id']) : null;

                    OrderItem::create([
                        'order_id' => $order->id,
                        'product_id' => $product?->id,
                        'variant_id' => $variant?->id,
                        'name' => $item['name'],
                        'sku' => $variant?->sku ?? $product?->sku ?? null,
                        'quantity' => $item['quantity'],
                        'unit_price' => $item['unit_price'],
                        'total_price' => $item['unit_price'] * $item['quantity'],
                    ]);

                    $this->deductStock($product, $variant, $item['quantity'], $warehouse, $order->order_number);
                }

                // ── Accounting ──
                if (AccountingSetting::current()->post_storefront_orders) {
                    app(AccountingService::class)->postOrder($order);
                    $totalReceived = collect($payments)->sum('amount');
                    if ($validated['payment_status'] === 'paid' && $totalReceived > 0) {
                        foreach ($payments as $pay) {
                            if ((float) ($pay['amount'] ?? 0) > 0) {
                                app(AccountingService::class)->receiveOrderPayment(
                                    $order,
                                    (float) $pay['amount'],
                                    $pay['method'] ?? $validated['payment_method']
                                );
                            }
                        }
                    }
                }

                return $order;
            });

            return redirect()->route('orders.show', $order)
                ->with('success', __('orders.created'));

        } catch (ValidationException $e) {
            throw $e;
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    /**
     * Process a return (refund) on an order — partial or full.
     */
    public function processReturn(Request $request, Order $order)
    {
        $validated = $request->validate([
            'items' => 'required|array',
            'items.*.order_item_id' => 'required|integer|exists:order_items,id',
            'items.*.quantity' => 'required|integer|min:0',
            'method' => 'required|string|max:50',
            'reason' => 'nullable|string|max:2000',
        ]);

        if ($order->status === 'refunded') {
            return back()->with('error', 'এই অর্ডারটি ইতিমধ্যে সম্পূর্ণ রিফান্ড হয়েছে।');
        }

        $warehouse = $this->resolveWarehouse(null);
        if (! $warehouse) {
            return back()->with('error', 'কোনো ওয়ারহাউস পাওয়া যায়নি।');
        }

        try {
            DB::transaction(function () use ($order, $validated, $warehouse) {
                $order->load(['items.product', 'items.variant']);

                $returnableItems = collect($validated['items'])
                    ->filter(fn ($i) => (int) $i['quantity'] > 0)
                    ->values();

                if ($returnableItems->isEmpty()) {
                    throw new \Exception('কোনো আইটেম রিটার্নের জন্য নির্বাচন করা হয়নি।');
                }

                $amount = 0;
                $returnedCost = 0;
                $return = OrderReturn::create([
                    'order_id' => $order->id,
                    'user_id' => auth()->id(),
                    'type' => 'return',
                    'amount' => 0,
                    'method' => $validated['method'],
                    'status' => 'completed',
                    'reason' => $validated['reason'] ?? null,
                    'returned_at' => now(),
                ]);

                foreach ($returnableItems as $ri) {
                    $orderItem = $order->items->firstWhere('id', $ri['order_item_id']);
                    if (! $orderItem) {
                        continue;
                    }

                    $maxQty = $orderItem->quantity - $order->returnedQuantity($orderItem);
                    $qty = min((int) $ri['quantity'], max($maxQty, 0));
                    if ($qty <= 0) {
                        continue;
                    }

                    $lineTotal = round((float) $orderItem->unit_price * $qty, 2);
                    $cost = $orderItem->variant?->cost_price ?? $orderItem->product?->cost_price ?? 0;
                    $returnedCost += (float) $cost * $qty;
                    $amount += $lineTotal;

                    OrderReturnItem::create([
                        'order_return_id' => $return->id,
                        'order_item_id' => $orderItem->id,
                        'product_id' => $orderItem->product_id,
                        'variant_id' => $orderItem->variant_id,
                        'name' => $orderItem->name,
                        'sku' => $orderItem->sku,
                        'quantity' => $qty,
                        'unit_price' => $orderItem->unit_price,
                        'total_price' => $lineTotal,
                    ]);

                    $this->restockItem($orderItem, $qty, $warehouse, $return->return_number);
                }

                if ($order->returnedTotal() + $amount <= 0.01) {
                    throw new \Exception('রিটার্ন/রিফান্ডের পরিমাণ শূন্য।');
                }

                $return->update(['amount' => round($amount, 2)]);

                // Order status / payment sync
                $fullyReturned = $order->items->sum(function ($it) use ($order) {
                    return $order->returnedQuantity($it);
                }) >= $order->items->sum('quantity');

                if ($fullyReturned) {
                    $order->status = 'refunded';
                    $order->payment_status = 'refunded';
                } elseif ($amount >= $order->total - 0.01) {
                    $order->payment_status = 'refunded';
                } elseif ($order->payment_status === 'paid') {
                    $order->payment_status = 'refunded';
                }

                $order->save();

                app(AccountingService::class)->postOrderReturn($order, $return, $returnedCost);
            });

            return back()->with('success', 'রিটার্ন সফলভাবে সম্পন্ন হয়েছে! স্টক পুনরায় যুক্ত হয়েছে।');
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    /**
     * Process an exchange: return selected items and add replacement items.
     *
     * The returned items are restocked and the exchange difference is either
     * charged (customer pays extra) or refunded (customer gets money back).
     */
    public function exchange(Request $request, Order $order)
    {
        $validated = $request->validate([
            'return_items' => 'nullable|array',
            'return_items.*.order_item_id' => 'required|integer|exists:order_items,id',
            'return_items.*.quantity' => 'required|integer|min:0',
            'exchange_items_json' => 'required|json',
            'method' => 'required|string|max:50',
            'reason' => 'nullable|string|max:2000',
        ]);

        $exchangeItems = json_decode($validated['exchange_items_json'], true);

        if (empty($exchangeItems) || ! is_array($exchangeItems)) {
            return back()->with('error', 'এক্সচেঞ্জের জন্য নতুন কোনো পণ্য যোগ করা হয়নি।');
        }

        foreach ($exchangeItems as $key => $item) {
            if (! isset($item['product_id'], $item['name'], $item['quantity'], $item['unit_price'])) {
                return back()->with('error', 'এক্সচেঞ্জ পণ্যের তথ্য অসম্পূর্ণ।');
            }
            $exchangeItems[$key]['quantity'] = (int) $item['quantity'];
            $exchangeItems[$key]['unit_price'] = (float) $item['unit_price'];
        }

        $warehouse = $this->resolveWarehouse(null);
        if (! $warehouse) {
            return back()->with('error', 'কোনো ওয়ারহাউস পাওয়া যায়নি।');
        }

        try {
            DB::transaction(function () use ($order, $validated, $exchangeItems, $warehouse) {
                $order->load(['items.product', 'items.variant']);

                // 1) Return the selected old items + restock
                $return = OrderReturn::create([
                    'order_id' => $order->id,
                    'user_id' => auth()->id(),
                    'type' => 'exchange',
                    'amount' => 0,
                    'method' => $validated['method'],
                    'status' => 'completed',
                    'reason' => $validated['reason'] ?? null,
                    'returned_at' => now(),
                ]);

                $returnedAmount = 0;
                $returnedCost = 0;

                foreach ((array) $validated['return_items'] as $ri) {
                    $orderItem = $order->items->firstWhere('id', $ri['order_item_id']);
                    if (! $orderItem) {
                        continue;
                    }
                    $maxQty = $orderItem->quantity - $order->returnedQuantity($orderItem);
                    $qty = min((int) $ri['quantity'], max($maxQty, 0));
                    if ($qty <= 0) {
                        continue;
                    }

                    $lineTotal = round((float) $orderItem->unit_price * $qty, 2);
                    $cost = $orderItem->variant?->cost_price ?? $orderItem->product?->cost_price ?? 0;
                    $returnedCost += (float) $cost * $qty;
                    $returnedAmount += $lineTotal;

                    OrderReturnItem::create([
                        'order_return_id' => $return->id,
                        'order_item_id' => $orderItem->id,
                        'product_id' => $orderItem->product_id,
                        'variant_id' => $orderItem->variant_id,
                        'name' => $orderItem->name,
                        'sku' => $orderItem->sku,
                        'quantity' => $qty,
                        'unit_price' => $orderItem->unit_price,
                        'total_price' => $lineTotal,
                    ]);

                    $this->restockItem($orderItem, $qty, $warehouse, $return->return_number);
                }

                // 2) Validate + deduct stock for the replacement items
                $this->validateOrderStock($exchangeItems);

                $newSubtotal = 0;
                $newCost = 0;

                foreach ($exchangeItems as $ei) {
                    $product = Product::find($ei['product_id']);
                    $variant = ! empty($ei['variant_id']) ? ProductVariant::find($ei['variant_id']) : null;

                    $orderItem = OrderItem::create([
                        'order_id' => $order->id,
                        'product_id' => $product?->id,
                        'variant_id' => $variant?->id,
                        'name' => $ei['name'],
                        'sku' => $variant?->sku ?? $product?->sku ?? null,
                        'quantity' => $ei['quantity'],
                        'unit_price' => $ei['unit_price'],
                        'total_price' => round($ei['unit_price'] * $ei['quantity'], 2),
                    ]);

                    $newSubtotal += $ei['unit_price'] * $ei['quantity'];
                    $newCost += (float) ($variant?->cost_price ?? $product?->cost_price ?? 0) * $ei['quantity'];

                    $this->deductStock($product, $variant, $ei['quantity'], $warehouse, $return->return_number, 'Order Exchange');
                }

                // 3. Money difference
                $difference = round($newSubtotal - $returnedAmount, 2);

                $oldTotal = (float) $order->total;
                $adjust = $difference;

                if ($adjust > 0) {
                    // customer owes extra → hang the extra on the order total
                    $order->total = round($oldTotal + $adjust, 2);
                    $order->subtotal = round((float) $order->subtotal + $adjust, 2);
                } elseif ($adjust < 0) {
                    // customer gets money back → record the refund + lower the total
                    $diffRefund = OrderReturn::create([
                        'order_id' => $order->id,
                        'user_id' => auth()->id(),
                        'type' => 'return',
                        'amount' => abs($adjust),
                        'method' => $order->payment_method ?? 'cash',
                        'status' => 'completed',
                        'reason' => 'Exchange price difference return',
                        'returned_at' => now(),
                    ]);

                    $order->total = round($oldTotal - abs($adjust), 2);
                    $order->subtotal = round((float) $order->subtotal - abs($adjust), 2);
                }

                $return->update(['amount' => round($returnedAmount, 2)]);

                // Accounting for the exchange (restock + new goods + money diff)
                app(AccountingService::class)->postExchange($order, $return, $newCost, $difference);

                $order->save();

                // Order status: fully returned/exchanged ⇒ mark refunded
                if ($order->items->sum(fn ($it) => $order->returnedQuantity($it)) >= $order->items->sum('quantity')) {
                    $order->status = 'refunded';
                    $order->save();
                }
            });

            return back()->with('success', 'এক্সচেঞ্জ সফলভাবে সম্পন্ন হয়েছে!');
        } catch (ValidationException $e) {
            throw $e;
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    public function edit(Order $order)
    {
        $order->load(['items', 'customer', 'shippingAddress']);
        $statuses = ['pending', 'processing', 'shipped', 'delivered', 'cancelled', 'refunded'];
        $paymentStatuses = ['pending', 'paid', 'failed', 'refunded'];

        return view('tenant.orders.edit', compact('order', 'statuses', 'paymentStatuses'));
    }

    public function update(Request $request, Order $order)
    {
        $validated = $request->validate([
            'status' => 'sometimes|in:pending,processing,shipped,delivered,cancelled,refunded',
            'payment_status' => 'sometimes|in:pending,paid,failed,refunded',
            'notes' => 'nullable|string|max:2000',
            'carrier' => 'nullable|string|max:255',
            'tracking_id' => 'nullable|string|max:255',
            'estimated_delivery' => 'nullable|date',
        ]);

        $order->update($validated);

        // Keep the ledger in sync: cancelling/refunding an order reverses its accounting entries
        if (in_array($order->status, ['cancelled', 'refunded'], true)) {
            app(AccountingService::class)->reverseOrderEntries($order);
        }

        return redirect()->route('orders.show', $order)
            ->with('success', __('orders.updated'));
    }

    /**
     * Record a customer payment against an order (receivable → cash/bank).
     */
    public function receivePayment(Request $request, Order $order)
    {
        $validated = $request->validate([
            'amount' => 'required|numeric|min:0.01',
            'payment_method' => 'required|string|max:50',
            'reference' => 'nullable|string|max:255',
        ]);

        $service = app(AccountingService::class);
        $accounting = AccountingSetting::current();

        DB::transaction(function () use ($service, $order, $validated) {
            // Ensure the sale is on the books even if auto-post was off
            $saleEntry = JournalEntry::ofReference('order', $order->id)->posted()->latest('id')->first();
            if (! $saleEntry) {
                $service->postOrder($order);
            }

            $received = JournalEntry::ofReference('order_payment', $order->id)
                ->posted()
                ->get()
                ->sum(fn ($e) => (float) $e->lines()->sum('debit'));

            $remaining = round((float) $order->total - $received, 2);

            if ((float) $validated['amount'] > $remaining + 0.01) {
                throw new \InvalidArgumentException('অর্ডারের বাকি পরিমাণের বেশি পেমেন্ট নেওয়া যাবে না (বাকি: ৳'.$remaining.')।');
            }

            $service->receiveOrderPayment($order, (float) $validated['amount'], $validated['payment_method'], $validated['reference'] ?? null);

            $order->update(['payment_status' => $remaining - (float) $validated['amount'] < 0.01 ? 'paid' : 'partial']);
        });

        return back()->with('success', 'পেমেন্ট সফলভাবে হিসাব হয়েছে।');
    }

    public function bulkUpdate(Request $request)
    {
        $request->validate([
            'order_ids' => 'required|array',
            'order_ids.*' => 'exists:orders,id',
            'status' => 'required|in:pending,processing,shipped,delivered,cancelled,refunded',
        ]);

        $count = Order::whereIn('id', $request->order_ids)
            ->update(['status' => $request->status]);

        return redirect()->route('orders.index')
            ->with('success', __('orders.bulk_updated', ['count' => $count]));
    }

    public function export(Request $request)
    {
        $query = Order::with(['items', 'customer']);

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        $orders = $query->latest()->get();

        $filename = 'orders-export-'.now()->format('Y-m-d-His').'.csv';
        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"$filename\"",
        ];

        $callback = function () use ($orders) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, [
                __('orders.order_number'), __('orders.customer'), __('orders.phone'),
                __('orders.total'), __('orders.status'), __('orders.payment_status'),
                __('orders.payment_method'), __('orders.items'), __('orders.date'),
            ]);

            foreach ($orders as $order) {
                fputcsv($handle, [
                    $order->order_number,
                    $order->customer_name,
                    $order->customer_phone,
                    number_format($order->total, 2),
                    $order->status,
                    $order->payment_status,
                    $order->payment_method,
                    $order->items->count(),
                    $order->created_at->format('Y-m-d H:i'),
                ]);
            }
            fclose($handle);
        };

        return response()->stream($callback, 200, $headers);
    }

    public function print(Order $order)
    {
        $order->load(['items.product', 'items.variant', 'customer', 'shippingAddress']);
        $storefront = StorefrontSettings::first();

        return view('tenant.orders.print', compact('order', 'storefront'));
    }
}
