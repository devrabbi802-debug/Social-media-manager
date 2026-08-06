<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\ChartOfAccount;
use App\Models\JournalEntry;
use App\Services\AccountingService;
use Illuminate\Http\Request;

/**
 * Simple money in / money out for non-accountants.
 * Income → debit payment account, credit income account.
 * Expense → debit expense account, credit payment account.
 */
class MoneyController extends Controller
{
    public function index(Request $request, AccountingService $accounting)
    {
        $accounting->ensureChartOfAccounts();

        $query = JournalEntry::with(['creator', 'lines.account'])
            ->whereIn('reference_type', ['expense', 'income']);

        if ($request->filled('type') && in_array($request->type, ['income', 'expense'], true)) {
            $query->where('reference_type', $request->type);
        }
        if ($request->filled('from')) {
            $query->whereDate('entry_date', '>=', $request->from);
        }
        if ($request->filled('to')) {
            $query->whereDate('entry_date', '<=', $request->to);
        }

        $incomeTotal = (clone $query)->where('reference_type', 'income')->with('lines')
            ->get()->sum(fn ($e) => $e->totalDebit());
        $expenseTotal = (clone $query)->where('reference_type', 'expense')->with('lines')
            ->get()->sum(fn ($e) => $e->totalDebit());

        $entries = $query->latest('entry_date')->latest('id')->paginate(20)->withQueryString();

        foreach ($entries as $entry) {
            $entry->setAttribute('amount', $entry->totalDebit());
        }

        return view('tenant.accounting.money.index', compact('entries', 'incomeTotal', 'expenseTotal'));
    }

    public function create(Request $request, AccountingService $accounting)
    {
        $accounting->ensureChartOfAccounts();

        $type = $request->query('type') === 'income' ? 'income' : 'expense';
        $accountType = $type === 'income' ? 'income' : 'expense';

        $accounts = ChartOfAccount::active()->ofType($accountType)->orderBy('code')->get();

        return view('tenant.accounting.money.create', compact('type', 'accounts'));
    }

    public function store(Request $request, AccountingService $accounting)
    {
        $validated = $request->validate([
            'type' => 'required|in:income,expense',
            'amount' => 'required|numeric|min:0.01',
            'account_id' => 'required|exists:chart_of_accounts,id',
            'payment_method' => 'required|string|max:50',
            'narration' => 'nullable|string|max:2000',
            'entry_date' => 'required|date',
        ]);

        $entry = $validated['type'] === 'income'
            ? $accounting->postIncome(
                (float) $validated['amount'],
                (int) $validated['account_id'],
                $validated['payment_method'],
                $validated['narration'] ?? ($validated['type'] === 'income' ? 'Income' : 'Expense'),
                $validated['entry_date']
            )
            : $accounting->postExpense(
                (float) $validated['amount'],
                (int) $validated['account_id'],
                $validated['payment_method'],
                $validated['narration'] ?? 'Expense',
                $validated['entry_date']
            );

        return redirect()->route('accounting.money.index', ['type' => $validated['type']])
            ->with('success', $validated['type'] === 'income' ? 'Income recorded.' : 'Expense recorded.');
    }
}
