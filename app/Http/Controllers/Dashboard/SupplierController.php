<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\Supplier;
use Illuminate\Http\Request;

class SupplierController extends Controller
{
    public function index(Request $request)
    {
        $query = Supplier::withCount(['invoices' => fn ($q) => $q->where('status', '!=', 'cancelled')]);

        if ($request->filled('search')) {
            $query->search($request->search);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $suppliers = $query->orderBy('name')->paginate(20)->withQueryString();

        $stats = [
            'total' => Supplier::count(),
            'active' => Supplier::active()->count(),
            'outstanding' => round(
                (float) Supplier::query()->get()->sum(fn ($s) => $s->balance()),
                2
            ),
        ];

        return view('tenant.purchase.suppliers.index', compact('suppliers', 'stats'));
    }

    public function create()
    {
        return view('tenant.purchase.suppliers.form', ['supplier' => null]);
    }

    public function store(Request $request)
    {
        $validated = $this->validated($request);

        Supplier::create([...$validated, 'created_by' => auth()->id()]);

        return redirect()->route('purchase.suppliers.index')->with('success', 'সাপ্লায়ার সফলভাবে যোগ হয়েছে।');
    }

    public function show(Supplier $supplier)
    {
        $supplier->load(['invoices', 'payments', 'purchases', 'receipts', 'returns']);

        $invoices = $supplier->invoices()->with('purchaseOrder')->orderByDesc('id')->paginate(10);
        $payments = $supplier->payments()->orderByDesc('id')->paginate(10);

        return view('tenant.purchase.suppliers.show', compact('supplier', 'invoices', 'payments'));
    }

    public function edit(Supplier $supplier)
    {
        return view('tenant.purchase.suppliers.form', compact('supplier'));
    }

    public function update(Request $request, Supplier $supplier)
    {
        $validated = $this->validated($request, $supplier);

        $supplier->update($validated);

        return redirect()->route('purchase.suppliers.show', $supplier)->with('success', 'সাপ্লায়ার আপডেট হয়েছে।');
    }

    public function destroy(Supplier $supplier)
    {
        if ($supplier->invoices()->exists() || $supplier->receipts()->exists()) {
            return back()->with('error', 'এই সাপ্লায়ারের সাথে লেনদেন আছে, ডিলিট করা যাবে না।');
        }

        $supplier->delete();

        return redirect()->route('purchase.suppliers.index')->with('success', 'সাপ্লায়ার ডিলিট হয়েছে।');
    }

    private function validated(Request $request, ?Supplier $supplier = null): array
    {
        $rules = [
            'name' => 'required|string|max:255',
            'contact_person' => 'nullable|string|max:255',
            'phone' => 'nullable|string|max:50',
            'email' => 'nullable|email|max:255',
            'company' => 'nullable|string|max:255',
            'tax_number' => 'nullable|string|max:100',
            'address' => 'nullable|string|max:1000',
            'payment_terms' => 'nullable|string|max:255',
            'payment_term_days' => 'nullable|integer|min:0|max:3650',
            'opening_balance' => 'nullable|numeric',
            'notes' => 'nullable|string',
            'status' => 'nullable|in:active,inactive',
        ];

        return tap($request->validate($rules), function (&$data) {
            $data['opening_balance'] = (float) ($data['opening_balance'] ?? 0);
        });
    }
}
