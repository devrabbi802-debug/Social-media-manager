<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrderItem;
use Illuminate\Http\Request;

class SalesReportController extends Controller
{
    public function index(Request $request)
    {
        $from = $request->filled('from') ? $request->date('from') : now()->startOfMonth();
        $to = $request->filled('to') ? $request->date('to') : now()->endOfDay();

        $status = $request->filled('status') ? $request->status : null;
        $paymentStatus = $request->filled('payment_status') ? $request->payment_status : null;

        $query = Order::query()->whereBetween('created_at', [$from->startOfDay(), $to->endOfDay()]);

        if ($status) {
            $query->where('status', $status);
        }

        if ($paymentStatus) {
            $query->where('payment_status', $paymentStatus);
        }

        $orders = (clone $query)->with('items')->latest('created_at')->paginate(25)->withQueryString();

        $allOrders = (clone $query)->with('items')->get();
        $validOrders = $allOrders->reject(fn ($o) => in_array($o->status, ['cancelled', 'refunded'], true));

        $validRevenue = $validOrders->sum(fn ($o) => (float) $o->total);

        $summary = [
            'revenue' => round($validRevenue, 2),
            'orders_count' => $allOrders->count(),
            'valid_orders_count' => $validOrders->count(),
            'avg_order_value' => $validOrders->isEmpty() ? 0 : round($validRevenue / $validOrders->count(), 2),
            'items_sold' => $validOrders->sum(fn ($o) => $o->items->sum('quantity')),
            'discount' => round($validOrders->sum(fn ($o) => (float) $o->discount), 2),
            'shipping' => round($validOrders->sum(fn ($o) => (float) $o->shipping_cost), 2),
            'tax' => round($validOrders->sum(fn ($o) => (float) $o->tax), 2),
            'due' => round($validOrders->where('payment_status', '!=', 'paid')->sum(fn ($o) => (float) $o->total), 2),
        ];

        $byStatus = $allOrders->groupBy('status')->map(
            fn ($g) => ['count' => $g->count(), 'total' => round($g->sum(fn ($o) => (float) $o->total), 2)]
        );

        $byPaymentStatus = $allOrders->groupBy('payment_status')->map(
            fn ($g) => ['count' => $g->count(), 'total' => round($g->sum(fn ($o) => (float) $o->total), 2)]
        );

        $byPaymentMethod = $allOrders->groupBy('payment_method')->map(
            fn ($g) => ['count' => $g->count(), 'total' => round($g->sum(fn ($o) => (float) $o->total), 2)]
        )->sortByDesc('total');

        $dailySales = (clone $query)
            ->selectRaw('DATE(created_at) as date, COUNT(*) as count, SUM(CASE WHEN status NOT IN ("cancelled","refunded") THEN total ELSE 0 END) as sales')
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        $topProducts = OrderItem::whereHas('order', function ($q) use ($from, $to, $status, $paymentStatus) {
            $q->whereBetween('created_at', [$from->startOfDay(), $to->endOfDay()])
                ->whereNotIn('status', ['cancelled', 'refunded']);

            if ($status) {
                $q->where('status', $status);
            }

            if ($paymentStatus) {
                $q->where('payment_status', $paymentStatus);
            }
        })
            ->selectRaw('name, sku, SUM(quantity) as quantity, SUM(total_price) as revenue')
            ->groupBy('name', 'sku')
            ->orderByDesc('revenue')
            ->limit(10)
            ->get();

        return view('tenant.reports.sales', compact(
            'summary',
            'byStatus',
            'byPaymentStatus',
            'byPaymentMethod',
            'dailySales',
            'topProducts',
            'orders',
            'from',
            'to',
            'status',
            'paymentStatus'
        ));
    }
}
