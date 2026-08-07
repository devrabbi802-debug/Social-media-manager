<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\PurchaseInvoice;
use App\Models\PurchaseReceiptItem;
use App\Models\Supplier;
use Illuminate\Http\Request;

class PurchaseReportController extends Controller
{
    public function index(Request $request)
    {
        $from = $request->filled('from') ? $request->date('from') : now()->startOfMonth();
        $to = $request->filled('to') ? $request->date('to') : now()->endOfDay();

        $supplierId = $request->filled('supplier_id') ? (int) $request->supplier_id : null;

        $invoiceQuery = PurchaseInvoice::with('supplier')
            ->where('status', '!=', 'cancelled')
            ->whereBetween('invoice_date', [$from, $to]);

        if ($supplierId) {
            $invoiceQuery->where('supplier_id', $supplierId);
        }

        $invoices = $invoiceQuery->get();

        $summary = [
            'total_purchase' => round($invoices->sum('total'), 2),
            'total_paid' => round($invoices->sum('paid_amount'), 2),
            'total_due' => round($invoices->sum(fn ($i) => $i->due()), 2),
            'invoice_count' => $invoices->count(),
            'tax' => round($invoices->sum('tax_amount'), 2),
            'discount' => round($invoices->sum('discount_amount'), 2),
        ];

        $bySupplier = $invoices->groupBy('supplier_id')->map(function ($group) {
            $first = $group->first();

            return [
                'supplier' => $first->supplier,
                'count' => $group->count(),
                'total' => round($group->sum('total'), 2),
                'paid' => round($group->sum('paid_amount'), 2),
                'due' => round($group->sum(fn ($i) => $i->due()), 2),
            ];
        })->values();

        $byProduct = PurchaseReceiptItem::query()
            ->whereHas('receipt', function ($q) use ($from, $to, $supplierId) {
                $q->whereBetween('receipt_date', [$from, $to]);
                if ($supplierId) {
                    $q->where('supplier_id', $supplierId);
                }
            })
            ->get()
            ->groupBy(fn ($item) => $item->product_id ? 'p:'.$item->product_id : 'v:'.$item->variant_id)
            ->map(function ($group) {
                $first = $group->first();

                return [
                    'name' => $first->name,
                    'sku' => $first->sku,
                    'quantity' => $group->sum('quantity'),
                    'cost' => round($group->sum('line_total'), 2),
                ];
            })
            ->values()
            ->sortByDesc('cost');

        // AP aging buckets by due date.
        $aging = $this->agingBuckets(
            PurchaseInvoice::with('supplier')->where('status', '!=', 'cancelled')->get()
        );

        $suppliers = Supplier::active()->orderBy('name')->get();

        return view('tenant.purchase.reports.index', compact(
            'summary',
            'bySupplier',
            'byProduct',
            'aging',
            'suppliers',
            'from',
            'to'
        ));
    }

    private function agingBuckets($invoices): array
    {
        $today = now()->startOfDay();
        $buckets = [
            'current' => ['label' => 'Current (0-30 days)', 'total' => 0, 'count' => 0],
            '31_60' => ['label' => '31-60 days', 'total' => 0, 'count' => 0],
            '61_90' => ['label' => '61-90 days', 'total' => 0, 'count' => 0],
            '90_plus' => ['label' => '90+ days', 'total' => 0, 'count' => 0],
        ];

        foreach ($invoices as $invoice) {
            $due = $invoice->due();
            if ($due <= 0.01) {
                continue;
            }

            $days = $invoice->due_date ? max($today->diffInDays($invoice->due_date, false), 0) : 0;

            $bucket = $days <= 30 ? 'current' : ($days <= 60 ? '31_60' : ($days <= 90 ? '61_90' : '90_plus'));
            $buckets[$bucket]['total'] = round($buckets[$bucket]['total'] + $due, 2);
            $buckets[$bucket]['count']++;
        }

        return $buckets;
    }
}
