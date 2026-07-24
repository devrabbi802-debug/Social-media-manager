<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Customer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class OrderController extends Controller
{
    public function index(Request $request)
    {
        $query = Order::with(['items', 'customer']);

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('order_number', 'like', "%{$search}%")
                  ->orWhere('customer_name', 'like', "%{$search}%")
                  ->orWhere('customer_phone', 'like', "%{$search}%");
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('payment_status')) {
            $query->where('payment_status', $request->payment_status);
        }

        if ($request->filled('payment_method')) {
            $query->where('payment_method', $request->payment_method);
        }

        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        $sortField = $request->get('sort', 'created_at');
        $sortDir = $request->get('dir', 'desc');
        $allowedSorts = ['created_at', 'order_number', 'total', 'status'];
        if (in_array($sortField, $allowedSorts)) {
            $query->orderBy($sortField, $sortDir === 'asc' ? 'asc' : 'desc');
        } else {
            $query->latest();
        }

        $orders = $query->paginate(20)->withQueryString();

        $totalOrders = Order::count();
        $pendingCount = Order::where('status', 'pending')->count();
        $processingCount = Order::where('status', 'processing')->count();
        $deliveredCount = Order::where('status', 'delivered')->count();
        $cancelledCount = Order::where('status', 'cancelled')->count();
        $totalRevenue = Order::whereNotIn('status', ['cancelled', 'refunded'])->sum('total');

        return view('tenant.orders.index', compact(
            'orders', 'totalOrders', 'pendingCount', 'processingCount',
            'deliveredCount', 'cancelledCount', 'totalRevenue'
        ));
    }

    public function show(Request $request, Order $order)
    {
        $order->load(['items.product', 'items.variant', 'customer', 'shippingAddress']);
        return view('tenant.orders.show', compact('order'));
    }

    public function edit(Order $order)
    {
        $order->load(['items', 'customer', 'shippingAddress']);
        $statuses = ['pending', 'processing', 'shipped', 'delivered', 'cancelled', 'refunded'];
        $paymentStatuses = ['pending', 'paid', 'failed', 'refunded'];
        return view('tenant.orders.edit', compact('order', 'statuses', 'paymentStatuses'));
    }

    public function update(Request $request, Order $order)
    {
        $validated = $request->validate([
            'status' => 'sometimes|in:pending,processing,shipped,delivered,cancelled,refunded',
            'payment_status' => 'sometimes|in:pending,paid,failed,refunded',
            'notes' => 'nullable|string|max:2000',
            'carrier' => 'nullable|string|max:255',
            'tracking_id' => 'nullable|string|max:255',
            'estimated_delivery' => 'nullable|date',
        ]);

        $order->update($validated);

        return redirect()->route('orders.show', $order)
            ->with('success', __('orders.updated'));
    }

    public function bulkUpdate(Request $request)
    {
        $request->validate([
            'order_ids' => 'required|array',
            'order_ids.*' => 'exists:orders,id',
            'status' => 'required|in:pending,processing,shipped,delivered,cancelled,refunded',
        ]);

        $count = Order::whereIn('id', $request->order_ids)
            ->update(['status' => $request->status]);

        return redirect()->route('orders.index')
            ->with('success', __('orders.bulk_updated', ['count' => $count]));
    }

    public function export(Request $request)
    {
        $query = Order::with(['items', 'customer']);

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        $orders = $query->latest()->get();

        $filename = 'orders-export-' . now()->format('Y-m-d-His') . '.csv';
        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"$filename\"",
        ];

        $callback = function () use ($orders) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, [
                __('orders.order_number'), __('orders.customer'), __('orders.phone'),
                __('orders.total'), __('orders.status'), __('orders.payment_status'),
                __('orders.payment_method'), __('orders.items'), __('orders.date'),
            ]);

            foreach ($orders as $order) {
                fputcsv($handle, [
                    $order->order_number,
                    $order->customer_name,
                    $order->customer_phone,
                    number_format($order->total, 2),
                    $order->status,
                    $order->payment_status,
                    $order->payment_method,
                    $order->items->count(),
                    $order->created_at->format('Y-m-d H:i'),
                ]);
            }
            fclose($handle);
        };

        return response()->stream($callback, 200, $headers);
    }

    public function print(Order $order)
    {
        $order->load(['items.product', 'items.variant', 'customer', 'shippingAddress']);
        return view('tenant.orders.print', compact('order'));
    }
}
