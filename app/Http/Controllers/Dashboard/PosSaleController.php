<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\AccountingSetting;
use App\Models\BusinessSetting;
use App\Models\PosOrder;
use App\Models\PosRefund;
use App\Models\PosSetting;
use App\Models\StockMovement;
use App\Models\StorefrontSettings;
use App\Models\Warehouse;
use App\Services\AccountingService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PosSaleController extends Controller
{
    public function index(Request $request)
    {
        $query = PosOrder::with(['user', 'customer', 'items'])
            ->where('status', '!=', 'hold');

        if ($request->filled('order_number')) {
            $query->where('order_number', 'like', "%{$request->order_number}%");
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('payment_method')) {
            $query->where('payment_method', $request->payment_method);
        }

        if ($request->filled('from')) {
            $query->whereDate('created_at', '>=', $request->from);
        }

        if ($request->filled('to')) {
            $query->whereDate('created_at', '<=', $request->to);
        }

        $orders = $query->latest()->paginate(20)->withQueryString();

        $settings = PosSetting::current();
        $paymentAccounts = $settings->paymentAccounts();

        $totalSales = (clone $query)->where('status', 'completed')->sum('total');
        $totalOrders = (clone $query)->count();
        $totalRefunds = PosRefund::where('status', 'completed')
            ->whereBetween('created_at', [$request->from ?? now()->startOfMonth(), $request->to ?? now()->endOfMonth()])
            ->sum('amount');

        return view('tenant.pos.sales.index', compact('orders', 'totalSales', 'totalOrders', 'totalRefunds', 'paymentAccounts'));
    }

    public function show(PosOrder $order)
    {
        $order->load(['user', 'customer', 'items', 'payments', 'refunds', 'session']);
        $settings = PosSetting::current();
        $paymentAccounts = $settings->paymentAccounts()->filter(fn ($a) => $settings->isEnabled($a->code))->values();

        return view('tenant.pos.sales.show', compact('order', 'paymentAccounts'));
    }

    public function receipt(PosOrder $order)
    {
        $order->load(['user', 'customer', 'items', 'payments']);
        $settings = PosSetting::current();

        // Company branding: per-user BusinessSetting first, fallback to storefront/store settings
        $business = BusinessSetting::where('user_id', $order->user_id)->first();
        $storefront = StorefrontSettings::first();

        $baseUrl = request()->getSchemeAndHttpHost();
        $companyLogoUrl = null;
        if ($business && $business->logo_path) {
            $companyLogoUrl = $baseUrl.'/storage/'.ltrim($business->logo_path, '/');
        } elseif ($storefront && $storefront->store_logo) {
            $companyLogoUrl = $baseUrl.'/storage/'.ltrim($storefront->store_logo, '/');
        }

        $companyName = null;
        if ($business && $business->business_name) {
            $companyName = $business->business_name;
        } elseif ($storefront && $storefront->store_name) {
            $companyName = $storefront->store_name;
        }

        return view('tenant.pos.sales.receipt', compact('order', 'settings', 'companyLogoUrl', 'companyName'));
    }

    public function refund(PosOrder $order, Request $request)
    {
        if ($order->status !== 'completed') {
            return back()->with('error', 'সম্পন্ন অর্ডার রিফান্ড করা যাবে।');
        }

        $validated = $request->validate([
            'amount' => 'required|numeric|min:0.01',
            'method' => 'required|string|max:50',
            'reason' => 'nullable|string|max:1000',
            'items' => 'nullable|array',
            'items.*.id' => 'required|integer',
            'items.*.quantity' => 'required|integer|min:0',
        ]);

        $refundable = max((float) $order->total - $order->refundedTotal(), 0);

        if ($validated['amount'] > $refundable + 0.01) {
            return back()->with('error', 'রিফান্ডের পরিমাণ বাকি রিফান্ডযোগ্য টাকার বেশি হতে পারবে না।');
        }

        try {
            DB::transaction(function () use ($order, $validated) {
                $settings = PosSetting::current();
                $warehouse = Warehouse::find($settings->default_warehouse_id)
                    ?? Warehouse::where('is_active', true)->first();

                $refund = PosRefund::create([
                    'pos_order_id' => $order->id,
                    'user_id' => auth()->id(),
                    'amount' => $validated['amount'],
                    'method' => $validated['method'],
                    'status' => 'completed',
                    'reason' => $validated['reason'] ?? null,
                    'refunded_at' => now(),
                ]);

                $fullRefund = abs((float) $validated['amount'] - (float) $order->total) < 0.01;
                $order->status = $fullRefund ? 'refunded' : 'completed';
                $order->payment_status = $fullRefund ? 'refunded' : ($order->payment_status === 'partial' ? 'partial' : 'refunded');
                $order->save();

                // Restore stock for returned quantities
                $returnedCost = 0;
                if (! empty($validated['items']) && $warehouse) {
                    foreach ($validated['items'] as $returned) {
                        if (! $returned['quantity']) {
                            continue;
                        }

                        $item = $order->items()->find($returned['id']);
                        if (! $item) {
                            continue;
                        }

                        $qty = min((int) $returned['quantity'], $item->quantity);
                        $returnedCost += (float) $item->unit_cost * $qty;

                        if ($item->variant_id) {
                            $item->variant?->increment('stock_quantity', $qty);
                            $item->product?->recalculateStock();
                        } else {
                            $item->product?->increment('stock_quantity', $qty);
                            $item->product?->refresh();
                        }

                        StockMovement::create([
                            'product_id' => $item->product_id,
                            'variant_id' => $item->variant_id,
                            'warehouse_id' => $warehouse->id,
                            'type' => 'in',
                            'quantity' => $qty,
                            'reference' => $refund->refund_number,
                            'notes' => 'POS Refund',
                            'created_by' => auth()->id(),
                        ]);
                    }
                }

                if (AccountingSetting::current()->post_pos_refunds) {
                    app(AccountingService::class)->postPosRefund($order, $refund, $returnedCost);
                }

                // Reflect refund on open session
                $session = $order->session;
                if ($session && $session->status === 'open') {
                    $session->refunds_total = round((float) $session->refunds_total + (float) $validated['amount'], 2);
                    $session->total_sales = max(round((float) $session->total_sales - (float) $validated['amount'], 2), 0);
                    $session->save();
                }
            });

            return back()->with('success', 'রিফান্ড সফলভাবে সম্পন্ন হয়েছে!');
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }
}
