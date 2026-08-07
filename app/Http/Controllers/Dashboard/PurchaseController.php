<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\PurchaseInvoice;
use App\Models\PurchaseOrder;
use App\Models\PurchaseReceipt;
use App\Models\Supplier;
use Illuminate\Http\Request;

class PurchaseController extends Controller
{
    public function index()
    {
        $stats = [
            'suppliers' => Supplier::active()->count(),
            'orders' => PurchaseOrder::whereIn('status', ['ordered', 'partially_received'])->count(),
            'pending_receipts' => PurchaseOrder::whereIn('status', ['ordered', 'partially_received'])
                ->with('items')->get()->sum(fn ($po) => $po->items->sum(fn ($i) => $i->remainingQuantity())),
            'outstanding' => round((float) PurchaseInvoice::where('status', '!=', 'cancelled')
                ->get()->sum(fn ($i) => $i->due()), 2),
        ];

        $recentOrders = PurchaseOrder::with('supplier')->latest()->take(5)->get();
        $recentReceipts = PurchaseReceipt::with('supplier')->latest()->take(5)->get();
        $recentInvoices = PurchaseInvoice::with('supplier')->latest()->take(5)->get();
        $overdueInvoices = PurchaseInvoice::with('supplier')
            ->where('status', '!=', 'cancelled')
            ->get()
            ->filter(fn ($i) => $i->isOverdue())
            ->take(5)
            ->values();

        return view('tenant.purchase.dashboard', compact('stats', 'recentOrders', 'recentReceipts', 'recentInvoices', 'overdueInvoices'));
    }

    /**
     * JSON product search for the purchase item picker.
     */
    public function products(Request $request)
    {
        $search = $request->query('search');

        $query = Product::with([
            'category',
            'variants' => fn ($q) => $q->active(),
        ])->active();

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('sku', 'like', "%{$search}%")
                    ->orWhere('barcode', 'like', "%{$search}%");
            });
        }

        $products = $query->orderBy('name')->limit(30)->get();

        $baseUrl = request()->getSchemeAndHttpHost();

        $data = $products->map(function (Product $product) use ($baseUrl) {
            $image = $product->primaryImage;

            return [
                'id' => $product->id,
                'name' => $product->name,
                'sku' => $product->sku,
                'barcode' => $product->barcode,
                'cost' => (float) ($product->cost_price ?? 0),
                'stock' => $product->total_stock,
                'unit' => $product->unit,
                'image' => $image ? $baseUrl.'/storage/'.$image->image_path : null,
                'has_variants' => $product->variants->count() > 0,
                'variants' => $product->variants->map(function (ProductVariant $v) {
                    return [
                        'id' => $v->id,
                        'name' => $v->display,
                        'sku' => $v->sku,
                        'barcode' => $v->barcode,
                        'cost' => (float) ($v->cost_price ?? 0),
                        'stock' => (int) $v->stock_quantity,
                    ];
                })->values(),
            ];
        });

        return response()->json(['data' => $data]);
    }

    /**
     * JSON supplier search for the combobox.
     */
    public function suppliers(Request $request)
    {
        $search = $request->query('search');

        $query = Supplier::active();

        if ($search) {
            $query->search($search);
        }

        $suppliers = $query->orderBy('name')->limit(20)->get();

        return response()->json([
            'data' => $suppliers->map(fn (Supplier $s) => [
                'id' => $s->id,
                'name' => $s->name,
                'phone' => $s->phone,
                'company' => $s->company,
                'payment_term_days' => $s->payment_term_days,
                'balance' => round($s->balance(), 2),
            ]),
        ]);
    }
}
