<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\ChartOfAccount;
use App\Services\AccountingService;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ChartOfAccountController extends Controller
{
    public function index(AccountingService $accounting)
    {
        $accounting->ensureChartOfAccounts();

        $accounts = ChartOfAccount::withCount('lines')->orderBy('code')->get();

        $groups = [];
        foreach (ChartOfAccount::TYPES as $key => $label) {
            $groups[$key] = [
                'label' => $label,
                'total' => 0,
                'accounts' => $accounts->where('account_type', $key)->values(),
            ];
        }

        foreach ($groups as &$group) {
            foreach ($group['accounts'] as $account) {
                $account->setAttribute('balance', $accounting->accountBalance($account));
            }
            $group['total'] = collect($group['accounts'])
                ->whereNull('parent_id')
                ->sum(fn ($a) => $a->balance);
        }

        return view('tenant.accounting.chart-of-accounts', compact('groups', 'accounts'));
    }

    public function create()
    {
        $types = ChartOfAccount::TYPES;
        $parents = ChartOfAccount::active()->orderBy('code')->get();

        return view('tenant.accounting.account-form', compact('types', 'parents'))->with('account', null);
    }

    public function store(Request $request)
    {
        $data = $this->validated($request);
        $data['is_pos_payment'] = $request->boolean('is_pos_payment');

        ChartOfAccount::create($data + ['created_by' => auth()->id()]);

        return redirect()->route('accounting.chart-of-accounts.index')
            ->with('success', 'Account created successfully.');
    }

    public function edit(ChartOfAccount $account)
    {
        $types = ChartOfAccount::TYPES;
        $parents = ChartOfAccount::active()->where('id', '!=', $account->id)->orderBy('code')->get();

        return view('tenant.accounting.account-form', compact('account', 'types', 'parents'));
    }

    public function update(Request $request, ChartOfAccount $account)
    {
        $data = $this->validated($request, $account);

        if ($account->is_system) {
            $data['code'] = $account->code;
            $data['account_type'] = $account->account_type;
            $data['normal_balance'] = $account->normal_balance;
        }

        $data['is_pos_payment'] = $request->boolean('is_pos_payment');

        $account->update($data);

        return redirect()->route('accounting.chart-of-accounts.index')
            ->with('success', 'Account updated successfully.');
    }

    public function destroy(ChartOfAccount $account)
    {
        if ($account->is_system) {
            return back()->with('error', 'System accounts cannot be deleted.');
        }

        if ($account->lines()->exists() || $account->children()->exists()) {
            return back()->with('error', 'This account has transactions and cannot be deleted.');
        }

        $account->delete();

        return back()->with('success', 'Account deleted.');
    }

    private function validated(Request $request, ?ChartOfAccount $account = null): array
    {
        return $request->validate([
            'account_type' => 'required|in:asset,liability,equity,income,expense',
            'code' => [
                'required', 'string', 'max:20',
                Rule::unique('chart_of_accounts', 'code')->ignore($account?->id),
            ],
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
            'normal_balance' => 'required|in:debit,credit',
            'parent_id' => 'nullable|exists:chart_of_accounts,id',
            'is_pos_payment' => 'boolean',
        ]);
    }
}
