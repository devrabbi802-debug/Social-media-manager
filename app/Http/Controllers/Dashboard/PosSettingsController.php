<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\PosSetting;
use App\Models\Warehouse;
use Illuminate\Http\Request;

class PosSettingsController extends Controller
{
    public function index()
    {
        $settings = PosSetting::current();
        $warehouses = Warehouse::where('is_active', true)->orderBy('name')->get();

        return view('tenant.pos.settings', compact('settings', 'warehouses'));
    }

    public function update(Request $request)
    {
        $validated = $request->validate([
            'store_name' => 'nullable|string|max:255',
            'store_phone' => 'nullable|string|max:50',
            'store_email' => 'nullable|email|max:255',
            'store_address' => 'nullable|string|max:1000',
            'receipt_footer' => 'nullable|string|max:1000',
            'tax_rate' => 'required|numeric|min:0|max:100',
            'tax_type' => 'required|in:inclusive,exclusive',
            'currency' => 'required|string|max:20',
            'currency_symbol' => 'required|string|max:10',
            'payment_methods' => 'nullable|array',
            'payment_methods.*' => 'required|string',
            'default_payment_method' => 'required|string|max:50',
            'default_warehouse_id' => 'nullable|exists:warehouses,id',
            'auto_print_receipt' => 'boolean',
            'receipt_size' => 'required|in:80mm,58mm,a4',
        ]);

        $settings = PosSetting::current();
        $settings->update($validated);

        return back()->with('success', 'POS সেটিংস আপডেট হয়েছে!');
    }
}
