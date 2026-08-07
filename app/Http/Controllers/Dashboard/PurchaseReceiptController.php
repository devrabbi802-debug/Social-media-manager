<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\BusinessSetting;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderItem;
use App\Models\PurchaseReceipt;
use App\Models\PurchaseSetting;
use App\Models\StockMovement;
use App\Models\Supplier;
use App\Models\Warehouse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class PurchaseReceiptController extends Controller
{
    public function index(Request $request)
    {
        $query = PurchaseReceipt::with(['supplier', 'purchaseOrder']);

        if ($request->filled('search')) {
            $query->where('receipt_number', 'like', "%{$request->search}%");
        }

        if ($request->filled('supplier_id')) {
            $query->where('supplier_id', $request->supplier_id);
        }

        if ($request->filled('from')) {
            $query->whereDate('receipt_date', '>=', $request->from);
        }

        if ($request->filled('to')) {
            $query->whereDate('receipt_date', '<=', $request->to);
        }

        $receipts = $query->latest()->paginate(20)->withQueryString();
        $suppliers = Supplier::active()->orderBy('name')->get();

        return view('tenant.purchase.receipts.index', compact('receipts', 'suppliers'));
    }

    public function create(Request $request)
    {
        $order = null;
        if ($request->filled('po_id')) {
            $order = PurchaseOrder::with([
                'supplier',
                'items.product',
                'items.variant',
                'advancePayments.methods',
            ])
                ->findOrFail($request->po_id);

            if (! $order->isReceivable()) {
                return back()->with('error', 'এই অর্ডারের জন্য আর রিসিভ করার কিছু নেই।');
            }
        }

        $suppliers = Supplier::active()->orderBy('name')->get();
        $warehouses = Warehouse::active()->orderBy('name')->get();
        $settings = PurchaseSetting::current();

        $openOrders = PurchaseOrder::with(['supplier', 'items'])
            ->where('status', '!=', 'cancelled')
            ->orderByDesc('order_date')
            ->get()
            ->filter(fn ($o) => $o->isReceivable())
            ->values();

        return view('tenant.purchase.receipts.form', [
            'order' => $order,
            'suppliers' => $suppliers,
            'warehouses' => $warehouses,
            'openOrders' => $openOrders,
            'defaultWarehouseId' => $settings->default_warehouse_id,
            'autoInvoice' => $settings->auto_create_invoice_on_receipt,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'supplier_id' => 'required|exists:suppliers,id',
            'warehouse_id' => 'required|exists:warehouses,id',
            'receipt_date' => 'required|date',
            'purchase_order_id' => 'nullable|exists:purchase_orders,id',
            'create_invoice' => 'nullable|in:1,0',
            'discount_type' => 'nullable|in:fixed,percent',
            'discount_value' => 'nullable|numeric|min:0',
            'tax_rate' => 'nullable|numeric|min:0|max:100',
            'notes' => 'nullable|string',
        ]);

        $items = $this->validateItems($request);

        if (empty($items)) {
            return back()->with('error', 'কমপক্ষে একটি পণ্য যোগ করুন।')->withInput();
        }

        $settings = PurchaseSetting::current();

        $subtotal = round(array_sum(array_map(fn ($i) => $i['quantity'] * ($i['unit_cost'] - $i['discount']), $items)), 2);
        $discountType = $validated['discount_type'] ?? null;
        $discountValue = (float) ($validated['discount_value'] ?? 0);
        if ($discountType === 'percent') {
            $discountAmount = round($subtotal * ($discountValue / 100), 2);
        } else {
            $discountAmount = min($discountValue, $subtotal);
        }
        $taxable = max($subtotal - $discountAmount, 0);
        $taxRate = (float) ($validated['tax_rate'] ?? 0);
        $taxAmount = round($taxable * ($taxRate / 100), 2);
        $total = round($taxable + $taxAmount, 2);

        try {
            [$receipt, $invoice] = DB::transaction(function () use ($validated, $items, $settings, $subtotal, $discountType, $discountValue, $discountAmount, $taxRate, $taxAmount, $total) {
                $receipt = PurchaseReceipt::create([
                    'purchase_order_id' => $validated['purchase_order_id'] ?? null,
                    'supplier_id' => $validated['supplier_id'],
                    'warehouse_id' => $validated['warehouse_id'],
                    'receipt_date' => $validated['receipt_date'],
                    'status' => 'received',
                    'subtotal' => $subtotal,
                    'discount_type' => $discountType,
                    'discount_value' => $discountValue,
                    'discount_amount' => $discountAmount,
                    'tax_rate' => $taxRate,
                    'tax_amount' => $taxAmount,
                    'total' => $total,
                    'notes' => $validated['notes'] ?? null,
                    'created_by' => auth()->id(),
                    'received_at' => now(),
                ]);

                foreach ($items as $item) {
                    $receipt->items()->create($item + ['line_total' => round($item['quantity'] * ($item['unit_cost'] - $item['discount']), 2)]);
                    $this->stockIn($receipt, $item, $settings);

                    if (! empty($item['purchase_order_item_id'])) {
                        $orderItem = PurchaseOrderItem::find($item['purchase_order_item_id']);
                        $orderItem?->increment('received_quantity', $item['quantity']);
                    }
                }

                if ($validated['purchase_order_id']) {
                    $this->updateOrderProgress($validated['purchase_order_id']);
                }

                $invoice = null;
                $createInvoice = array_key_exists('create_invoice', $validated)
                    ? (int) $validated['create_invoice'] === 1
                    : $settings->auto_create_invoice_on_receipt;

                if ($createInvoice) {
                    $invoice = app(PurchaseInvoiceController::class)->createFromReceipt($receipt, $settings);
                }

                return [$receipt, $invoice];
            });
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage())->withInput();
        }

        if ($invoice) {
            return redirect()->route('purchase.invoices.show', $invoice)
                ->with('success', 'মাল রিসিভ হয়েছে এবং বিল (invoice) তৈরি হয়েছে।');
        }

        return redirect()->route('purchase.receipts.show', $receipt)
            ->with('success', 'মাল সফলভাবে রিসিভ হয়েছে।');
    }

    public function show(PurchaseReceipt $receipt)
    {
        $receipt->load(['supplier', 'warehouse', 'purchaseOrder.advancePayments.methods', 'items.product', 'items.variant', 'creator', 'invoice']);

        return view('tenant.purchase.receipts.show', compact('receipt'));
    }

    public function print(PurchaseReceipt $receipt)
    {
        $receipt->load(['supplier', 'warehouse', 'purchaseOrder', 'items.product', 'items.variant', 'creator']);
        $business = BusinessSetting::where('user_id', $receipt->created_by)->first();

        return view('tenant.purchase.receipts.print', compact('receipt', 'business'));
    }

    public function destroy(PurchaseReceipt $receipt)
    {
        if ($receipt->invoice()->exists()) {
            return back()->with('error', 'এই রিসিপ্টের সাথে বিল লিংক আছে। আগে বিল ডিলিট/বাতিল করুন।');
        }

        try {
            DB::transaction(function () use ($receipt) {
                $warehouseId = $receipt->warehouse_id;

                foreach ($receipt->items as $item) {
                    $this->restoreStock($item->product_id, $item->variant_id, $item->quantity, $warehouseId, $receipt->receipt_number);
                }

                $receipt->items()->delete();
                $receipt->delete();

                if ($receipt->purchase_order_id) {
                    $this->updateOrderProgress($receipt->purchase_order_id);
                }
            });
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }

        return redirect()->route('purchase.receipts.index')->with('success', 'রিসিপ্ট ডিলিট করা হয়েছে, স্টক ফিরিয়ে নেওয়া হয়েছে।');
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
                'purchase_order_item_id' => 'nullable|exists:purchase_order_items,id',
            ]);

            if ($validator->fails()) {
                continue;
            }

            $clean[] = [
                'purchase_order_item_id' => $item['purchase_order_item_id'] ?? null,
                'product_id' => (int) $item['product_id'],
                'variant_id' => ! empty($item['variant_id']) ? (int) $item['variant_id'] : null,
                'name' => $item['name'],
                'sku' => $item['sku'] ?? null,
                'quantity' => (int) $item['quantity'],
                'unit_cost' => (float) $item['unit_cost'],
                'discount' => (float) ($item['discount'] ?? 0),
            ];
        }

        return $clean;
    }

    /**
     * Increase stock + record movement. Optionally updates the product/variant cost price.
     */
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
            'notes' => 'Purchase Receipt',
            'created_by' => auth()->id(),
        ]);
    }

    private function restoreStock($productId, $variantId, int $quantity, $warehouseId, string $reference): void
    {
        $product = Product::find($productId);

        if ($variantId) {
            $variant = ProductVariant::find($variantId);
            $variant?->decrement('stock_quantity', $quantity);
            $product?->recalculateStock();
        } else {
            $product?->decrement('stock_quantity', $quantity);
        }

        StockMovement::create([
            'product_id' => $productId,
            'variant_id' => $variantId,
            'warehouse_id' => $warehouseId,
            'type' => 'out',
            'quantity' => $quantity,
            'reference' => 'Receipt deleted: '.$reference,
            'notes' => 'Purchase receipt deleted',
            'created_by' => auth()->id(),
        ]);
    }

    private function updateOrderProgress(int $orderId): void
    {
        $order = PurchaseOrder::with('items')->find($orderId);

        if (! $order) {
            return;
        }

        $receivedQty = $order->items->sum('received_quantity');
        $totalQty = $order->items->sum('quantity');

        if ($receivedQty >= $totalQty && $totalQty > 0) {
            $order->updateQuietly([
                'status' => 'received',
                'received_at' => now(),
            ]);
        } elseif ($receivedQty > 0) {
            $order->updateQuietly(['status' => 'partially_received']);
        } else {
            $order->updateQuietly(['status' => 'ordered']);
        }
    }
}
