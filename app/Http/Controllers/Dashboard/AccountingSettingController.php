<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\AccountingSetting;
use App\Models\ChartOfAccount;
use App\Services\AccountingService;
use Illuminate\Http\Request;

class AccountingSettingController extends Controller
{
    public function index(AccountingService $accounting)
    {
        $accounting->ensureChartOfAccounts();

        $settings = AccountingSetting::current();
        $accounts = ChartOfAccount::active()->orderBy('code')->get();

        $paymentMap = $settings->payment_account_map ?? [];
        $cashAccounts = $accounts->whereIn('account_type', ['asset'])->values();

        return view('tenant.accounting.settings', compact('settings', 'accounts', 'paymentMap', 'cashAccounts'));
    }

    public function update(Request $request, AccountingService $accounting)
    {
        $validated = $request->validate([
            'currency_symbol' => 'required|string|max:10',
            'currency' => 'required|string|max:10',
            'fiscal_year_start_month' => 'required|integer|min:1|max:12',
            'post_pos_sales' => 'sometimes|boolean',
            'post_pos_refunds' => 'sometimes|boolean',
            'post_storefront_orders' => 'sometimes|boolean',
            'default_cash_account_id' => 'nullable|exists:chart_of_accounts,id',
            'default_bank_account_id' => 'nullable|exists:chart_of_accounts,id',
            'default_receivable_account_id' => 'nullable|exists:chart_of_accounts,id',
            'default_inventory_account_id' => 'nullable|exists:chart_of_accounts,id',
            'default_cogs_account_id' => 'nullable|exists:chart_of_accounts,id',
            'default_sales_account_id' => 'nullable|exists:chart_of_accounts,id',
            'default_discount_account_id' => 'nullable|exists:chart_of_accounts,id',
            'default_tax_payable_account_id' => 'nullable|exists:chart_of_accounts,id',
            'default_expense_account_id' => 'nullable|exists:chart_of_accounts,id',
            'payment_map' => 'nullable|array',
            'payment_map.*' => 'nullable|exists:chart_of_accounts,id',
            'opening_balance' => 'nullable|array',
            'opening_balance.*' => 'nullable|numeric|min:0',
        ]);

        $settings = AccountingSetting::current();

        $settings->update([
            'currency_symbol' => $validated['currency_symbol'],
            'currency' => $validated['currency'],
            'fiscal_year_start_month' => $validated['fiscal_year_start_month'],
            'post_pos_sales' => $request->boolean('post_pos_sales'),
            'post_pos_refunds' => $request->boolean('post_pos_refunds'),
            'post_storefront_orders' => $request->boolean('post_storefront_orders'),
            'default_cash_account_id' => $validated['default_cash_account_id'] ?? $settings->default_cash_account_id,
            'default_bank_account_id' => $validated['default_bank_account_id'] ?? $settings->default_bank_account_id,
            'default_receivable_account_id' => $validated['default_receivable_account_id'] ?? $settings->default_receivable_account_id,
            'default_inventory_account_id' => $validated['default_inventory_account_id'] ?? $settings->default_inventory_account_id,
            'default_cogs_account_id' => $validated['default_cogs_account_id'] ?? $settings->default_cogs_account_id,
            'default_sales_account_id' => $validated['default_sales_account_id'] ?? $settings->default_sales_account_id,
            'default_discount_account_id' => $validated['default_discount_account_id'] ?? $settings->default_discount_account_id,
            'default_tax_payable_account_id' => $validated['default_tax_payable_account_id'] ?? $settings->default_tax_payable_account_id,
            'default_expense_account_id' => $validated['default_expense_account_id'] ?? $settings->default_expense_account_id,
            'payment_account_map' => collect($validated['payment_map'] ?? [])
                ->filter(fn ($v) => ! empty($v))
                ->map(fn ($v) => (int) $v)
                ->all(),
        ]);

        // Opening balances → balanced opening entry
        if (! empty($validated['opening_balance'])) {
            $changes = 0;

            foreach ($validated['opening_balance'] as $accountId => $amount) {
                $account = ChartOfAccount::find($accountId);
                if (! $account || ! in_array($account->account_type, ['asset', 'liability', 'equity'], true)) {
                    continue;
                }

                $new = (float) $amount;
                if (abs($new - (float) $account->opening_balance) > 0.005) {
                    $account->update(['opening_balance' => $new]);
                    $changes++;
                }
            }

            if ($changes > 0) {
                try {
                    $accounting->syncOpeningBalances();
                } catch (\InvalidArgumentException $e) {
                    return back()->with('error', $e->getMessage());
                }
            }
        }

        return back()->with('success', 'Accounting settings saved.');
    }
}
