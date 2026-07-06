<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\InventoryAlert;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\StockMovement;
use App\Models\Warehouse;
use Illuminate\Http\Request;

class InventoryController extends Controller
{
    public function index(Request $request)
    {
        $query = Product::with(['category', 'brand', 'variants', 'inventoryAlert']);

        if ($request->filled('search')) {
            $query->search($request->search);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->boolean('low_stock')) {
            $query->whereHas('inventoryAlert', function ($q) {
                $q->whereColumn('threshold', '>=', 'products.stock_quantity');
            });
        }

        $products = $query->latest()->paginate(20);
        $lowStockCount = Product::whereHas('inventoryAlert', function ($q) {
            $q->whereColumn('threshold', '>=', 'products.stock_quantity');
        })->count();
        $outOfStockCount = Product::where('status', 'out_of_stock')->count();
        $totalProducts = Product::count();
        $allProducts = Product::orderBy('name')->get();
        $warehouses = Warehouse::where('is_active', true)->orderBy('name')->get();

        return view('dashboard.inventory.index', compact(
            'products',
            'lowStockCount',
            'outOfStockCount',
            'totalProducts',
            'allProducts',
            'warehouses'
        ));
    }

    public function movements(Request $request)
    {
        $query = StockMovement::with(['product', 'variant', 'warehouse', 'creator']);

        if ($request->filled('product_id')) {
            $query->where('product_id', $request->product_id);
        }

        if ($request->filled('warehouse_id')) {
            $query->where('warehouse_id', $request->warehouse_id);
        }

        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        if ($request->filled('from_date')) {
            $query->whereDate('created_at', '>=', $request->from_date);
        }

        if ($request->filled('to_date')) {
            $query->whereDate('created_at', '<=', $request->to_date);
        }

        $movements = $query->latest()->paginate(30);
        $products = Product::orderBy('name')->get();
        $warehouses = Warehouse::where('is_active', true)->orderBy('name')->get();

        return view('dashboard.inventory.movements', compact('movements', 'products', 'warehouses'));
    }

    public function stockIn(Request $request)
    {
        $validated = $request->validate([
            'product_id' => 'required|exists:products,id',
            'variant_id' => 'nullable|exists:product_variants,id',
            'warehouse_id' => 'required|exists:warehouses,id',
            'quantity' => 'required|integer|min:1',
            'reference' => 'nullable|string|max:255',
            'notes' => 'nullable|string',
        ]);

        $product = Product::findOrFail($validated['product_id']);

        if ($validated['variant_id']) {
            $variant = ProductVariant::findOrFail($validated['variant_id']);
            $variant->increment('stock_quantity', $validated['quantity']);
            $variant->refresh();
            if ($variant->stock_quantity > 0 && $product->status === 'out_of_stock') {
                $product->update(['status' => 'active']);
            }
        } else {
            $product->increment('stock_quantity', $validated['quantity']);
            $product->refresh();
            if ($product->stock_quantity > 0 && $product->status === 'out_of_stock') {
                $product->update(['status' => 'active']);
            }
        }

        StockMovement::create([
            ...$validated,
            'type' => 'in',
            'created_by' => auth()->id(),
        ]);

        return redirect()->route('inventory.movements')
            ->with('success', 'স্টক সফলভাবে যোগ হয়েছে!');
    }

    public function stockOut(Request $request)
    {
        $validated = $request->validate([
            'product_id' => 'required|exists:products,id',
            'variant_id' => 'nullable|exists:product_variants,id',
            'warehouse_id' => 'required|exists:warehouses,id',
            'quantity' => 'required|integer|min:1',
            'reference' => 'nullable|string|max:255',
            'notes' => 'nullable|string',
        ]);

        $product = Product::findOrFail($validated['product_id']);

        if ($validated['variant_id']) {
            $variant = ProductVariant::findOrFail($validated['variant_id']);
            if ($variant->stock_quantity < $validated['quantity']) {
                return back()->with('error', 'পর্যাপ্ত স্টক নেই!');
            }
            $variant->decrement('stock_quantity', $validated['quantity']);
            $variant->refresh();
        } else {
            if ($product->stock_quantity < $validated['quantity']) {
                return back()->with('error', 'পর্যাপ্ত স্টক নেই!');
            }
            $product->decrement('stock_quantity', $validated['quantity']);
            $product->refresh();
            if ($product->stock_quantity <= 0) {
                $product->update(['status' => 'out_of_stock']);
            }
        }

        StockMovement::create([
            ...$validated,
            'type' => 'out',
            'created_by' => auth()->id(),
        ]);

        return redirect()->route('inventory.movements')
            ->with('success', 'স্টক সফলভাবে বের হয়েছে!');
    }

    public function adjustStock(Request $request)
    {
        $validated = $request->validate([
            'product_id' => 'required|exists:products,id',
            'variant_id' => 'nullable|exists:product_variants,id',
            'warehouse_id' => 'required|exists:warehouses,id',
            'quantity' => 'required|integer|min:0',
            'notes' => 'nullable|string',
        ]);

        $product = Product::findOrFail($validated['product_id']);

        if ($validated['variant_id']) {
            $variant = ProductVariant::findOrFail($validated['variant_id']);
            $variant->update(['stock_quantity' => $validated['quantity']]);
        } else {
            $product->update(['stock_quantity' => $validated['quantity']]);
            if ($validated['quantity'] <= 0) {
                $product->update(['status' => 'out_of_stock']);
            } elseif ($product->status === 'out_of_stock') {
                $product->update(['status' => 'active']);
            }
        }

        StockMovement::create([
            ...$validated,
            'type' => 'adjustment',
            'created_by' => auth()->id(),
        ]);

        return redirect()->route('inventory.movements')
            ->with('success', 'স্টক অ্যাডজাস্ট হয়েছে!');
    }

    public function alerts(Request $request)
    {
        $query = InventoryAlert::with(['product.category', 'product.brand'])
            ->where('is_active', true);

        if ($request->boolean('low_only')) {
            $query->whereHas('product', function ($q) {
                $q->whereColumn('inventory_alerts.threshold', '>=', 'products.stock_quantity');
            });
        }

        $alerts = $query->paginate(20);

        return view('dashboard.inventory.alerts', compact('alerts'));
    }

    public function storeAlert(Request $request)
    {
        $validated = $request->validate([
            'product_id' => 'required|exists:products,id|unique:inventory_alerts,product_id',
            'threshold' => 'required|integer|min:0',
        ]);

        InventoryAlert::create($validated);

        return back()->with('success', 'স্টক অ্যালার্ট সেট করা হয়েছে!');
    }

    public function updateAlert(InventoryAlert $alert)
    {
        $validated = request()->validate([
            'threshold' => 'required|integer|min:0',
            'is_active' => 'boolean',
        ]);

        $validated['is_active'] = request()->boolean('is_active');

        $alert->update($validated);

        return back()->with('success', 'অ্যালার্ট আপডেট হয়েছে!');
    }

    public function destroyAlert(InventoryAlert $alert)
    {
        $alert->delete();

        return back()->with('success', 'অ্যালার্ট ডিলিট হয়েছে!');
    }
}
