<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\JournalEntry;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\PurchaseReceipt;
use App\Models\PurchaseReturn;
use App\Models\PurchaseSetting;
use App\Models\StockMovement;
use App\Models\Supplier;
use App\Models\Warehouse;
use App\Services\AccountingService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class PurchaseReturnController extends Controller
{
    public function index(Request $request)
    {
        $query = PurchaseReturn::with(['supplier', 'purchaseReceipt']);

        if ($request->filled('search')) {
            $query->where('return_number', 'like', "%{$request->search}%")
                ->orWhereHas('supplier', fn ($q) => $q->where('name', 'like', "%{$request->search}%"));
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('from')) {
            $query->whereDate('return_date', '>=', $request->from);
        }

        if ($request->filled('to')) {
            $query->whereDate('return_date', '<=', $request->to);
        }

        $returns = $query->latest()->paginate(20)->withQueryString();

        return view('tenant.purchase.returns.index', compact('returns'));
    }

    public function create(Request $request)
    {
        $receipt = null;
        if ($request->filled('receipt_id')) {
            $receipt = PurchaseReceipt::with(['supplier', 'items.product', 'items.variant'])
                ->findOrFail($request->receipt_id);
        }

        $suppliers = Supplier::active()->orderBy('name')->get();
        $warehouses = Warehouse::active()->orderBy('name')->get();

        return view('tenant.purchase.returns.form', compact('receipt', 'suppliers', 'warehouses'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'supplier_id' => 'required|exists:suppliers,id',
            'warehouse_id' => 'required|exists:warehouses,id',
            'return_date' => 'required|date',
            'purchase_receipt_id' => 'nullable|exists:purchase_receipts,id',
            'reason' => 'nullable|string',
        ]);

        $items = $this->validateItems($request);

        if (empty($items)) {
            return back()->with('error', 'কমপক্ষে একটি পণ্য যোগ করুন।')->withInput();
        }

        $total = round(array_sum(array_map(fn ($i) => $i['quantity'] * $i['unit_cost'], $items)), 2);

        try {
            $return = DB::transaction(function () use ($validated, $items, $total) {
                $return = PurchaseReturn::create([
                    'purchase_receipt_id' => $validated['purchase_receipt_id'] ?? null,
                    'supplier_id' => $validated['supplier_id'],
                    'warehouse_id' => $validated['warehouse_id'],
                    'return_date' => $validated['return_date'],
                    'status' => 'completed',
                    'total' => $total,
                    'reason' => $validated['reason'] ?? null,
                    'created_by' => auth()->id(),
                    'completed_at' => now(),
                ]);

                foreach ($items as $item) {
                    $return->items()->create($item + ['line_total' => round($item['quantity'] * $item['unit_cost'], 2)]);
                    $this->stockOut($return, $item);
                }

                if (PurchaseSetting::current()->auto_post_purchases) {
                    app(AccountingService::class)->postPurchaseReturn($return, $total);
                }

                return $return;
            });
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage())->withInput();
        }

        return redirect()->route('purchase.returns.show', $return)->with('success', 'রিটার্ন সম্পন্ন হয়েছে, স্টক আউট হয়েছে।');
    }

    public function show(PurchaseReturn $return)
    {
        $return->load(['supplier', 'warehouse', 'purchaseReceipt', 'items.product', 'items.variant', 'creator']);

        return view('tenant.purchase.returns.show', compact('return'));
    }

    public function cancel(PurchaseReturn $return)
    {
        if ($return->status !== 'completed') {
            return back()->with('error', 'এই রিটার্ন ইতিমধ্যে বাতিল।');
        }

        try {
            DB::transaction(function () use ($return) {
                foreach ($return->items as $item) {
                    $this->restoreStock($item->product_id, $item->variant_id, $item->quantity, $return->warehouse_id, $return->return_number);
                }

                if (PurchaseSetting::current()->auto_post_purchases) {
                    $entry = JournalEntry::ofReference('purchase_return', $return->id)
                        ->posted()->latest('id')->first();

                    if ($entry) {
                        app(AccountingService::class)->reverse($entry, "Return #{$return->return_number} cancelled");
                    }
                }

                $return->update([
                    'status' => 'cancelled',
                    'completed_at' => null,
                ]);
            });
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }

        return back()->with('success', 'রিটার্ন বাতিল করা হয়েছে, স্টক ফিরে এসেছে।');
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
            ]);

            if ($validator->fails()) {
                continue;
            }

            $clean[] = [
                'product_id' => (int) $item['product_id'],
                'variant_id' => ! empty($item['variant_id']) ? (int) $item['variant_id'] : null,
                'name' => $item['name'],
                'sku' => $item['sku'] ?? null,
                'quantity' => (int) $item['quantity'],
                'unit_cost' => (float) $item['unit_cost'],
            ];
        }

        return $clean;
    }

    private function stockOut(PurchaseReturn $return, array $item): void
    {
        $product = Product::find($item['product_id']);
        $variant = $item['variant_id'] ? ProductVariant::find($item['variant_id']) : null;

        if ($variant) {
            $variant->decrement('stock_quantity', $item['quantity']);
            $product?->recalculateStock();
        } else {
            $product?->decrement('stock_quantity', $item['quantity']);
        }

        StockMovement::create([
            'product_id' => $item['product_id'],
            'variant_id' => $item['variant_id'] ?? null,
            'warehouse_id' => $return->warehouse_id,
            'type' => 'out',
            'quantity' => $item['quantity'],
            'reference' => $return->return_number,
            'notes' => 'Purchase Return',
            'created_by' => auth()->id(),
        ]);
    }

    private function restoreStock($productId, $variantId, int $quantity, $warehouseId, string $reference): void
    {
        $product = Product::find($productId);

        if ($variantId) {
            ProductVariant::find($variantId)?->increment('stock_quantity', $quantity);
            $product?->recalculateStock();
        } else {
            $product?->increment('stock_quantity', $quantity);
        }

        StockMovement::create([
            'product_id' => $productId,
            'variant_id' => $variantId,
            'warehouse_id' => $warehouseId,
            'type' => 'in',
            'quantity' => $quantity,
            'reference' => 'Return cancelled: '.$reference,
            'notes' => 'Purchase return cancelled',
            'created_by' => auth()->id(),
        ]);
    }
}
