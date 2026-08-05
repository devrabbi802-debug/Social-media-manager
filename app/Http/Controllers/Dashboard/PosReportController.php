<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\PosOrder;
use App\Models\PosRefund;
use App\Models\PosSession;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PosReportController extends Controller
{
    public function index(Request $request)
    {
        $from = $request->filled('from') ? $request->from : now()->startOfDay()->format('Y-m-d');
        $to = $request->filled('to') ? $request->to : now()->endOfDay()->format('Y-m-d');

        $completedOrders = PosOrder::with(['items', 'payments', 'session'])
            ->where('status', 'completed')
            ->whereBetween('pos_orders.created_at', [$from.' 00:00:00', $to.' 23:59:59']);

        $baseQuery = PosOrder::whereBetween('pos_orders.created_at', [$from.' 00:00:00', $to.' 23:59:59']);

        $summary = [
            'gross_sales' => (clone $completedOrders)->sum('total'),
            'net_sales' => 0,
            'sales_count' => (clone $completedOrders)->count(),
            'tax_collected' => (clone $completedOrders)->sum('tax_amount'),
            'discounts' => (clone $completedOrders)->sum('discount_amount'),
            'items_sold' => (clone $completedOrders)->get()->sum(fn ($o) => $o->items->sum('quantity')),
            'refunds' => PosRefund::where('status', 'completed')
                ->whereBetween('created_at', [$from.' 00:00:00', $to.' 23:59:59'])
                ->sum('amount'),
            'profit' => 0,
        ];

        $summary['net_sales'] = round($summary['gross_sales'] - $summary['refunds'], 2);

        // Profit from stored costs
        $profit = 0;
        $orders = (clone $completedOrders)->get();
        foreach ($orders as $order) {
            foreach ($order->items as $item) {
                $profit += ($item->unit_price - $item->unit_cost) * $item->quantity;
            }
        }
        $summary['profit'] = round($profit, 2);

        // Payment method breakdown (from payments table, refunds subtracted proportionally not applied)
        $paymentBreakdown = DB::table('pos_payments')
            ->join('pos_orders', 'pos_orders.id', '=', 'pos_payments.pos_order_id')
            ->where('pos_orders.status', 'completed')
            ->whereBetween('pos_orders.created_at', [$from.' 00:00:00', $to.' 23:59:59'])
            ->selectRaw('pos_payments.method, SUM(pos_payments.amount) as total, COUNT(*) as count')
            ->groupBy('pos_payments.method')
            ->orderByDesc('total')
            ->get();

        // Top selling products
        $topProducts = DB::table('pos_order_items')
            ->join('pos_orders', 'pos_orders.id', '=', 'pos_order_items.pos_order_id')
            ->where('pos_orders.status', 'completed')
            ->whereBetween('pos_orders.created_at', [$from.' 00:00:00', $to.' 23:59:59'])
            ->selectRaw('pos_order_items.name, SUM(pos_order_items.quantity) as quantity, SUM(pos_order_items.total_price) as revenue')
            ->groupBy('pos_order_items.name')
            ->orderByDesc('quantity')
            ->limit(10)
            ->get();

        // Category breakdown
        $categoryBreakdown = DB::table('pos_order_items')
            ->join('pos_orders', 'pos_orders.id', '=', 'pos_order_items.pos_order_id')
            ->join('products', 'products.id', '=', 'pos_order_items.product_id')
            ->leftJoin('categories', 'categories.id', '=', 'products.category_id')
            ->where('pos_orders.status', 'completed')
            ->whereBetween('pos_orders.created_at', [$from.' 00:00:00', $to.' 23:59:59'])
            ->selectRaw('COALESCE(categories.name, "Uncategorized") as category, SUM(pos_order_items.quantity) as quantity, SUM(pos_order_items.total_price) as revenue')
            ->groupBy('category')
            ->orderByDesc('revenue')
            ->get();

        // Daily sales trend
        $dailySales = (clone $baseQuery)
            ->selectRaw('DATE(pos_orders.created_at) as date, SUM(CASE WHEN status = "completed" THEN total ELSE 0 END) as sales')
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        // Sales by cashier
        $cashiers = (clone $completedOrders)
            ->join('users', 'users.id', '=', 'pos_orders.user_id')
            ->selectRaw('users.name, COUNT(pos_orders.id) as count, SUM(pos_orders.total) as total')
            ->groupBy('users.name')
            ->orderByDesc('total')
            ->get();

        $sessions = PosSession::whereBetween('opened_at', [$from.' 00:00:00', $to.' 23:59:59'])
            ->orWhereBetween('closed_at', [$from.' 00:00:00', $to.' 23:59:59'])
            ->with('user')->get();

        return view('tenant.pos.reports.index', compact(
            'summary',
            'paymentBreakdown',
            'topProducts',
            'categoryBreakdown',
            'dailySales',
            'cashiers',
            'sessions',
            'from',
            'to'
        ));
    }
}
