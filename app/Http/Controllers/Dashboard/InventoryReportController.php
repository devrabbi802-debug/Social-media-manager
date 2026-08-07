<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\StockMovement;
use App\Models\Warehouse;
use Illuminate\Http\Request;

class InventoryReportController extends Controller
{
    public function index(Request $request)
    {
        $from = $request->filled('from') ? $request->date('from') : now()->startOfMonth();
        $to = $request->filled('to') ? $request->date('to') : now()->endOfDay();

        $type = $request->filled('type') ? $request->type : null;
        $warehouseId = $request->filled('warehouse_id') ? (int) $request->warehouse_id : null;

        $products = Product::with(['category', 'brand', 'variants'])->get();

        $stockValue = 0;
        $totalStock = 0;
        $lowStockCount = 0;
        $outOfStockCount = 0;

        $byCategory = collect();
        $byBrand = collect();

        foreach ($products as $product) {
            if ($product->variants->isNotEmpty()) {
                $stock = $product->variants->sum('stock_quantity');
                $value = 0;

                foreach ($product->variants as $variant) {
                    $unitCost = (float) ($variant->cost_price ?? $variant->price ?? $product->base_price);
                    $value += (float) $variant->stock_quantity * $unitCost;
                }
            } else {
                $stock = (int) $product->stock_quantity;
                $unitCost = (float) ($product->cost_price ?? $product->base_price);
                $value = $stock * $unitCost;
            }

            $totalStock += $stock;
            $stockValue += $value;

            if ($stock <= 0) {
                $outOfStockCount++;
            } elseif ($stock <= $product->low_stock_threshold) {
                $lowStockCount++;
            }

            $categoryName = $product->category?->name ?? 'Uncategorized';
            $cat = $byCategory->get($categoryName) ?? ['category' => $categoryName, 'stock' => 0, 'value' => 0, 'count' => 0];
            $cat['stock'] += $stock;
            $cat['value'] += $value;
            $cat['count']++;
            $byCategory->put($categoryName, $cat);

            $brandName = $product->brand?->name ?? 'No Brand';
            $brand = $byBrand->get($brandName) ?? ['brand' => $brandName, 'stock' => 0, 'value' => 0, 'count' => 0];
            $brand['stock'] += $stock;
            $brand['value'] += $value;
            $brand['count']++;
            $byBrand->put($brandName, $brand);
        }

        $byCategory = $byCategory->values()->sortByDesc('value');
        $byBrand = $byBrand->values()->sortByDesc('value');

        $movementQuery = StockMovement::with(['product', 'variant', 'warehouse', 'creator'])
            ->whereBetween('created_at', [$from->startOfDay(), $to->endOfDay()]);

        if ($type) {
            $movementQuery->where('type', $type);
        }

        if ($warehouseId) {
            $movementQuery->where('warehouse_id', $warehouseId);
        }

        $movements = (clone $movementQuery)->latest('created_at')->paginate(25)->withQueryString();

        $byType = (clone $movementQuery)
            ->selectRaw('type, SUM(quantity) as quantity, COUNT(*) as count')
            ->groupBy('type')
            ->get();

        $topMoved = (clone $movementQuery)
            ->selectRaw('product_id, variant_id, SUM(quantity) as quantity')
            ->groupBy('product_id', 'variant_id')
            ->orderByDesc('quantity')
            ->limit(10)
            ->get();

        $inQty = 0;
        $outQty = 0;

        foreach ($byType as $row) {
            if ($row->type === 'in') {
                $inQty += $row->quantity;
            } elseif ($row->type === 'out') {
                $outQty += $row->quantity;
            }
        }

        $lowStockProducts = $products->filter(function ($product) {
            $stock = $product->variants->isNotEmpty()
                ? $product->variants->sum('stock_quantity')
                : (int) $product->stock_quantity;

            return $stock > 0 && $stock <= $product->low_stock_threshold;
        });

        $warehouses = Warehouse::orderBy('name')->get();

        $summary = [
            'total_products' => $products->count(),
            'total_stock' => $totalStock,
            'stock_value' => round($stockValue, 2),
            'low_stock_count' => $lowStockCount,
            'out_of_stock_count' => $outOfStockCount,
            'movements_count' => (clone $movementQuery)->count(),
            'in_qty' => $inQty,
            'out_qty' => $outQty,
            'net_movement' => $inQty - $outQty,
        ];

        return view('tenant.reports.inventory', compact(
            'summary',
            'byCategory',
            'byBrand',
            'byType',
            'topMoved',
            'movements',
            'lowStockProducts',
            'warehouses',
            'from',
            'to',
            'type',
            'warehouseId'
        ));
    }
}
