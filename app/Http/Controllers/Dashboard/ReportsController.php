<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\PosOrder;
use App\Models\Product;
use App\Models\PurchaseInvoice;
use App\Models\StockMovement;
use App\Services\AccountingService;

class ReportsController extends Controller
{
    public function index(AccountingService $accounting)
    {
        $monthStart = now()->startOfMonth();

        $orders = Order::where('created_at', '>=', $monthStart)
            ->whereNotIn('status', ['cancelled', 'refunded'])
            ->get();

        $products = Product::with('variants')->get();
        $stockValue = 0;
        $totalStock = 0;
        $lowStock = 0;
        $outOfStock = 0;

        foreach ($products as $product) {
            $stock = $product->variants->isNotEmpty()
                ? $product->variants->sum('stock_quantity')
                : (int) $product->stock_quantity;

            if ($product->variants->isNotEmpty()) {
                $cost = $product->variants->sum(function ($variant) use ($product) {
                    $unitCost = (float) ($variant->cost_price ?? $variant->price ?? $product->base_price);

                    return (float) $variant->stock_quantity * $unitCost;
                });
            } else {
                $cost = (float) $product->stock_quantity * (float) ($product->cost_price ?? $product->base_price);
            }

            $totalStock += $stock;
            $stockValue += $cost;

            if ($stock <= 0) {
                $outOfStock++;
            } elseif ($stock <= $product->low_stock_threshold) {
                $lowStock++;
            }
        }

        $posOrders = PosOrder::where('status', 'completed')
            ->where('created_at', '>=', $monthStart)
            ->get();

        $purchaseInvoices = PurchaseInvoice::where('status', '!=', 'cancelled')
            ->where('invoice_date', '>=', $monthStart->toDateString())
            ->get();

        $netMovement = StockMovement::whereBetween('created_at', [$monthStart, now()])
            ->get()
            ->sum(fn ($m) => $m->type === 'in' ? $m->quantity : ($m->type === 'out' ? -$m->quantity : 0));

        $summary = [
            'sales' => [
                'revenue' => round($orders->sum(fn ($o) => (float) $o->total), 2),
                'orders_count' => $orders->count(),
            ],
            'inventory' => [
                'products_count' => $products->count(),
                'stock_value' => round($stockValue, 2),
                'total_stock' => $totalStock,
                'low_stock' => $lowStock,
                'out_of_stock' => $outOfStock,
                'net_movement' => $netMovement,
            ],
            'pos' => [
                'sales' => round($posOrders->sum('total'), 2),
                'orders_count' => $posOrders->count(),
            ],
            'purchase' => [
                'total' => round($purchaseInvoices->sum('total'), 2),
                'due' => round($purchaseInvoices->sum(fn ($i) => $i->due()), 2),
                'invoice_count' => $purchaseInvoices->count(),
            ],
            'accounting' => $this->accountingSummary($accounting),
        ];

        return view('tenant.reports.hub', compact('summary'));
    }

    private function accountingSummary(AccountingService $accounting): array
    {
        try {
            $accounting->ensureChartOfAccounts();
            $income = $accounting->incomeStatement(now()->startOfMonth(), now()->endOfDay());

            return [
                'income' => $income['total_income'],
                'expense' => $income['total_expense'],
                'net_profit' => $income['net_profit'],
            ];
        } catch (\Throwable) {
            return ['income' => 0, 'expense' => 0, 'net_profit' => 0];
        }
    }
}
