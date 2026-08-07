<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\ChartOfAccount;
use App\Models\PurchaseSetting;
use App\Models\Warehouse;
use Illuminate\Http\Request;

class PurchaseSettingController extends Controller
{
    public function index()
    {
        $settings = PurchaseSetting::current();
        $warehouses = Warehouse::active()->orderBy('name')->get();
        $accounts = ChartOfAccount::active()->orderBy('code')->get();
        $paymentAccounts = $settings->paymentAccounts();

        return view('tenant.purchase.settings', compact('settings', 'warehouses', 'accounts', 'paymentAccounts'));
    }

    public function update(Request $request)
    {
        $validated = $request->validate([
            'default_warehouse_id' => 'nullable|exists:warehouses,id',
            'purchase_account_id' => 'nullable|exists:chart_of_accounts,id',
            'payment_methods' => 'nullable|array',
            'payment_methods.*' => 'required|string',
            'default_payment_method' => 'nullable|string|max:50',
            'payment_term_days' => 'required|integer|min:0|max:3650',
            'default_tax_rate' => 'nullable|numeric|min:0|max:100',
            'auto_create_invoice_on_receipt' => 'nullable|in:1,0',
            'auto_post_purchases' => 'nullable|in:1,0',
            'update_cost_price_on_receipt' => 'nullable|in:1,0',
            'po_prefix' => 'nullable|string|max:10',
            'grn_prefix' => 'nullable|string|max:10',
            'inv_prefix' => 'nullable|string|max:10',
            'pay_prefix' => 'nullable|string|max:10',
            'rtn_prefix' => 'nullable|string|max:10',
        ]);

        PurchaseSetting::current()->update([
            'default_warehouse_id' => $validated['default_warehouse_id'] ?? null,
            'purchase_account_id' => $validated['purchase_account_id'] ?? null,
            'payment_methods' => $validated['payment_methods'] ?? [],
            'default_payment_method' => $validated['default_payment_method'] ?? null,
            'payment_term_days' => $validated['payment_term_days'],
            'default_tax_rate' => $validated['default_tax_rate'] ?? 0,
            'auto_create_invoice_on_receipt' => $request->boolean('auto_create_invoice_on_receipt'),
            'auto_post_purchases' => $request->boolean('auto_post_purchases'),
            'update_cost_price_on_receipt' => $request->boolean('update_cost_price_on_receipt'),
            'po_prefix' => $validated['po_prefix'] ?? 'PO',
            'grn_prefix' => $validated['grn_prefix'] ?? 'GRN',
            'inv_prefix' => $validated['inv_prefix'] ?? 'INV',
            'pay_prefix' => $validated['pay_prefix'] ?? 'PAY',
            'rtn_prefix' => $validated['rtn_prefix'] ?? 'RTN',
        ]);

        return redirect()->route('purchase.settings')->with('success', 'পারচেজ সেটিংস আপডেট হয়েছে।');
    }
}
