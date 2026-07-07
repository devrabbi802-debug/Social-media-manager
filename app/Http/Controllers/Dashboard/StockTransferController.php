<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\StockMovement;
use App\Models\StockTransfer;
use App\Models\Warehouse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class StockTransferController extends Controller
{
    public function index(Request $request)
    {
        $query = StockTransfer::with(['product', 'variant', 'fromWarehouse', 'toWarehouse', 'creator']);

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('product_id')) {
            $query->where('product_id', $request->product_id);
        }

        $transfers = $query->latest()->paginate(20);
        $products = Product::orderBy('name')->get();
        $warehouses = Warehouse::where('is_active', true)->orderBy('name')->get();

        return view('dashboard.inventory.transfers', compact('transfers', 'products', 'warehouses'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'product_id' => 'required|exists:products,id',
            'variant_id' => 'nullable|exists:product_variants,id',
            'from_warehouse_id' => 'required|exists:warehouses,id|different:to_warehouse_id',
            'to_warehouse_id' => 'required|exists:warehouses,id|different:from_warehouse_id',
            'quantity' => 'required|integer|min:1',
            'notes' => 'nullable|string',
        ]);

        $product = Product::findOrFail($validated['product_id']);

        if ($validated['variant_id']) {
            $variant = ProductVariant::findOrFail($validated['variant_id']);
            if ($variant->stock_quantity < $validated['quantity']) {
                return back()->with('error', 'উৎস গুদমে পর্যাপ্ত স্টক নেই!');
            }
        } else {
            if ($product->stock_quantity < $validated['quantity']) {
                return back()->with('error', 'উৎস গুদমে পর্যাপ্ত স্টক নেই!');
            }
        }

        DB::transaction(function () use ($validated, $product) {
            if ($validated['variant_id']) {
                $variant = ProductVariant::findOrFail($validated['variant_id']);
                $variant->decrement('stock_quantity', $validated['quantity']);
            } else {
                $product->decrement('stock_quantity', $validated['quantity']);
            }

            StockMovement::create([
                'product_id' => $validated['product_id'],
                'variant_id' => $validated['variant_id'] ?? null,
                'warehouse_id' => $validated['from_warehouse_id'],
                'type' => 'out',
                'quantity' => $validated['quantity'],
                'reference' => 'Transfer to warehouse #' . $validated['to_warehouse_id'],
                'notes' => $validated['notes'] ?? null,
                'created_by' => auth()->id(),
            ]);

            StockTransfer::create([
                ...$validated,
                'status' => 'pending',
                'created_by' => auth()->id(),
            ]);
        });

        return back()->with('success', 'স্টক ট্রান্সফার অনুরোধ তৈরি হয়েছে!');
    }

    public function complete(StockTransfer $transfer)
    {
        if ($transfer->status !== 'pending') {
            return back()->with('error', 'শুধুমাত্র অপেক্ষমান ট্রান্সফার সম্পন্ন করা যাবে!');
        }

        DB::transaction(function () use ($transfer) {
            $product = $transfer->product;

            if ($transfer->variant_id) {
                $variant = $transfer->variant;
                $variant->increment('stock_quantity', $transfer->quantity);
            } else {
                $product->increment('stock_quantity', $transfer->quantity);
            }

            StockMovement::create([
                'product_id' => $transfer->product_id,
                'variant_id' => $transfer->variant_id,
                'warehouse_id' => $transfer->to_warehouse_id,
                'type' => 'in',
                'quantity' => $transfer->quantity,
                'reference' => 'Transfer from warehouse #' . $transfer->from_warehouse_id,
                'created_by' => auth()->id(),
            ]);

            $transfer->update(['status' => 'completed']);

            if ($product->variants->count() > 0) {
                $product->recalculateStock();
            }
        });

        return back()->with('success', 'স্টক ট্রান্সফার সম্পন্ন হয়েছে!');
    }

    public function cancel(StockTransfer $transfer)
    {
        if ($transfer->status !== 'pending') {
            return back()->with('error', 'শুধুমাত্র অপেক্ষমান ট্রান্সফার বাতিল করা যাবে!');
        }

        DB::transaction(function () use ($transfer) {
            $product = $transfer->product;

            if ($transfer->variant_id) {
                $variant = $transfer->variant;
                $variant->increment('stock_quantity', $transfer->quantity);
            } else {
                $product->increment('stock_quantity', $transfer->quantity);
            }

            StockMovement::create([
                'product_id' => $transfer->product_id,
                'variant_id' => $transfer->variant_id,
                'warehouse_id' => $transfer->from_warehouse_id,
                'type' => 'in',
                'quantity' => $transfer->quantity,
                'reference' => 'Cancelled transfer #' . $transfer->id,
                'created_by' => auth()->id(),
            ]);

            $transfer->update(['status' => 'cancelled']);

            if ($product->variants->count() > 0) {
                $product->recalculateStock();
            }
        });

        return back()->with('success', 'স্টক ট্রান্সফার বাতিল হয়েছে!');
    }

    public function destroy(StockTransfer $transfer)
    {
        if ($transfer->status === 'completed') {
            return back()->with('error', 'সম্পন্ন ট্রান্সফার ডিলিট করা যাবে না!');
        }

        $transfer->delete();

        return back()->with('success', 'ট্রান্সফার ডিলিট হয়েছে!');
    }
}
