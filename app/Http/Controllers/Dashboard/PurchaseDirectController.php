<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\PurchaseOrder;
use App\Models\PurchaseReceipt;
use App\Models\PurchaseSetting;
use App\Models\StockMovement;
use App\Models\Supplier;
use App\Models\Warehouse;
use App\Services\AccountingService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class PurchaseDirectController extends Controller
{
    public function create()
    {
        $settings = PurchaseSetting::current();
        $suppliers = Supplier::active()->orderBy('name')->get();
        $warehouses = Warehouse::active()->orderBy('name')->get();

        return view('tenant.purchase.direct.form', [
            'suppliers' => $suppliers,
            'warehouses' => $warehouses,
            'defaultWarehouseId' => $settings->default_warehouse_id,
            'defaultTaxRate' => $settings->default_tax_rate,
            'autoInvoice' => $settings->auto_create_invoice_on_receipt,
            'paymentAccounts' => $settings->paymentAccounts()->filter(fn ($a) => $settings->isEnabled($a->code))->values(),
        ]);
    }

    /**
     * Single-transaction direct purchase: PO (received) + GRN + (optional invoice) + stock + accounting.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'supplier_id' => 'required|exists:suppliers,id',
            'warehouse_id' => 'required|exists:warehouses,id',
            'purchase_date' => 'required|date',
            'discount_type' => 'nullable|in:fixed,percent',
            'discount_value' => 'nullable|numeric|min:0',
            'tax_rate' => 'nullable|numeric|min:0|max:100',
            'notes' => 'nullable|string',
            'create_invoice' => 'nullable|in:1,0',
            'advance_date' => 'nullable|date',
            'advance_methods_json' => 'nullable|json',
        ]);

        $items = $this->validateItems($request);

        if (empty($items)) {
            return back()->with('error', 'কমপক্ষে একটি পণ্য যোগ করুন।')->withInput();
        }

        $settings = PurchaseSetting::current();
        $totals = $this->totals($items, $validated);
        $advanceMethods = $this->parseAdvance($request);

        try {
            [$order, $receipt, $invoice] = DB::transaction(function () use ($validated, $items, $totals, $settings, $advanceMethods) {
                // 1) Purchase Order — fully received immediately
                $order = PurchaseOrder::create([
                    'supplier_id' => $validated['supplier_id'],
                    'order_date' => $validated['purchase_date'],
                    'expected_date' => $validated['purchase_date'],
                    'status' => 'received',
                    ...$totals,
                    'notes' => ($validated['notes'] ?? null).' [Direct Purchase]',
                    'created_by' => auth()->id(),
                    'ordered_at' => now(),
                    'received_at' => now(),
                ]);

                foreach ($items as $item) {
                    $order->items()->create($item + [
                        'received_quantity' => $item['quantity'],
                        'line_total' => round($item['quantity'] * ($item['unit_cost'] - $item['discount']), 2),
                    ]);
                }

                // 2) — GRN (receipt) linked to the PO
                $receipt = PurchaseReceipt::create([
                    'purchase_order_id' => $order->id,
                    'supplier_id' => $validated['supplier_id'],
                    'warehouse_id' => $validated['warehouse_id'],
                    'receipt_date' => $validated['purchase_date'],
                    'status' => 'received',
                    ...$totals,
                    'notes' => $validated['notes'] ?? null,
                    'created_by' => auth()->id(),
                    'received_at' => now(),
                ]);

                foreach ($items as $item) {
                    $receipt->items()->create($item + ['line_total' => round($item['quantity'] * ($item['unit_cost'] - $item['discount']), 2)]);
                    $this->stockIn($receipt, $item, $settings);
                }

                // 3) — Optional advance payment against the PO (BEFORE invoice
                // so applyOrderAdvance can auto-deduct it from the due amount)
                if (! empty($advanceMethods)) {
                    $this->recordAdvance($order, $advanceMethods, $validated['advance_date'] ?? now()->toDateString());
                }

                // 4) — Optional invoice (bills payable / AP)
                $invoice = null;
                $createInvoice = array_key_exists('create_invoice', $validated)
                    ? (int) $validated['create_invoice'] === 1
                    : $settings->auto_create_invoice_on_receipt;

                if ($createInvoice) {
                    $invoice = app(PurchaseInvoiceController::class)->createFromReceipt($receipt, $settings);
                }

                return [$order, $receipt, $invoice];
            });
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage())->withInput();
        }

        if ($invoice) {
            return redirect()->route('purchase.receipts.show', $receipt)
                ->with('success', 'মাল রিসিভ হয়েছে, বিল তৈরি হয়েছে এবং স্টক + অ্যাকাউন্টিং আপডেট হয়েছে।');
        }

        return redirect()->route('purchase.receipts.show', $receipt)
            ->with('success', 'মাল রিসিভ হয়েছে এবং স্টক আপডেট হয়েছে।');
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

    private function parseAdvance(Request $request): array
    {
        $methods = json_decode($request->input('advance_methods_json', '[]'), true) ?: [];

        if (empty($methods) || ! is_array($methods)) {
            return [];
        }

        foreach ($methods as $m) {
            if (empty($m['method']) || (float) ($m['amount'] ?? 0) <= 0) {
                return [];
            }
        }

        return $methods;
    }

    private function recordAdvance(PurchaseOrder $order, array $methods, string $paymentDate): void
    {
        $amount = round(array_sum(array_column($methods, 'amount')), 2);

        if ($amount <= 0) {
            return;
        }

        $payment = $order->advancePayments()->create([
            'supplier_id' => $order->supplier_id,
            'purchase_order_id' => $order->id,
            'amount' => $amount,
            'method' => $methods[0]['method'] ?? 'cash',
            'reference' => $methods[0]['reference'] ?? null,
            'payment_date' => $paymentDate,
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

    private function stockIn(PurchaseReceipt $receipt, array $item, PurchaseSetting $settings): void
    {
        $product = Product::find($item['product_id']);
        $variant = $item['variant_id'] ? ProductVariant::find($item['variant_id']) : null;

        $cost = (float) $item['unit_cost'];

        if ($settings->update_cost_price_on_receipt) {
            if ($variant) {
                $variant->updateQuietly(['cost_price' => $cost]);
            } else {
                $product->updateQuietly(['cost_price' => $cost]);
            }
        }

        if ($variant) {
            $variant->increment('stock_quantity', $item['quantity']);
            $product?->recalculateStock();
        } else {
            $product?->increment('stock_quantity', $item['quantity']);
            $product?->refresh();
        }

        StockMovement::create([
            'product_id' => $item['product_id'],
            'variant_id' => $item['variant_id'] ?? null,
            'warehouse_id' => $receipt->warehouse_id,
            'type' => 'in',
            'quantity' => $item['quantity'],
            'reference' => $receipt->receipt_number,
            'notes' => 'Direct Purchase',
            'created_by' => auth()->id(),
        ]);
    }
}
