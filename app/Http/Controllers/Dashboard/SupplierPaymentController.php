<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\JournalEntry;
use App\Models\PurchaseInvoice;
use App\Models\PurchaseSetting;
use App\Models\Supplier;
use App\Models\SupplierPayment;
use App\Services\AccountingService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class SupplierPaymentController extends Controller
{
    public function index(Request $request)
    {
        $query = SupplierPayment::with(['supplier', 'invoice'])->where('status', '!=', 'cancelled');

        if ($request->filled('search')) {
            $query->where('payment_number', 'like', "%{$request->search}%")
                ->orWhereHas('supplier', fn ($q) => $q->where('name', 'like', "%{$request->search}%"));
        }

        if ($request->filled('supplier_id')) {
            $query->where('supplier_id', $request->supplier_id);
        }

        if ($request->filled('from')) {
            $query->whereDate('payment_date', '>=', $request->from);
        }

        if ($request->filled('to')) {
            $query->whereDate('payment_date', '<=', $request->to);
        }

        $payments = $query->latest()->paginate(20)->withQueryString();
        $suppliers = Supplier::active()->orderBy('name')->get();

        $total = round((float) (clone $query)->sum('amount'), 2);

        return view('tenant.purchase.payments.index', compact('payments', 'suppliers', 'total'));
    }

    public function create(Request $request)
    {
        $suppliers = Supplier::active()->orderBy('name')->get();
        $defaultInvoice = $request->filled('invoice_id') ? PurchaseInvoice::find($request->invoice_id) : null;
        $defaultSupplier = $request->filled('supplier_id')
            ? Supplier::find($request->supplier_id)
            : $defaultInvoice?->supplier;

        $settings = PurchaseSetting::current();
        $paymentAccounts = $settings->paymentAccounts()->filter(fn ($a) => $settings->isEnabled($a->code))->values();

        return view('tenant.purchase.payments.form', compact('suppliers', 'defaultInvoice', 'defaultSupplier', 'paymentAccounts'));
    }

    /**
     * JSON list of open (unpaid / partially paid) invoices for a supplier.
     */
    public function openInvoices(Request $request)
    {
        $invoices = PurchaseInvoice::with('supplier')
            ->where('status', '!=', 'cancelled')
            ->where('supplier_id', $request->integer('supplier_id'))
            ->get()
            ->filter(fn ($i) => $i->due() > 0.01)
            ->map(fn ($i) => [
                'id' => $i->id,
                'invoice_number' => $i->invoice_number,
                'due' => $i->due(),
            ])
            ->values();

        return response()->json(['data' => $invoices]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'supplier_id' => 'required|exists:suppliers,id',
            'purchase_invoice_id' => 'nullable|exists:purchase_invoices,id',
            'amount' => 'required|numeric|min:0.01',
            'method' => 'nullable|string|max:50',
            'reference' => 'nullable|string|max:255',
            'payment_date' => 'required|date',
            'notes' => 'nullable|string',
            'methods_json' => 'nullable|json',
        ]);

        $supplier = Supplier::findOrFail($validated['supplier_id']);
        $invoice = $validated['purchase_invoice_id'] ? PurchaseInvoice::findOrFail($validated['purchase_invoice_id']) : null;

        if ($invoice && $invoice->supplier_id !== $supplier->id) {
            return back()->with('error', 'ইনভয়েসটি নির্বাচিত সাপ্লায়ারের নয়।')->withInput();
        }

        $maxAmount = $invoice ? $invoice->due() : max($supplier->balance(), 0);

        if ($validated['amount'] > $maxAmount + 0.01) {
            return back()->with('error', 'পেমেন্টের পরিমাণ ব্যালেন্স/বাকি টাকার বেশি হতে পারবে না।')->withInput();
        }

        $methods = json_decode($request->input('methods_json', '[]'), true) ?: [];

        if (empty($methods) || ! is_array($methods)) {
            $methods = [['method' => $validated['method'] ?? 'cash', 'amount' => $validated['amount'], 'reference' => $validated['reference']]];
        }

        $methodTotal = round(array_sum(array_column($methods, 'amount')), 2);

        if (abs($methodTotal - (float) $validated['amount']) > 0.01) {
            return back()->with('error', 'পেমেন্ট মাধ্যমগুলোর যোগফল পরিমাণের সমান হতে হবে।')->withInput();
        }

        foreach ($methods as $m) {
            $validator = Validator::make($m, [
                'method' => 'required|string|max:50',
                'amount' => 'required|numeric|min:0',
                'reference' => 'nullable|string|max:255',
            ]);
            if ($validator->fails()) {
                return back()->with('error', 'অবৈধ পেমেন্ট তথ্য।')->withInput();
            }
        }

        try {
            DB::transaction(function () use ($validated, $methods, $invoice) {
                $payment = SupplierPayment::create([
                    ...$validated,
                    'method' => $methods[0]['method'] ?? $validated['method'] ?? 'cash',
                    'reference' => $methods[0]['reference'] ?? $validated['reference'] ?? null,
                    'status' => 'completed',
                    'created_by' => auth()->id(),
                    'paid_at' => now(),
                ]);

                foreach ($methods as $m) {
                    $payment->methods()->create([
                        'method' => $m['method'],
                        'amount' => $m['amount'],
                        'reference' => $m['reference'] ?? null,
                    ]);
                }

                if ($invoice) {
                    $invoice->update([
                        'paid_amount' => round((float) $invoice->paid_amount + (float) $validated['amount'], 2),
                        'status' => $invoice->isPaid() ? 'paid' : 'partially_paid',
                    ]);
                }

                if (PurchaseSetting::current()->auto_post_purchases) {
                    app(AccountingService::class)->postSupplierPayment($payment);
                }
            });
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage())->withInput();
        }

        return redirect()->route('purchase.payments.index')->with('success', 'সাপ্লায়ার পেমেন্ট সফলভাবে সম্পন্ন হয়েছে।');
    }

    public function destroy(SupplierPayment $payment)
    {
        if ($payment->status !== 'completed') {
            return back()->with('error', 'এই পেমেন্ট ইতিমধ্যে বাতিল।');
        }

        try {
            DB::transaction(function () use ($payment) {
                if ($payment->purchase_invoice_id) {
                    $invoice = $payment->invoice;
                    $invoice->update([
                        'paid_amount' => max(round((float) $invoice->paid_amount - (float) $payment->amount, 2), 0),
                        'status' => $invoice->paid_amount >= $invoice->total ? 'paid' : ($invoice->paid_amount > 0 ? 'partially_paid' : 'awaiting_payment'),
                    ]);
                }

                $payment->update([
                    'status' => 'cancelled',
                    'paid_at' => null,
                ]);

                if (PurchaseSetting::current()->auto_post_purchases) {
                    $entry = JournalEntry::ofReference('supplier_payment', $payment->id)
                        ->posted()->latest('id')->first();

                    if ($entry) {
                        app(AccountingService::class)->reverse($entry, "Payment #{$payment->payment_number} cancelled");
                    }
                }
            });
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }

        return back()->with('success', 'পেমেন্ট বাতিল করা হয়েছে।');
    }
}
