<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\AccountingSetting;
use App\Models\ChartOfAccount;
use App\Models\JournalEntry;
use App\Services\AccountingService;
use Illuminate\Support\Carbon;

class AccountingController extends Controller
{
    public function index(AccountingService $accounting)
    {
        $accounting->ensureChartOfAccounts();

        $cash = $accounting->accountBalance(ChartOfAccount::byCode('1010'));
        $bank = $accounting->accountBalance(ChartOfAccount::byCode('1020'));
        $mobile = $accounting->accountBalance(ChartOfAccount::byCode('1030'));
        $receivable = $accounting->accountBalance(ChartOfAccount::byCode('1100'));
        $payable = $accounting->accountBalance(ChartOfAccount::byCode('2010'));
        $inventory = $accounting->accountBalance(ChartOfAccount::byCode('1200'));

        $monthStart = Carbon::now()->startOfMonth();
        $incomeStatement = $accounting->incomeStatement($monthStart, Carbon::now());
        $balanceSheet = $accounting->balanceSheet(Carbon::now());

        $recentEntries = JournalEntry::with(['creator', 'lines.account'])
            ->latest('entry_date')
            ->latest('id')
            ->limit(10)
            ->get();

        $onboarding = $this->onboardingChecklist($accounting);

        return view('tenant.accounting.dashboard', compact(
            'cash', 'bank', 'mobile', 'receivable', 'payable', 'inventory',
            'incomeStatement', 'balanceSheet', 'recentEntries', 'onboarding'
        ));
    }

    protected function onboardingChecklist(AccountingService $accounting): array
    {
        $settings = AccountingSetting::current();
        $hasDefaultCash = (bool) $settings->default_cash_account_id;
        $hasDefaultBank = (bool) $settings->default_bank_account_id;
        $hasDefaultSales = (bool) $settings->default_sales_account_id;
        $hasOpening = JournalEntry::where('reference_type', 'opening')->exists();
        $hasFirstEntry = JournalEntry::whereNotNull('entry_date')->exists();

        return [
            [
                'label_key' => 'onb_settings',
                'done' => $hasDefaultCash && $hasDefaultBank && $hasDefaultSales,
                'route' => route('accounting.settings.index'),
            ],
            [
                'label_key' => 'onb_opening',
                'done' => $hasOpening,
                'route' => route('accounting.settings.index'),
            ],
            [
                'label_key' => 'onb_entry',
                'done' => $hasFirstEntry,
                'route' => route('accounting.journal.index'),
            ],
        ];
    }
}
