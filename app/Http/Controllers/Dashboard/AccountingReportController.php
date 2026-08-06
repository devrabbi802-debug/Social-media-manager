<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\ChartOfAccount;
use App\Models\JournalEntry;
use App\Services\AccountingService;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class AccountingReportController extends Controller
{
    public function trialBalance(Request $request, AccountingService $accounting)
    {
        $accounting->ensureChartOfAccounts();

        $asOf = $request->filled('as_of') ? Carbon::parse($request->as_of)->endOfDay() : Carbon::now();
        $data = $accounting->trialBalance($asOf);

        return view('tenant.accounting.reports.trial-balance', compact('data', 'asOf'));
    }

    public function incomeStatement(Request $request, AccountingService $accounting)
    {
        $accounting->ensureChartOfAccounts();

        $fiscalStart = $accounting->fiscalYearStart();
        $from = $request->filled('from') ? Carbon::parse($request->from) : $fiscalStart;
        $to = $request->filled('to') ? Carbon::parse($request->to) : Carbon::now();

        $data = $accounting->incomeStatement($from, $to);

        return view('tenant.accounting.reports.income-statement', compact('data', 'from', 'to'));
    }

    public function balanceSheet(Request $request, AccountingService $accounting)
    {
        $accounting->ensureChartOfAccounts();

        $asOf = $request->filled('as_of') ? Carbon::parse($request->as_of)->endOfDay() : Carbon::now();
        $data = $accounting->balanceSheet($asOf);

        return view('tenant.accounting.reports.balance-sheet', compact('data', 'asOf'));
    }

    public function ledger(Request $request, AccountingService $accounting)
    {
        $accounting->ensureChartOfAccounts();

        $accounts = ChartOfAccount::active()->orderBy('code')->get();
        $data = null;
        $account = null;

        if ($request->filled('account_id')) {
            $account = ChartOfAccount::find($request->account_id);
            $from = $request->filled('from') ? Carbon::parse($request->from) : null;
            $to = $request->filled('to') ? Carbon::parse($request->to)->endOfDay() : null;
            $data = $accounting->ledger($account, $from, $to);
        }

        return view('tenant.accounting.reports.ledger', compact('accounts', 'account', 'data'));
    }

    public function transactions(Request $request, AccountingService $accounting)
    {
        $accounting->ensureChartOfAccounts();

        $query = JournalEntry::with(['lines.account', 'creator'])->posted();

        if ($request->filled('from')) {
            $query->whereDate('entry_date', '>=', $request->from);
        }
        if ($request->filled('to')) {
            $query->whereDate('entry_date', '<=', $request->to);
        }
        if ($request->filled('reference_type')) {
            $query->where('reference_type', $request->reference_type);
        }
        if ($request->filled('account_id')) {
            $query->whereHas('lines', fn ($q) => $q->where('account_id', $request->account_id));
        }

        $entries = $query->latest('entry_date')->latest('id')->paginate(30)->withQueryString();

        foreach ($entries as $entry) {
            $entry->setAttribute('debit_total', $entry->totalDebit());
            $entry->setAttribute('credit_total', $entry->totalCredit());
        }

        $accounts = ChartOfAccount::active()->orderBy('code')->get();

        return view('tenant.accounting.reports.transactions', compact('entries', 'accounts'));
    }
}
