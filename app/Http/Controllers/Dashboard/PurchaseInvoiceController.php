<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\BusinessSetting;
use App\Models\JournalEntry;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\PurchaseInvoice;
use App\Models\PurchaseOrder;
use App\Models\PurchaseReceipt;
use App\Models\PurchaseSetting;
use App\Models\Supplier;
use App\Models\SupplierPayment;
use App\Services\AccountingService;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class PurchaseInvoiceController extends Controller
{
    public function index(Request $request)
    {
        $query = PurchaseInvoice::with('supplier');

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
            $query->whereDate('invoice_date', '>=', $request->from);
        }

        if ($request->filled('to')) {
            $query->whereDate('invoice_date', '<=', $request->to);
        }

        $invoices = $query->latest()->paginate(20)->withQueryString();
        $suppliers = Supplier::active()->orderBy('name')->get();

        $stats = [
            'total' => round((float) (clone $query)->where('status', '!=', 'cancelled')->sum('total'), 2),
            'due' => round((float) (clone $query)->where('status', '!=', 'cancelled')->get()->sum(fn ($i) => $i->due()), 2),
            'overdue' => (clone $query)->where('status', '!=', 'cancelled')->get()->filter(fn ($i) => $i->isOverdue())->count(),
        ];

        return view('tenant.purchase.invoices.index', compact('invoices', 'suppliers', 'stats'));
    }

    public function create(Request $request)
    {
        $receipt = null;
        if ($request->filled('receipt_id')) {
            $receipt = PurchaseReceipt::with(['supplier', 'items.product', 'items.variant', 'purchaseOrder'])
                ->findOrFail($request->receipt_id);
        }

        $suppliers = Supplier::active()->orderBy('name')->get();
        $settings = PurchaseSetting::current();

        return view('tenant.purchase.invoices.form', [
            'invoice' => null,
            'receipt' => $receipt,
            'suppliers' => $suppliers,
            'defaultTaxRate' => $settings->default_tax_rate,
            'defaultDueDays' => $settings->payment_term_days,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $this->validateHeader($request, $this->itemsPayload($request));
        $items = $this->validateItems($request);

        if (empty($items) && $validated['purchase_receipt_id']) {
            $receipt = PurchaseReceipt::with('items')->find($validated['purchase_receipt_id']);

            if ($receipt) {
                $items = $receipt->items->map(fn ($item) => [
                    'product_id' => $item->product_id,
                    'variant_id' => $item->variant_id,
                    'name' => $item->name,
                    'sku' => $item->sku,
                    'quantity' => $item->quantity,
                    'unit_cost' => (float) $item->unit_cost,
                    'discount' => 0,
                ])->all();
            }
        }

        if (empty($items)) {
            return back()->with('error', 'কমপক্ষে একটি পণ্য যোগ করুন।')->withInput();
        }

        try {
            $invoice = DB::transaction(function () use ($validated, $items, $request) {
                $invoice = $this->persist($validated, $items, $request);
                $this->postInvoiceIfNeeded($invoice);

                return $invoice;
            });
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage())->withInput();
        }

        return redirect()->route('purchase.invoices.show', $invoice)
            ->with('success', 'বিল (Invoice) তৈরি হয়েছে।');
    }

    public function show(PurchaseInvoice $invoice)
    {
        $invoice->load(['supplier', 'purchaseOrder', 'purchaseReceipt', 'payments', 'creator']);

        $settings = PurchaseSetting::current();
        $paymentAccounts = $settings->paymentAccounts()->filter(fn ($a) => $settings->isEnabled($a->code))->values();

        return view('tenant.purchase.invoices.show', compact('invoice', 'paymentAccounts'));
    }

    public function edit(PurchaseInvoice $invoice)
    {
        if ($invoice->status !== 'draft') {
            return back()->with('error', 'শুধুমাত্র খসড়া বিল সম্পাদনা করা যাবে।');
        }

        $suppliers = Supplier::active()->orderBy('name')->get();

        return view('tenant.purchase.invoices.form', [
            'invoice' => $invoice,
            'receipt' => null,
            'suppliers' => $suppliers,
            'defaultTaxRate' => (float) $invoice->tax_rate,
            'defaultDueDays' => $invoice->due_date?->diffInDays($invoice->invoice_date) ?? 0,
        ]);
    }

    public function update(Request $request, PurchaseInvoice $invoice)
    {
        if ($invoice->status !== 'draft') {
            return back()->with('error', 'শুধুমাত্র খসড়া বিল সম্পাদনা করা যাবে।');
        }

        $validated = $this->validateHeader($request, $this->itemsPayload($request));
        $items = $this->validateItems($request);

        if (empty($items)) {
            return back()->with('error', 'কমপক্ষে একটি পণ্য যোগ করুন।')->withInput();
        }

        try {
            DB::transaction(function () use ($invoice, $validated, $items, $request) {
                $invoice->items()->delete();
                $invoice = $this->persist($validated, $items, $request, $invoice);
                $this->postInvoiceIfNeeded($invoice);
            });
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage())->withInput();
        }

        return redirect()->route('purchase.invoices.show', $invoice)->with('success', 'বিল আপডেট হয়েছে।');
    }

    public function destroy(PurchaseInvoice $invoice)
    {
        if ($invoice->status !== 'draft') {
            return back()->with('error', 'শুধুমাত্র খসড়া বিল ডিলিট করা যাবে।');
        }

        $invoice->delete();

        return redirect()->route('purchase.invoices.index')->with('success', 'বিল ডিলিট হয়েছে।');
    }

    public function cancel(PurchaseInvoice $invoice)
    {
        if ($invoice->payments()->where('status', 'completed')->exists()) {
            return back()->with('error', 'এই বিলের পেমেন্ট আছে, তাই বাতিল করা যাবে না। আগে পেমেন্ট রিভার্স করুন।');
        }

        if ($invoice->status === 'cancelled') {
            return back()->with('error', 'বিল ইতিমধ্যে বাতিল।');
        }

        try {
            DB::transaction(function () use ($invoice) {
                if (PurchaseSetting::current()->auto_post_purchases) {
                    $entry = JournalEntry::ofReference('purchase_invoice', $invoice->id)
                        ->posted()->latest('id')->first();

                    if ($entry) {
                        app(AccountingService::class)->reverse($entry, "Invoice #{$invoice->invoice_number} cancelled");
                    }
                }

                $invoice->update([
                    'status' => 'cancelled',
                    'cancelled_at' => now(),
                ]);
            });
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }

        return back()->with('success', 'বিল বাতিল করা হয়েছে।');
    }

    public function pay(Request $request, PurchaseInvoice $invoice)
    {
        if (in_array($invoice->status, ['paid', 'cancelled'], true)) {
            return back()->with('error', 'এই বিলের জন্য আর পেমেন্ট নেওয়া যাবে না।');
        }

        $validated = $request->validate([
            'amount' => 'required|numeric|min:0.01',
            'method' => 'nullable|string|max:50',
            'reference' => 'nullable|string|max:255',
            'payment_date' => 'required|date',
            'methods_json' => 'nullable|json',
        ]);

        $due = $invoice->due();

        if ($validated['amount'] > $due + 0.01) {
            return back()->with('error', 'পেমেন্টের পরিমাণ বাকি টাকার বেশি হতে পারবে না।');
        }

        $methods = json_decode($request->input('methods_json', '[]'), true) ?: [];

        if (empty($methods) || ! is_array($methods)) {
            $methods = [['method' => $validated['method'], 'amount' => $validated['amount'], 'reference' => $validated['reference']]];
        }

        $methodTotal = round(array_sum(array_column($methods, 'amount')), 2);

        if (abs($methodTotal - (float) $validated['amount']) > 0.01) {
            return back()->with('error', 'পেমেন্ট মাধ্যমগুলোর যোগফল পরিমাণের সমান হতে হবে।');
        }

        foreach ($methods as $m) {
            $validator = Validator::make($m, [
                'method' => 'required|string|max:50',
                'amount' => 'required|numeric|min:0',
                'reference' => 'nullable|string|max:255',
            ]);
            if ($validator->fails()) {
                return back()->with('error', 'অবৈধ পেমেন্ট তথ্য।');
            }
        }

        try {
            DB::transaction(function () use ($invoice, $validated, $methods) {
                $payment = SupplierPayment::create([
                    'supplier_id' => $invoice->supplier_id,
                    'purchase_invoice_id' => $invoice->id,
                    'amount' => $validated['amount'],
                    'method' => $methods[0]['method'] ?? $validated['method'],
                    'reference' => $methods[0]['reference'] ?? $validated['reference'] ?? null,
                    'payment_date' => $validated['payment_date'],
                    'status' => 'completed',
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

                $invoice->update([
                    'paid_amount' => round((float) $invoice->paid_amount + (float) $validated['amount'], 2),
                    'status' => $invoice->isPaid() ? 'paid' : 'partially_paid',
                ]);

                if (PurchaseSetting::current()->auto_post_purchases) {
                    app(AccountingService::class)->postSupplierPayment($payment);
                }
            });
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }

        return back()->with('success', 'সাপ্লায়ার পেমেন্ট গ্রহণ করা হয়েছে।');
    }

    public function print(PurchaseInvoice $invoice)
    {
        $invoice->load(['supplier', 'purchaseOrder', 'purchaseReceipt', 'items.product', 'items.variant', 'creator']);
        $business = BusinessSetting::where('user_id', $invoice->created_by)->first();

        return view('tenant.purchase.invoices.print', compact('invoice', 'business'));
    }

    /**
     * Called by PurchaseReceiptController to auto-create a bill from a GRN.
     */
    public function createFromReceipt(PurchaseReceipt $receipt, ?PurchaseSetting $settings = null): ?PurchaseInvoice
    {
        $settings = $settings ?? PurchaseSetting::current();

        $items = $receipt->items->map(fn ($item) => [
            'product_id' => $item->product_id,
            'variant_id' => $item->variant_id,
            'purchase_order_item_id' => $item->purchase_order_item_id,
            'name' => $item->name,
            'sku' => $item->sku,
            'quantity' => $item->quantity,
            'unit_cost' => (float) $item->unit_cost,
            'discount' => (float) $item->discount,
        ])->all();

        $dueDate = Carbon::parse($receipt->receipt_date)
            ->addDays((int) $settings->payment_term_days);

        return DB::transaction(function () use ($receipt, $items, $dueDate, $settings) {
            $subtotal = round(array_sum(array_map(fn ($i) => $i['quantity'] * ($i['unit_cost'] - $i['discount']), $items)), 2);
            $discountType = $receipt->discount_type;
            $discountValue = (float) $receipt->discount_value;
            $discountAmount = $discountType === 'percent'
                ? round($subtotal * ($discountValue / 100), 2)
                : min($discountValue, $subtotal);
            $taxable = max($subtotal - $discountAmount, 0);
            $taxRate = (float) $receipt->tax_rate;
            $taxAmount = round($taxable * ($taxRate / 100), 2);
            $total = round($taxable + $taxAmount, 2);

            $invoice = PurchaseInvoice::create([
                'purchase_order_id' => $receipt->purchase_order_id,
                'purchase_receipt_id' => $receipt->id,
                'supplier_id' => $receipt->supplier_id,
                'invoice_date' => $receipt->receipt_date,
                'due_date' => $dueDate,
                'status' => 'awaiting_payment',
                'subtotal' => $subtotal,
                'discount_type' => $discountType,
                'discount_value' => $discountValue,
                'discount_amount' => $discountAmount,
                'tax_rate' => $taxRate,
                'tax_amount' => $taxAmount,
                'total' => $total,
                'paid_amount' => 0,
                'notes' => 'Auto-created from GRN '.$receipt->receipt_number,
                'created_by' => auth()->id(),
                'posted_at' => now(),
            ]);

            foreach ($items as $item) {
                $invoice->items()->create($item + ['line_total' => round($item['quantity'] * ($item['unit_cost'] - $item['discount']), 2)]);
            }

            $this->applyOrderAdvance($invoice);

            if ($settings->auto_post_purchases) {
                app(AccountingService::class)->postPurchaseInvoice($invoice);
            }

            return $invoice;
        });
    }

    /**
     * Auto-apply any PO advance payment to the new invoice so the due
     * amount reflects money already given to the supplier.
     */
    private function applyOrderAdvance(PurchaseInvoice $invoice): void
    {
        if (! $invoice->purchase_order_id) {
            return;
        }

        $order = PurchaseOrder::with('advancePayments')->find($invoice->purchase_order_id);

        if (! $order) {
            return;
        }

        $alreadyApplied = (float) $order->invoices->where('id', '!=', $invoice->id)->sum('advance_applied');
        $available = round($order->advanceTotal() - $alreadyApplied, 2);

        if ($available <= 0) {
            return;
        }

        $apply = min($available, $invoice->due());

        if ($apply <= 0) {
            return;
        }

        $invoice->updateQuietly([
            'advance_applied' => round((float) $invoice->advance_applied + $apply, 2),
            'status' => $invoice->due() < 0.01 ? 'paid' : 'awaiting_payment',
        ]);
    }

    /**
     * Shared persistence used by store/update.
     */
    private function persist(array $validated, array $items, Request $request, ?PurchaseInvoice $invoice = null): PurchaseInvoice
    {
        $subtotal = round(array_sum(array_map(fn ($i) => $i['quantity'] * ($i['unit_cost'] - $i['discount']), $items)), 2);
        $discountAmount = min((float) ($validated['discount_amount'] ?? 0), $subtotal);
        $taxable = max($subtotal - $discountAmount, 0);
        $taxRate = (float) ($validated['tax_rate'] ?? 0);
        $taxAmount = round($taxable * ($taxRate / 100), 2);
        $total = round($taxable + $taxAmount, 2);

        $status = $validated['status'] ?? 'awaiting_payment';

        $data = [
            'purchase_order_id' => $validated['purchase_order_id'] ?? null,
            'purchase_receipt_id' => $validated['purchase_receipt_id'] ?? null,
            'supplier_id' => $validated['supplier_id'],
            'invoice_date' => $validated['invoice_date'],
            'due_date' => $validated['due_date'] ?? null,
            'status' => $status,
            'subtotal' => $subtotal,
            'discount_amount' => $discountAmount,
            'tax_rate' => $taxRate,
            'tax_amount' => $taxAmount,
            'total' => $total,
            'notes' => $validated['notes'] ?? null,
        ];

        if ($status !== 'draft') {
            $data['posted_at'] = $invoice?->posted_at ?? now();
        }

        if ($invoice) {
            $data['paid_amount'] = $invoice->paid_amount;
            $invoice->update($data);
        } else {
            $invoice = PurchaseInvoice::create([...$data, 'created_by' => auth()->id()]);
        }

        foreach ($items as $item) {
            $invoice->items()->create($item + ['line_total' => round($item['quantity'] * ($item['unit_cost'] - $item['discount']), 2)]);
        }

        // Record an immediate payment when the bill was settled at creation time.
        if ($status === 'paid' && (float) $invoice->paid_amount < 0.01) {
            $payment = SupplierPayment::create([
                'supplier_id' => $invoice->supplier_id,
                'purchase_invoice_id' => $invoice->id,
                'amount' => $total,
                'method' => $validated['payment_method'] ?? 'cash',
                'reference' => $validated['payment_reference'] ?? null,
                'payment_date' => $invoice->invoice_date,
                'status' => 'completed',
                'created_by' => auth()->id(),
                'paid_at' => now(),
            ]);

            $invoice->updateQuietly(['paid_amount' => $total]);

            if (PurchaseSetting::current()->auto_post_purchases) {
                app(AccountingService::class)->postSupplierPayment($payment);
            }
        }

        return $invoice;
    }

    private function postInvoiceIfNeeded(PurchaseInvoice $invoice): void
    {
        if ($invoice->status !== 'draft' && PurchaseSetting::current()->auto_post_purchases) {
            app(AccountingService::class)->postPurchaseInvoice($invoice);
        }
    }

    private function itemsPayload(Request $request): array
    {
        return json_decode($request->input('items_json', '[]'), true) ?: [];
    }

    private function validateHeader(Request $request, array $items): array
    {
        return $request->validate([
            'supplier_id' => 'required|exists:suppliers,id',
            'invoice_date' => 'required|date',
            'due_date' => 'nullable|date|after_or_equal:invoice_date',
            'purchase_order_id' => 'nullable|exists:purchase_orders,id',
            'purchase_receipt_id' => 'nullable|exists:purchase_receipts,id',
            'status' => 'nullable|in:draft,awaiting_payment,paid',
            'discount_amount' => 'nullable|numeric|min:0',
            'tax_rate' => 'nullable|numeric|min:0|max:100',
            'notes' => 'nullable|string',
            'payment_method' => 'nullable|string|max:50',
            'payment_reference' => 'nullable|string|max:255',
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
}
