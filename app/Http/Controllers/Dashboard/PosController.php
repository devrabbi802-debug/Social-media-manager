<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\AccountingSetting;
use App\Models\Category;
use App\Models\Customer;
use App\Models\PosOrder;
use App\Models\PosPayment;
use App\Models\PosSession;
use App\Models\PosSetting;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\StockMovement;
use App\Models\Warehouse;
use App\Services\AccountingService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class PosController extends Controller
{
    public function index()
    {
        $settings = PosSetting::current();
        $openSession = PosSession::open()->where('user_id', auth()->id())->first();
        $categories = Category::orderBy('name')->get();
        $customers = Customer::orderByDesc('id')->limit(20)->get();
        $holds = PosOrder::where('status', 'hold')->with('items')->latest()->limit(20)->get();
        $warehouses = Warehouse::where('is_active', true)->orderBy('name')->get();
        $defaultWarehouse = Warehouse::find($settings->default_warehouse_id) ?? $warehouses->first();
        $paymentAccounts = $settings->paymentAccounts()->filter(fn ($a) => $settings->isEnabled($a->code))->values();

        return view('tenant.pos.index', compact(
            'settings',
            'openSession',
            'categories',
            'customers',
            'holds',
            'warehouses',
            'defaultWarehouse',
            'paymentAccounts'
        ));
    }

    public function products(Request $request)
    {
        $search = $request->query('search');
        $categoryId = $request->query('category_id');

        $query = Product::with(['category', 'variants' => function ($q) {
            $q->active();
        }])->active();

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

        $data = $products->map(function (Product $product) use ($baseUrl) {
            $image = $product->primaryImage;

            return [
                'id' => $product->id,
                'name' => $product->name,
                'sku' => $product->sku,
                'barcode' => $product->barcode,
                'price' => (float) $product->price,
                'cost' => (float) ($product->cost_price ?? 0),
                'stock' => $product->total_stock,
                'image' => $image ? $baseUrl.'/storage/'.$image->image_path : null,
                'has_variants' => $product->has_variants,
                'variants' => $product->variants->map(function (ProductVariant $v) {
                    return [
                        'id' => $v->id,
                        'name' => $v->display,
                        'sku' => $v->sku,
                        'barcode' => $v->barcode,
                        'price' => (float) ($v->price ?? $v->product->price),
                        'cost' => (float) ($v->cost_price ?? 0),
                        'stock' => (int) $v->stock_quantity,
                    ];
                })->values(),
            ];
        });

        return response()->json([
            'data' => $data,
            'next_page' => $products->currentPage() < $products->lastPage() ? $products->currentPage() + 1 : null,
        ]);
    }

    public function checkout(Request $request)
    {
        $validated = $request->validate([
            'items_json' => 'required|json',
            'payments_json' => 'required|json',
            'discount_type' => 'nullable|in:fixed,percent',
            'discount_value' => 'nullable|numeric|min:0',
            'customer_id' => 'nullable|exists:customers,id',
            'customer_name' => 'nullable|string|max:255',
            'customer_phone' => 'nullable|string|max:50',
            'tendered_amount' => 'nullable|numeric|min:0',
            'change_due' => 'nullable|numeric|min:0',
            'warehouse_id' => 'nullable|exists:warehouses,id',
            'notes' => 'nullable|string',
            'resume_order_id' => 'nullable|exists:pos_orders,id',
        ]);

        $items = json_decode($request->input('items_json'), true);
        $payments = json_decode($request->input('payments_json'), true);

        if (empty($items) || ! is_array($items)) {
            return back()->with('error', 'কার্টে কোনো পণ্য নেই।');
        }

        if (empty($payments) || ! is_array($payments)) {
            return back()->with('error', 'কোনো পেমেন্ট দেওয়া হয়নি।');
        }

        foreach ($items as $item) {
            $validator = Validator::make($item, [
                'product_id' => 'required|exists:products,id',
                'variant_id' => 'nullable|exists:product_variants,id',
                'quantity' => 'required|integer|min:1',
                'unit_price' => 'required|numeric|min:0',
            ]);
            if ($validator->fails()) {
                return back()->with('error', 'অবৈধ কার্ট আইটেম।');
            }
        }

        foreach ($payments as $payment) {
            $validator = Validator::make($payment, [
                'method' => 'required|string|max:50',
                'amount' => 'required|numeric|min:0',
                'reference' => 'nullable|string|max:255',
            ]);
            if ($validator->fails()) {
                return back()->with('error', 'অবৈধ পেমেন্ট তথ্য।');
            }
        }

        $settings = PosSetting::current();
        $warehouse = Warehouse::find($validated['warehouse_id'] ?? $settings->default_warehouse_id)
            ?? Warehouse::where('is_active', true)->first();

        if (! $warehouse) {
            return back()->with('error', 'কোনো ওয়ারহাউস পাওয়া যায়নি। আগে একটি ওয়ারহাউস তৈরি করুন।');
        }

        $session = PosSession::open()->where('user_id', auth()->id())->first();

        try {
            $order = DB::transaction(function () use ($items, $payments, $validated, $settings, $warehouse, $session) {
                $this->validateStock($items);

                $subtotal = 0;
                foreach ($items as $item) {
                    $subtotal += $item['unit_price'] * $item['quantity'];
                }

                $discountAmount = 0;
                $discountType = $validated['discount_type'] ?? null;
                $discountValue = (float) ($validated['discount_value'] ?? 0);
                if ($discountType === 'percent') {
                    $discountAmount = round($subtotal * ($discountValue / 100), 2);
                } elseif ($discountType === 'fixed') {
                    $discountAmount = min($discountValue, $subtotal);
                }

                $taxable = max($subtotal - $discountAmount, 0);
                $taxAmount = 0;
                if ($settings->tax_rate > 0) {
                    if ($settings->tax_type === 'inclusive') {
                        $taxAmount = round($taxable - ($taxable / (1 + $settings->tax_rate / 100)), 2);
                        $total = round($taxable, 2);
                    } else {
                        $taxAmount = round($taxable * ($settings->tax_rate / 100), 2);
                        $total = round($taxable + $taxAmount, 2);
                    }
                } else {
                    $total = round($taxable, 2);
                }

                $paid = array_sum(array_column($payments, 'amount'));
                $paymentStatus = 'unpaid';
                if ($paid > 0 && $paid >= $total - 0.01) {
                    $paymentStatus = 'paid';
                } elseif ($paid > 0) {
                    $paymentStatus = 'partial';
                }

                $primaryMethod = $payments[0]['method'] ?? $settings->default_payment_method;

                $order = PosOrder::create([
                    'pos_session_id' => $session?->id,
                    'user_id' => auth()->id(),
                    'customer_id' => $validated['customer_id'] ?? null,
                    'customer_name' => $validated['customer_name'] ?? null,
                    'customer_phone' => $validated['customer_phone'] ?? null,
                    'subtotal' => $subtotal,
                    'discount_type' => $discountType,
                    'discount_value' => $discountValue,
                    'discount_amount' => $discountAmount,
                    'tax_type' => $settings->tax_type,
                    'tax_rate' => $settings->tax_rate,
                    'tax_amount' => $taxAmount,
                    'total' => $total,
                    'tendered_amount' => $validated['tendered_amount'] ?? $paid,
                    'change_due' => $validated['change_due'] ?? 0,
                    'payment_method' => $primaryMethod,
                    'payment_status' => $paymentStatus,
                    'status' => 'completed',
                    'notes' => $validated['notes'] ?? null,
                ]);

                $costOfGoods = 0;

                foreach ($items as $item) {
                    $product = Product::find($item['product_id']);
                    $variant = $item['variant_id'] ? ProductVariant::find($item['variant_id']) : null;

                    $cost = $variant ? (float) ($variant->cost_price ?? $product->cost_price ?? 0)
                        : (float) ($product->cost_price ?? 0);

                    $costOfGoods += $cost * (int) $item['quantity'];

                    PosOrder::createItem($order, $product, $variant, $item['unit_price'], $cost, $item['quantity']);

                    $this->deductStock($product, $variant, $item['quantity'], $warehouse, 'POS-'.$order->order_number);
                }

                foreach ($payments as $payment) {
                    PosPayment::create([
                        'pos_order_id' => $order->id,
                        'method' => $payment['method'],
                        'amount' => $payment['amount'],
                        'reference' => $payment['reference'] ?? null,
                    ]);
                }

                $this->updateSession($session, $order, $paid);

                if (AccountingSetting::current()->post_pos_sales) {
                    app(AccountingService::class)->postPosSale($order, $payments, $costOfGoods);
                }

                if (! empty($validated['resume_order_id'])) {
                    PosOrder::where('id', $validated['resume_order_id'])
                        ->where('status', 'hold')
                        ->delete();
                }

                return $order;
            });

            if ($settings->auto_print_receipt) {
                return redirect()->route('pos.sales.receipt', $order->id);
            }

            return redirect()->route('pos.sales.receipt', $order->id)
                ->with('success', 'অর্ডার সফলভাবে সম্পন্ন হয়েছে!');

        } catch (ValidationException $e) {
            throw $e;
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    public function hold(Request $request)
    {
        $validated = $request->validate([
            'items_json' => 'required|json',
            'discount_type' => 'nullable|in:fixed,percent',
            'discount_value' => 'nullable|numeric|min:0',
            'customer_name' => 'nullable|string|max:255',
            'customer_phone' => 'nullable|string|max:50',
            'notes' => 'nullable|string',
        ]);

        $items = json_decode($request->input('items_json'), true);
        if (empty($items) || ! is_array($items)) {
            return back()->with('error', 'কার্টে কোনো পণ্য নেই।');
        }

        foreach ($items as $item) {
            $validator = Validator::make($item, [
                'product_id' => 'required|exists:products,id',
                'variant_id' => 'nullable|exists:product_variants,id',
                'quantity' => 'required|integer|min:1',
                'unit_price' => 'required|numeric|min:0',
            ]);
            if ($validator->fails()) {
                return back()->with('error', 'অবৈধ কার্ট আইটেম।');
            }
        }

        DB::transaction(function () use ($items, $validated) {
            $subtotal = 0;
            foreach ($items as $item) {
                $subtotal += $item['unit_price'] * $item['quantity'];
            }

            $order = PosOrder::create([
                'pos_session_id' => null,
                'user_id' => auth()->id(),
                'customer_name' => $validated['customer_name'] ?? null,
                'customer_phone' => $validated['customer_phone'] ?? null,
                'subtotal' => $subtotal,
                'discount_type' => $validated['discount_type'] ?? null,
                'discount_value' => $validated['discount_value'] ?? 0,
                'discount_amount' => 0,
                'tax_type' => PosSetting::current()->tax_type,
                'tax_rate' => PosSetting::current()->tax_rate,
                'tax_amount' => 0,
                'total' => $subtotal,
                'payment_method' => 'cash',
                'payment_status' => 'unpaid',
                'status' => 'hold',
                'notes' => $validated['notes'] ?? null,
            ]);

            foreach ($items as $item) {
                $product = Product::find($item['product_id']);
                $variant = $item['variant_id'] ? ProductVariant::find($item['variant_id']) : null;
                $cost = $variant ? (float) ($variant->cost_price ?? $product->cost_price ?? 0)
                    : (float) ($product->cost_price ?? 0);

                PosOrder::createItem($order, $product, $variant, $item['unit_price'], $cost, $item['quantity']);
            }
        });

        return back()->with('success', 'অর্ডার হোল্ড করা হয়েছে।');
    }

    public function resume(PosOrder $order)
    {
        if ($order->status !== 'hold') {
            return back()->with('error', 'এই অর্ডার হোল্ড নেই।');
        }

        $order->load('items');

        $items = $order->items->map(fn ($item) => [
            'product_id' => $item->product_id,
            'variant_id' => $item->variant_id,
            'quantity' => $item->quantity,
            'unit_price' => (float) $item->unit_price,
            'name' => $item->name,
            'sku' => $item->sku,
        ])->values();

        return redirect()->route('pos.index')->with([
            'resume_items' => $items->toJson(),
            'resume_customer_name' => $order->customer_name,
            'resume_customer_phone' => $order->customer_phone,
            'resume_order_id' => $order->id,
        ]);
    }

    public function cancelHold(PosOrder $order)
    {
        if ($order->status !== 'hold') {
            return back()->with('error', 'এই অর্ডার হোল্ড নেই।');
        }

        $order->delete();

        return back()->with('success', 'হোল্ড অর্ডার বাতিল করা হয়েছে।');
    }

    protected function validateStock(array $items): void
    {
        foreach ($items as $item) {
            if ($item['variant_id']) {
                $variant = ProductVariant::find($item['variant_id']);
                $label = $variant?->display ?? 'ভেরিয়েন্ট';
                if (! $variant || $variant->stock_quantity < $item['quantity']) {
                    throw new \Exception("পর্যাপ্ত স্টক নেই: {$label}");
                }
            } else {
                $product = Product::find($item['product_id']);
                $label = $product?->name ?? 'পণ্য';
                if (! $product || $product->stock_quantity < $item['quantity']) {
                    throw new \Exception("পর্যাপ্ত স্টক নেই: {$label}");
                }
            }
        }
    }

    protected function deductStock(Product $product, ?ProductVariant $variant, int $quantity, Warehouse $warehouse, string $reference): void
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
            'notes' => 'POS Sale',
            'created_by' => auth()->id(),
        ]);
    }

    protected function updateSession(?PosSession $session, PosOrder $order, float $paid): void
    {
        if (! $session) {
            return;
        }

        $session->total_sales = round((float) $session->total_sales + (float) $order->total, 2);
        $session->total_tax = round((float) $session->total_tax + (float) $order->tax_amount, 2);
        $session->total_discount = round((float) $session->total_discount + (float) $order->discount_amount, 2);
        $session->sales_count += 1;

        foreach ($order->payments as $payment) {
            $method = strtolower($payment->method);
            if (in_array($method, ['cash'])) {
                $session->cash_sales = round((float) $session->cash_sales + (float) $payment->amount, 2);
            } elseif (in_array($method, ['card', 'credit_card', 'debit_card', 'visa', 'mastercard'])) {
                $session->card_sales = round((float) $session->card_sales + (float) $payment->amount, 2);
            } elseif (in_array($method, ['mobile', 'bkash', 'nagad', 'rocket', 'upay'])) {
                $session->mobile_sales = round((float) $session->mobile_sales + (float) $payment->amount, 2);
            } else {
                $session->other_sales = round((float) $session->other_sales + (float) $payment->amount, 2);
            }
        }

        $session->save();
    }
}
