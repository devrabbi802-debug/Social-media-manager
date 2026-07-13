<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\Warehouse;
use Illuminate\Http\Request;

class WarehouseController extends Controller
{
    public function index(Request $request)
    {
        $query = Warehouse::withCount('stockMovements');

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where('name', 'like', "%{$search}%");
        }

        $warehouses = $query->orderBy('name')->paginate(20);

        return view('tenant.warehouses.index', compact('warehouses'));
    }

    public function create()
    {
        return view('tenant.warehouses.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'address' => 'nullable|string',
            'phone' => 'nullable|string|max:50',
            'is_active' => 'boolean',
        ]);

        $validated['is_active'] = $request->boolean('is_active');

        Warehouse::create($validated);

        return redirect()->route('inventory.warehouses.index')
            ->with('success', 'গুদম/ওয়ারহাউস সফলভাবে তৈরি হয়েছে!');
    }

    public function edit(Warehouse $warehouse)
    {
        return view('tenant.warehouses.edit', compact('warehouse'));
    }

    public function update(Request $request, Warehouse $warehouse)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'address' => 'nullable|string',
            'phone' => 'nullable|string|max:50',
            'is_active' => 'boolean',
        ]);

        $validated['is_active'] = $request->boolean('is_active');

        $warehouse->update($validated);

        return redirect()->route('inventory.warehouses.index')
            ->with('success', 'গুদম/ওয়ারহাউস আপডেট হয়েছে!');
    }

    public function destroy(Warehouse $warehouse)
    {
        if ($warehouse->stockMovements()->count() > 0) {
            return back()->with('error', 'এই গুদমে স্টক মুভমেন্ট আছে, তাই ডিলিট করা যাবে না!');
        }

        $warehouse->delete();

        return redirect()->route('inventory.warehouses.index')
            ->with('success', 'গুদম/ওয়ারহাউস ডিলিট হয়েছে!');
    }
}
