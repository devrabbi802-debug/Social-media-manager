<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\ChartOfAccount;
use App\Models\JournalEntry;
use App\Services\AccountingService;
use Illuminate\Http\Request;

class JournalEntryController extends Controller
{
    public function index(Request $request, AccountingService $accounting)
    {
        $accounting->ensureChartOfAccounts();

        $query = JournalEntry::with(['creator', 'lines.account']);

        if ($request->filled('from')) {
            $query->whereDate('entry_date', '>=', $request->from);
        }
        if ($request->filled('to')) {
            $query->whereDate('entry_date', '<=', $request->to);
        }
        if ($request->filled('reference_type')) {
            $query->where('reference_type', $request->reference_type);
        }
        if ($request->filled('search')) {
            $query->where('journal_number', 'like', "%{$request->search}%")
                ->orWhere('narration', 'like', "%{$request->search}%");
        }

        $entries = $query->latest('entry_date')->latest('id')->paginate(20)->withQueryString();

        $totalDebit = 0;
        $totalCredit = 0;
        foreach ($entries as $entry) {
            $entry->load('lines');
            $totalDebit += $entry->totalDebit();
            $totalCredit += $entry->totalCredit();
        }

        return view('tenant.accounting.journal.index', compact('entries', 'totalDebit', 'totalCredit'));
    }

    public function create(AccountingService $accounting)
    {
        $accounting->ensureChartOfAccounts();

        $accounts = ChartOfAccount::active()->orderBy('code')->get()->groupBy('account_type');

        return view('tenant.accounting.journal.create', compact('accounts'));
    }

    public function store(Request $request, AccountingService $accounting)
    {
        $validated = $request->validate([
            'entry_date' => 'required|date',
            'narration' => 'required|string|max:2000',
            'account_id' => 'required|array|min:2',
            'account_id.*' => 'required|exists:chart_of_accounts,id',
            'debit.*' => 'nullable|numeric|min:0',
            'credit.*' => 'nullable|numeric|min:0',
            'memo.*' => 'nullable|string|max:255',
        ]);

        $lines = [];

        foreach ($validated['account_id'] as $index => $accountId) {
            $debit = (float) ($validated['debit'][$index] ?? 0);
            $credit = (float) ($validated['credit'][$index] ?? 0);

            if ($debit > 0 || $credit > 0) {
                $lines[] = [
                    'account_id' => $accountId,
                    'debit' => $debit,
                    'credit' => $credit,
                    'memo' => $validated['memo'][$index] ?? null,
                ];
            }
        }

        try {
            $accounting->post($lines, $validated['narration'], 'manual', null, $validated['entry_date']);

            return redirect()->route('accounting.journal.index')
                ->with('success', 'Journal entry posted successfully.');
        } catch (\InvalidArgumentException $e) {
            return back()->withInput()->with('error', $e->getMessage());
        }
    }

    public function show(JournalEntry $entry)
    {
        $entry->load(['lines.account', 'creator', 'reverses', 'reversedBy']);

        return view('tenant.accounting.journal.show', compact('entry'));
    }

    public function reverse(JournalEntry $entry, AccountingService $accounting)
    {
        if ($entry->isReversed()) {
            return back()->with('error', 'This entry has already been reversed.');
        }

        try {
            $accounting->reverse($entry);

            return redirect()->route('accounting.journal.show', $entry)
                ->with('success', 'Entry reversed. A new reversing entry was created.');
        } catch (\InvalidArgumentException $e) {
            return back()->with('error', $e->getMessage());
        }
    }
}
