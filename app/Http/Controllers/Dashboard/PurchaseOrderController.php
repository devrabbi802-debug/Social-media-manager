<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\BusinessSetting;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\PurchaseOrder;
use App\Models\PurchaseSetting;
use App\Models\Supplier;
use App\Models\Warehouse;
use App\Services\AccountingService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class PurchaseOrderController extends Controller
{
    public function index(Request $request)
    {
        $query = PurchaseOrder::with('supplier');

        if ($request->filled('search')) {
            $query->search($request->search);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('supplier_id')) {
            $query->where('supplier_id', $request->supplier_id);
        }

        if ($request->filled('from')) {
            $query->whereDate('order_date', '>=', $request->from);
        }

        if ($request->filled('to')) {
            $query->whereDate('order_date', '<=', $request->to);
        }

        $orders = $query->latest()->paginate(20)->withQueryString();
        $suppliers = Supplier::active()->orderBy('name')->get();

        $stats = [
            'total_value' => round((float) (clone $query)->where('status', '!=', 'cancelled')->sum('total'), 2),
            'open' => (clone $query)->whereIn('status', ['ordered', 'partially_received'])->count(),
            'received' => (clone $query)->where('status', 'received')->count(),
        ];

        return view('tenant.purchase.orders.index', compact('orders', 'suppliers', 'stats'));
    }

    public function create()
    {
        $settings = PurchaseSetting::current();
        $suppliers = Supplier::active()->orderBy('name')->get();
        $warehouses = Warehouse::active()->orderBy('name')->get();

        return view('tenant.purchase.orders.form', [
            'order' => new PurchaseOrder,
            'suppliers' => $suppliers,
            'warehouses' => $warehouses,
            'defaultTaxRate' => $settings->default_tax_rate,
            'defaultWarehouseId' => $settings->default_warehouse_id,
            'paymentAccounts' => $settings->paymentAccounts()->filter(fn ($a) => $settings->isEnabled($a->code))->values(),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $this->validateHeader($request);
        $items = $this->validateItems($request);

        if (empty($items)) {
            return back()->with('error', 'কমপক্ষে একটি পণ্য যোগ করুন।')->withInput();
        }

        $totals = $this->totals($items, $validated);

        try {
            $order = DB::transaction(function () use ($validated, $items, $totals) {
                $order = PurchaseOrder::create([
                    ...$validated,
                    ...$totals,
                    'status' => $validated['status'] ?? 'draft',
                    'created_by' => auth()->id(),
                ]);

                if ($order->status === 'ordered') {
                    $order->updateQuietly(['ordered_at' => now()]);
                }

                foreach ($items as $item) {
                    $order->items()->create($item + ['line_total' => round($item['quantity'] * ($item['unit_cost'] - $item['discount']), 2)]);
                }

                return $order;
            });
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage())->withInput();
        }

        try {
            $this->recordAdvanceFromForm($order, $request);
        } catch (\Exception $e) {
            return redirect()->route('purchase.orders.show', $order)
                ->with('error', 'পারচেজ অর্ডার তৈরি হয়েছে, তবে অগ্রিম পেমেন্ট সংরক্ষণ হয়নি: '.$e->getMessage());
        }

        return redirect()->route('purchase.orders.show', $order)
            ->with('success', 'পারচেজ অর্ডার তৈরি হয়েছে।');
    }

    public function show(PurchaseOrder $order)
    {
        $order->load(['supplier', 'items.product', 'items.variant', 'receipts', 'creator', 'invoices', 'advancePayments.methods']);
        $settings = PurchaseSetting::current();
        $warehouses = Warehouse::active()->orderBy('name')->get();
        $paymentAccounts = $settings->paymentAccounts()->filter(fn ($a) => $settings->isEnabled($a->code))->values();

        return view('tenant.purchase.orders.show', compact('order', 'settings', 'warehouses', 'paymentAccounts'));
    }

    /**
     * Record an advance payment made to a supplier against a purchase order.
     */
    public function payAdvance(Request $request, PurchaseOrder $order)
    {
        if (in_array($order->status, ['cancelled', 'received'], true)) {
            return back()->with('error', 'এই অর্ডারের জন্য আর অগ্রিম পেমেন্ট নেওয়া যাবে না।');
        }

        $validated = $request->validate([
            'amount' => 'required|numeric|min:0.01',
            'payment_date' => 'required|date',
            'notes' => 'nullable|string',
            'methods_json' => 'nullable|json',
        ]);

        $amount = (float) $validated['amount'];
        $methods = json_decode($request->input('methods_json', '[]'), true) ?: [];

        if ($amount > $order->maxAdvanceable()) {
            return back()->with('error', 'অগ্রিম পেমেন্ট অর্ডারের অগ্রিমযোগ্য পরিমাণের বেশি হতে পারবে না।')->withInput();
        }

        if (empty($methods) || ! is_array($methods)) {
            $methods = [['method' => $request->input('method'), 'amount' => $amount, 'reference' => null]];
        }

        $methodTotal = round(array_sum(array_column($methods, 'amount')), 2);

        if (abs($methodTotal - $amount) > 0.01) {
            return back()->with('error', 'পেমেন্ট মাধ্যমগুলোর যোগফল পরিমাণের সমান হতে হবে।')->withInput();
        }

        foreach ($methods as $m) {
            $validator = Validator::make($m, [
                'method' => 'required|string|max:50',
                'amount' => 'required|numeric|min:0',
                'reference' => 'nullable|string|max:255',
            ]);
            if ($validator->fails()) {
                return back()->with('error', 'অবৈধ পেমেন্ট তথ্য।')->withInput();
            }
        }

        try {
            DB::transaction(function () use ($order, $validated, $methods, $amount) {
                $payment = $order->advancePayments()->create([
                    'supplier_id' => $order->supplier_id,
                    'purchase_order_id' => $order->id,
                    'amount' => $amount,
                    'method' => $methods[0]['method'] ?? 'cash',
                    'reference' => $methods[0]['reference'] ?? null,
                    'payment_date' => $validated['payment_date'],
                    'notes' => $validated['notes'] ?? null,
                    'status' => 'completed',
                    'type' => 'advance',
                    'created_by' => auth()->id(),
                    'paid_at' => now(),
                ]);

                foreach ($methods as $m) {
                    $payment->methods()->create([
                        'method' => $m['method'],
                        'amount' => $m['amount'],
                        'reference' => $m['reference'] ?? null,
                    ]);
                }

                if (PurchaseSetting::current()->auto_post_purchases) {
                    app(AccountingService::class)->postSupplierAdvance($payment);
                }
            });
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage())->withInput();
        }

        return back()->with('success', 'সাপ্লায়ার অগ্রিম পেমেন্ট নেওয়া হয়েছে।');
    }

    /**
     * Record an advance payment supplied from the PO create/edit form.
     */
    private function recordAdvanceFromForm(PurchaseOrder $order, Request $request): void
    {
        $methods = json_decode($request->input('advance_methods_json', '[]'), true) ?: [];
        $paymentDate = $request->input('advance_date') ?: now()->toDateString();

        if (empty($methods) || ! is_array($methods)) {
            return;
        }

        foreach ($methods as $m) {
            if (empty($m['method']) || (float) ($m['amount'] ?? 0) <= 0) {
                throw new \InvalidArgumentException('অবৈধ পেমেন্ট তথ্য।');
            }
        }

        $amount = round(array_sum(array_column($methods, 'amount')), 2);

        if ($amount > $order->maxAdvanceable()) {
            throw new \InvalidArgumentException('অগ্রিম পেমেন্ট অর্ডারের অগ্রিমযোগ্য পরিমাণের বেশি হতে পারবে না।');
        }

        $payment = $order->advancePayments()->create([
            'supplier_id' => $order->supplier_id,
            'purchase_order_id' => $order->id,
            'amount' => $amount,
            'method' => $methods[0]['method'] ?? 'cash',
            'reference' => $methods[0]['reference'] ?? null,
            'payment_date' => $paymentDate,
            'notes' => $request->input('advance_notes'),
            'status' => 'completed',
            'type' => 'advance',
            'created_by' => auth()->id(),
            'paid_at' => now(),
        ]);

        foreach ($methods as $m) {
            $payment->methods()->create([
                'method' => $m['method'],
                'amount' => $m['amount'],
                'reference' => $m['reference'] ?? null,
            ]);
        }

        if (PurchaseSetting::current()->auto_post_purchases) {
            app(AccountingService::class)->postSupplierAdvance($payment);
        }
    }

    public function edit(PurchaseOrder $order)
    {
        if (! in_array($order->status, ['draft', 'ordered', 'partially_received'], true)) {
            return back()->with('error', 'শুধুমাত্র খসড়া/অর্ডারকৃত পারচেজ অর্ডার সম্পাদনা করা যাবে।');
        }

        $order->load('items');
        $suppliers = Supplier::active()->orderBy('name')->get();
        $warehouses = Warehouse::active()->orderBy('name')->get();
        $settings = PurchaseSetting::current();

        return view('tenant.purchase.orders.form', [
            'order' => $order,
            'suppliers' => $suppliers,
            'warehouses' => $warehouses,
            'defaultTaxRate' => (float) $order->tax_rate,
            'defaultWarehouseId' => $settings->default_warehouse_id,
            'paymentAccounts' => $settings->paymentAccounts()->filter(fn ($a) => $settings->isEnabled($a->code))->values(),
        ]);
    }

    public function update(Request $request, PurchaseOrder $order)
    {
        if (! in_array($order->status, ['draft', 'ordered', 'partially_received'], true)) {
            return back()->with('error', 'সম্পাদনা অনুমোদিত নয়।');
        }

        $validated = $this->validateHeader($request);
        $items = $this->validateItems($request);

        if (empty($items)) {
            return back()->with('error', 'কমপক্ষে একটি পণ্য যোগ করুন।')->withInput();
        }

        $totals = $this->totals($items, $validated);

        try {
            DB::transaction(function () use ($order, $validated, $items, $totals) {
                $receivedByProduct = $order->items()
                    ->get()->mapWithKeys(fn ($item) => [
                        $item->product_id.'-'.($item->variant_id ?? '') => $item->received_quantity,
                    ]);

                $order->update([...$validated, ...$totals]);

                $order->items()->delete();

                foreach ($items as $item) {
                    $key = $item['product_id'].'-'.($item['variant_id'] ?? '');
                    $received = $receivedByProduct[$key] ?? 0;

                    $order->items()->create($item + [
                        'received_quantity' => min($received, $item['quantity']),
                        'line_total' => round($item['quantity'] * ($item['unit_cost'] - $item['discount']), 2),
                    ]);
                }

                $order->refresh();
                $order->updateQuietly(['status' => $order->fullyReceived() ? 'received' : $order->status]);
            });
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage())->withInput();
        }

        try {
            $this->recordAdvanceFromForm($order, $request);
        } catch (\Exception $e) {
            return redirect()->route('purchase.orders.show', $order)
                ->with('error', 'পারচেজ অর্ডার আপডেট হয়েছে, তবে অগ্রিম পেমেন্ট সংরক্ষণ হয়নি: '.$e->getMessage());
        }

        return redirect()->route('purchase.orders.show', $order)->with('success', 'পারচেজ অর্ডার আপডেট হয়েছে।');
    }

    public function destroy(PurchaseOrder $order)
    {
        if ($order->status === 'received' || $order->receipts()->exists()) {
            return back()->with('error', 'অর্ডার প্রাপ্ত হওয়ার পরে ডিলিট করা যাবে না।');
        }

        $order->delete();

        return redirect()->route('purchase.orders.index')->with('success', 'পারচেজ অর্ডার ডিলিট হয়েছে।');
    }

    public function markOrdered(PurchaseOrder $order)
    {
        if (! in_array($order->status, ['draft'], true)) {
            return back()->with('error', 'এই অর্ডার ইতিমধ্যে অর্ডার করা হয়েছে।');
        }

        $order->update([
            'status' => 'ordered',
            'ordered_at' => now(),
        ]);

        return back()->with('success', 'অর্ডার কনফার্ম করা হয়েছে।');
    }

    public function cancel(PurchaseOrder $order)
    {
        if (in_array($order->status, ['received', 'cancelled'], true)) {
            return back()->with('error', 'প্রাপ্ত/বাতিল অর্ডার বাতিল করা যাবে না।');
        }

        $order->update([
            'status' => 'cancelled',
            'cancelled_at' => now(),
        ]);

        return back()->with('success', 'পারচেজ অর্ডার বাতিল করা হয়েছে।');
    }

    public function print(PurchaseOrder $order)
    {
        $order->load(['supplier', 'items.product', 'items.variant', 'creator']);

        $business = BusinessSetting::where('user_id', $order->created_by)->first();

        return view('tenant.purchase.orders.print', compact('order', 'business'));
    }

    private function validateHeader(Request $request): array
    {
        return $request->validate([
            'supplier_id' => 'required|exists:suppliers,id',
            'order_date' => 'required|date',
            'expected_date' => 'nullable|date',
            'status' => 'nullable|in:draft,ordered',
            'discount_type' => 'nullable|in:fixed,percent',
            'discount_value' => 'nullable|numeric|min:0',
            'tax_rate' => 'nullable|numeric|min:0|max:100',
            'notes' => 'nullable|string',
            'terms' => 'nullable|string',
        ]);
    }

    private function validateItems(Request $request): array
    {
        $items = json_decode($request->input('items_json', '[]'), true) ?: [];

        if (empty($items) || ! is_array($items)) {
            return [];
        }

        $clean = [];

        foreach ($items as $item) {
            $validator = Validator::make($item, [
                'product_id' => 'required|exists:products,id',
                'variant_id' => 'nullable|exists:product_variants,id',
                'name' => 'required|string|max:255',
                'quantity' => 'required|integer|min:1',
                'unit_cost' => 'required|numeric|min:0',
                'discount' => 'nullable|numeric|min:0',
            ]);

            if ($validator->fails()) {
                continue;
            }

            $product = Product::find($item['product_id']);
            $variant = ! empty($item['variant_id']) ? ProductVariant::find($item['variant_id']) : null;

            $clean[] = [
                'product_id' => (int) $item['product_id'],
                'variant_id' => $variant?->id,
                'name' => $variant ? ($product?->name ?? '').' - '.$variant->display : ($product?->name ?? $item['name']),
                'sku' => $variant?->sku ?? $product?->sku,
                'quantity' => (int) $item['quantity'],
                'unit_cost' => (float) $item['unit_cost'],
                'discount' => (float) ($item['discount'] ?? 0),
            ];
        }

        return $clean;
    }

    private function totals(array $items, array $validated): array
    {
        $subtotal = round(array_sum(array_map(
            fn ($i) => $i['quantity'] * ($i['unit_cost'] - $i['discount']),
            $items
        )), 2);

        $discountType = $validated['discount_type'] ?? null;
        $discountValue = (float) ($validated['discount_value'] ?? 0);
        $discountAmount = 0;

        if ($discountType === 'percent') {
            $discountAmount = round($subtotal * ($discountValue / 100), 2);
        } elseif ($discountType === 'fixed') {
            $discountAmount = min($discountValue, $subtotal);
        }

        $taxable = max($subtotal - $discountAmount, 0);
        $taxRate = (float) ($validated['tax_rate'] ?? 0);
        $taxAmount = round($taxable * ($taxRate / 100), 2);
        $total = round($taxable + $taxAmount, 2);

        return [
            'subtotal' => $subtotal,
            'discount_type' => $discountType,
            'discount_value' => $discountValue,
            'discount_amount' => $discountAmount,
            'tax_rate' => $taxRate,
            'tax_amount' => $taxAmount,
            'total' => $total,
        ];
    }
}
