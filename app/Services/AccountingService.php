<?php

namespace App\Services;

use App\Models\AccountingSetting;
use App\Models\ChartOfAccount;
use App\Models\JournalEntry;
use App\Models\JournalEntryLine;
use App\Models\Order;
use App\Models\PosOrder;
use App\Models\PosRefund;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

/**
 * Double-entry accounting engine (tenant-scoped).
 *
 * All business flows (POS sales/refunds, storefront orders, expenses,
 * customer payments, opening balances) go through this service so the books
 * always stay balanced. Non-accountant friendly: callers only provide the
 * high-level figures and the service builds the balanced journal entry.
 */
class AccountingService
{
    /**
     * Default chart of accounts: code => [type, name, description, normal_balance, is_system].
     */
    public const DEFAULT_ACCOUNTS = [
        // Assets
        '1010' => ['asset', 'Cash on Hand', 'হাতে থাকা নগদ টাকা', 'debit', true],
        '1020' => ['asset', 'Bank Accounts', 'ব্যাংক অ্যাকাউন্টে থাকা টাকা', 'debit', true],
        '1030' => ['asset', 'Mobile Wallet (Bkash/Nagad/Rocket)', 'মোবাইল ওয়ালেটে থাকা টাকা', 'debit', true],
        '1100' => ['asset', 'Accounts Receivable', 'কাস্টমারের কাছে পাওনা (বাকি) টাকা', 'debit', true],
        '1200' => ['asset', 'Inventory', 'হাতে থাকা পণ্যের মূল্য (স্টক)', 'debit', true],
        '1300' => ['asset', 'Other Current Assets', 'অন্যান্য সম্পদ', 'debit', false],

        // Liabilities
        '2010' => ['liability', 'Accounts Payable', 'সাপ্লায়ার/আমাদের পাওনা (বাকি) টাকা', 'credit', true],
        '2200' => ['liability', 'Tax Payable (VAT)', 'সরকারকে দেওয়ার মতো ট্যাক্স', 'credit', true],

        // Equity
        '3010' => ['equity', 'Owner\'s Capital', 'মালিকের নিজের মূলধন', 'credit', true],
        '3100' => ['equity', 'Opening Balance Equity', 'শুরুর ব্যালেন্সের হিসাব', 'credit', true],

        // Income
        '4010' => ['income', 'Sales Revenue', 'পণ্য বিক্রির আয়', 'credit', true],
        '4020' => ['income', 'Other Income', 'অন্যান্য আয়', 'credit', false],

        // Expenses
        '5010' => ['expense', 'Cost of Goods Sold (COGS)', 'বিক্রিত পণ্যের ক্রয়মূল্য', 'debit', true],
        '5020' => ['expense', 'Discounts Given', 'কাস্টমারকে দেওয়া ডিসকাউন্ট', 'debit', true],
        '5030' => ['expense', 'Rent Expense', 'ভাড়া বাবদ খরচ', 'debit', false],
        '5040' => ['expense', 'Utilities (Electricity/Internet)', 'বিদ্যুৎ/ইন্টারনেট বিল', 'debit', false],
        '5050' => ['expense', 'Salaries & Wages', 'কর্মচারীদের বেতন', 'debit', false],
        '5060' => ['expense', 'Marketing & Ads', 'মার্কেটিং ও বিজ্ঞাপন খরচ', 'debit', false],
        '5070' => ['expense', 'Delivery & Transport', 'ডেলিভারি ও পরিবহন খরচ', 'debit', false],
        '5080' => ['expense', 'Office Supplies', 'অফিসের জিনিসপত্র', 'debit', false],
        '5090' => ['expense', 'Bank Charges', 'ব্যাংক চার্জ', 'debit', false],
        '5100' => ['expense', 'Miscellaneous Expense', 'অন্যান্য খরচ', 'debit', false],
    ];

    /**
     * Create the default chart of accounts when none exist.
     *
     * @return bool true when seeded
     */
    public function ensureChartOfAccounts(): bool
    {
        if (ChartOfAccount::exists()) {
            return false;
        }

        DB::transaction(function () {
            $settings = AccountingSetting::current();

            foreach (self::DEFAULT_ACCOUNTS as $code => [$type, $name, $desc, $balance, $system]) {
                ChartOfAccount::create([
                    'account_type' => $type,
                    'code' => $code,
                    'name' => $name,
                    'description' => $desc,
                    'normal_balance' => $balance,
                    'is_system' => $system,
                    'is_pos_payment' => in_array($code, ['1010', '1020', '1030'], true),
                    'is_active' => true,
                ]);
            }

            $byCode = fn ($code) => ChartOfAccount::byCode($code)?->id;

            $settings->update([
                'default_cash_account_id' => $byCode('1010'),
                'default_bank_account_id' => $byCode('1020'),
                'default_receivable_account_id' => $byCode('1100'),
                'default_inventory_account_id' => $byCode('1200'),
                'default_cogs_account_id' => $byCode('5010'),
                'default_sales_account_id' => $byCode('4010'),
                'default_discount_account_id' => $byCode('5020'),
                'default_tax_payable_account_id' => $byCode('2200'),
                'default_expense_account_id' => $byCode('5100'),
            ]);
        });

        return true;
    }

    /**
     * Resolve the cash/bank account for a payment method.
     */
    public function paymentAccount(string $method): ChartOfAccount
    {
        $method = trim($method);

        if ($method !== '') {
            $byCode = ChartOfAccount::byCode($method);
            if ($byCode) {
                return $byCode;
            }
        }

        $account = AccountingSetting::current()->paymentAccount($method);

        if (! $account) {
            $account = ChartOfAccount::byCode('1010');
        }

        return $account;
    }

    /**
     * Post a balanced journal entry.
     *
     * @param  array<int, array{account_id:int, debit:float, credit:float, memo?:string}>  $lines
     */
    public function post(
        array $lines,
        string $narration,
        ?string $referenceType = null,
        $referenceId = null,
        ?string $entryDate = null,
        ?int $createdBy = null
    ): JournalEntry {
        if (count($lines) < 2) {
            throw new \InvalidArgumentException('A journal entry needs at least two lines.');
        }

        $lines = collect($lines)
            ->map(fn ($line) => [
                'account_id' => (int) $line['account_id'],
                'debit' => round((float) ($line['debit'] ?? 0), 2),
                'credit' => round((float) ($line['credit'] ?? 0), 2),
                'memo' => $line['memo'] ?? null,
            ])
            ->filter(fn ($line) => $line['debit'] > 0 || $line['credit'] > 0)
            ->values();

        $totalDebit = round($lines->sum('debit'), 2);
        $totalCredit = round($lines->sum('credit'), 2);

        if ($totalDebit < 0.01 || abs($totalDebit - $totalCredit) > 0.01) {
            throw new \InvalidArgumentException('Journal entry is not balanced (debit must equal credit).');
        }

        $entry = JournalEntry::create([
            'entry_date' => $entryDate ?? now()->toDateString(),
            'reference_type' => $referenceType,
            'reference_id' => $referenceId,
            'narration' => $narration,
            'status' => 'posted',
            'created_by' => $createdBy ?? auth()->id(),
        ]);

        foreach ($lines as $line) {
            JournalEntryLine::create([
                'journal_entry_id' => $entry->id,
                'account_id' => $line['account_id'],
                'debit' => $line['debit'],
                'credit' => $line['credit'],
                'memo' => $line['memo'],
            ]);
        }

        return $entry;
    }

    /**
     * Post a full POS sale.
     */
    public function postPosSale(PosOrder $order, array $payments, float $costOfGoods): JournalEntry
    {
        $settings = AccountingSetting::current();
        $total = (float) $order->total;
        $paid = round(array_sum(array_column($payments, 'amount')), 2);
        $tax = (float) $order->tax_amount;
        $discount = (float) $order->discount_amount;

        $lines = [];

        foreach ($payments as $payment) {
            $lines[] = [
                'account_id' => $this->paymentAccount($payment['method'])->id,
                'debit' => (float) $payment['amount'],
                'credit' => 0,
                'memo' => 'Payment via '.$payment['method'],
            ];
        }

        if ($total - $paid > 0.01) {
            $lines[] = [
                'account_id' => $settings->default_receivable_account_id,
                'debit' => round($total - $paid, 2),
                'credit' => 0,
                'memo' => 'Remaining balance (credit)',
            ];
        }

        $lines[] = [
            'account_id' => $settings->default_sales_account_id,
            'debit' => 0,
            'credit' => round($total - $tax, 2),
        ];

        if ($tax > 0) {
            $lines[] = [
                'account_id' => $settings->default_tax_payable_account_id,
                'debit' => 0,
                'credit' => $tax,
            ];
        }

        if ($discount > 0) {
            $lines[] = [
                'account_id' => $settings->default_discount_account_id,
                'debit' => $discount,
                'credit' => 0,
            ];
        }

        if ($costOfGoods > 0) {
            $lines[] = ['account_id' => $settings->default_cogs_account_id, 'debit' => $costOfGoods, 'credit' => 0];
            $lines[] = ['account_id' => $settings->default_inventory_account_id, 'debit' => 0, 'credit' => $costOfGoods];
        }

        return $this->post($lines, "POS Sale #{$order->order_number}", 'pos', $order->id, $order->created_at?->toDateString());
    }

    /**
     * Post a POS refund (full reversal or partial sales-return).
     */
    public function postPosRefund(PosOrder $order, PosRefund $refund, float $returnedCost): JournalEntry
    {
        $settings = AccountingSetting::current();
        $amount = (float) $refund->amount;
        $isFull = abs($amount - (float) $order->total) < 0.01;

        if ($isFull) {
            $original = JournalEntry::ofReference('pos', $order->id)->posted()->latest('id')->first();

            if ($original) {
                return $this->reverse($original, "POS Refund #{$refund->refund_number} (full)", 'pos_refund', $refund->id);
            }
        }

        $paymentAccount = $this->paymentAccount($refund->method);

        $lines = [
            ['account_id' => $settings->default_sales_account_id, 'debit' => $amount, 'credit' => 0],
            ['account_id' => $paymentAccount->id, 'debit' => 0, 'credit' => $amount],
        ];

        if ($returnedCost > 0) {
            $lines[] = ['account_id' => $settings->default_inventory_account_id, 'debit' => $returnedCost, 'credit' => 0];
            $lines[] = ['account_id' => $settings->default_cogs_account_id, 'debit' => 0, 'credit' => $returnedCost];
        }

        return $this->post($lines, "POS Refund #{$refund->refund_number}", 'pos_refund', $refund->id);
    }

    /**
     * Post a storefront order (recognized as receivable when not yet paid).
     */
    public function postOrder(Order $order): JournalEntry
    {
        $settings = AccountingSetting::current();
        $total = (float) $order->total;

        $lines = [
            ['account_id' => $settings->default_receivable_account_id, 'debit' => $total, 'credit' => 0],
            ['account_id' => $settings->default_sales_account_id, 'debit' => 0, 'credit' => $total],
        ];

        return $this->post($lines, "Storefront Order #{$order->order_number}", 'order', $order->id, $order->created_at?->toDateString());
    }

    /**
     * Record a customer payment against an order (receivable → cash/bank).
     */
    public function receiveOrderPayment(Order $order, float $amount, string $method, ?string $reference = null): JournalEntry
    {
        $settings = AccountingSetting::current();
        $paymentAccount = $this->paymentAccount($method);

        return $this->post([
            ['account_id' => $paymentAccount->id, 'debit' => $amount, 'credit' => 0, 'memo' => 'Payment via '.$method],
            ['account_id' => $settings->default_receivable_account_id, 'debit' => 0, 'credit' => $amount, 'memo' => 'Order '.$order->order_number],
        ], "Payment received for Order #{$order->order_number}", 'order_payment', $order->id);
    }

    /**
     * Reverse every posted journal entry tied to an order (sale + payments).
     * Used when an order is cancelled or refunded.
     */
    public function reverseOrderEntries(Order $order): void
    {
        $entries = JournalEntry::posted()
            ->where(function ($q) use ($order) {
                $q->ofReference('order', $order->id)
                    ->orWhere(function ($q2) use ($order) {
                        $q2->ofReference('order_payment', $order->id);
                    });
            })
            ->get();

        foreach ($entries as $entry) {
            $this->reverse($entry, "Order #{$order->order_number} reversed");
        }
    }

    /**
     * Post a simple expense (debit expense, credit payment account).
     */
    public function postExpense(
        float $amount,
        int $expenseAccountId,
        string $paymentMethod,
        ?string $narration = null,
        ?string $entryDate = null,
        ?string $reference = null
    ): JournalEntry {
        $paymentAccount = $this->paymentAccount($paymentMethod);

        return $this->post([
            ['account_id' => $expenseAccountId, 'debit' => $amount, 'credit' => 0],
            ['account_id' => $paymentAccount->id, 'debit' => 0, 'credit' => $amount],
        ], $narration ?? 'Expense', 'expense', $reference, $entryDate);
    }

    /**
     * Post a generic income (debit payment account, credit income account).
     */
    public function postIncome(
        float $amount,
        int $incomeAccountId,
        string $paymentMethod,
        ?string $narration = null,
        ?string $entryDate = null
    ): JournalEntry {
        $paymentAccount = $this->paymentAccount($paymentMethod);

        return $this->post([
            ['account_id' => $paymentAccount->id, 'debit' => $amount, 'credit' => 0],
            ['account_id' => $incomeAccountId, 'debit' => 0, 'credit' => $amount],
        ], $narration ?? 'Income', 'income', null, $entryDate);
    }

    /**
     * Create a reversing entry for a journal entry.
     *
     * The original entry stays posted; the reversal carries the opposite
     * lines so the two net out in the ledger. Both remain visible in reports.
     */
    public function reverse(JournalEntry $entry, ?string $reason = null, ?string $referenceType = null, $referenceId = null): JournalEntry
    {
        if ($entry->reversed_by_id) {
            throw new \InvalidArgumentException('This entry has already been reversed.');
        }

        $lines = $entry->lines()->get()->map(function (JournalEntryLine $line) {
            return [
                'account_id' => $line->account_id,
                'debit' => (float) $line->credit,
                'credit' => (float) $line->debit,
                'memo' => $line->memo,
            ];
        })->all();

        $reversal = $this->post(
            $lines,
            $reason ?? 'Reversal of '.$entry->journal_number,
            $referenceType,
            $referenceId,
            now()->toDateString()
        );

        $entry->update(['reversed_by_id' => $reversal->id]);
        $reversal->update(['reverses_id' => $entry->id]);

        return $reversal;
    }

    /**
     * Post opening balances as a real journal entry so the books start balanced.
     * The net difference is parked in Opening Balance Equity (3100).
     */
    public function syncOpeningBalances(?string $date = null): ?JournalEntry
    {
        $this->ensureChartOfAccounts();

        // Reset any previous system opening entries (they are fully regenerated below)
        JournalEntry::ofReference('opening', 0)->get()
            ->each(fn ($e) => $e->lines()->delete());
        JournalEntry::ofReference('opening', 0)->delete();

        $accounts = ChartOfAccount::query()
            ->whereIn('account_type', ['asset', 'liability', 'equity'])
            ->where('opening_balance', '!=', 0)
            ->get();

        if ($accounts->isEmpty()) {
            return null;
        }

        $lines = [];
        $net = 0;

        foreach ($accounts as $account) {
            $amount = (float) $account->opening_balance;

            if ($account->normal_balance === 'debit') {
                $lines[] = ['account_id' => $account->id, 'debit' => $amount, 'credit' => 0];
                $net += $amount;
            } else {
                $lines[] = ['account_id' => $account->id, 'debit' => 0, 'credit' => $amount];
                $net -= $amount;
            }
        }

        $equity = ChartOfAccount::byCode('3100');

        if ($net < -0.01) {
            $lines[] = ['account_id' => $equity->id, 'debit' => abs($net), 'credit' => 0];
        } elseif ($net > 0.01) {
            $lines[] = ['account_id' => $equity->id, 'debit' => 0, 'credit' => $net];
        }

        return $this->post($lines, 'Opening balance setup', 'opening', 0, $date ?? now()->toDateString());
    }

    // ─────────────────────────────────────────────────────────────────────────
    // Balances & Reports
    // ─────────────────────────────────────────────────────────────────────────

    /**
     * Balance of a single account (respects normal balance + posted entries only).
     */
    public function accountBalance(ChartOfAccount $account, ?Carbon $asOf = null): float
    {
        $query = JournalEntryLine::query()
            ->where('account_id', $account->id)
            ->whereHas('entry', fn ($q) => $q->where('status', 'posted'));

        if ($asOf) {
            $query->whereHas('entry', fn ($q) => $q->whereDate('entry_date', '<=', $asOf));
        }

        $debit = (float) (clone $query)->sum('debit');
        $credit = (float) (clone $query)->sum('credit');

        return $account->normal_balance === 'debit' ? $debit - $credit : $credit - $debit;
    }

    /**
     * Account balances (optional type filter and as-of date).
     *
     * @return array<int, array{account: ChartOfAccount, balance: float}>
     */
    public function balances(?Carbon $asOf = null, ?array $types = null): array
    {
        $accounts = ChartOfAccount::active()->orderBy('code')->get();

        if ($types) {
            $accounts = $accounts->filter(fn ($a) => in_array($a->account_type, $types, true));
        }

        return $accounts->map(fn (ChartOfAccount $account) => [
            'account' => $account,
            'balance' => $this->accountBalance($account, $asOf),
        ])->values()->all();
    }

    public function trialBalance(?Carbon $asOf = null): array
    {
        $rows = $this->balances($asOf);

        $debitTotal = 0;
        $creditTotal = 0;

        foreach ($rows as &$row) {
            $balance = $row['balance'];
            $row['debit'] = $balance > 0 && $row['account']->normal_balance === 'debit' ? $balance : 0;
            $row['credit'] = $balance > 0 && $row['account']->normal_balance === 'credit' ? $balance : 0;
            $debitTotal += $row['debit'];
            $creditTotal += $row['credit'];
        }

        return ['rows' => $rows, 'debit_total' => $debitTotal, 'credit_total' => $creditTotal];
    }

    public function incomeStatement(Carbon $from, Carbon $to): array
    {
        $income = $this->balances($to, ['income']);
        $expenses = $this->balances($to, ['expense']);

        $incomeRows = [];
        $expenseRows = [];

        $totalIncome = 0;
        $totalExpense = 0;

        foreach ($income as $row) {
            $balance = $this->accountBalance($row['account'], $from->copy()->subDay());
            $periodBalance = round($this->accountBalance($row['account'], $to) - $balance, 2);

            $incomeRows[] = ['account' => $row['account'], 'balance' => $periodBalance];
            $totalIncome += $periodBalance;
        }

        foreach ($expenses as $row) {
            $balance = $this->accountBalance($row['account'], $from->copy()->subDay());
            $periodBalance = round($this->accountBalance($row['account'], $to) - $balance, 2);

            $expenseRows[] = ['account' => $row['account'], 'balance' => $periodBalance];
            $totalExpense += $periodBalance;
        }

        return [
            'income' => $incomeRows,
            'expenses' => $expenseRows,
            'total_income' => $totalIncome,
            'total_expense' => $totalExpense,
            'net_profit' => round($totalIncome - $totalExpense, 2),
        ];
    }

    public function balanceSheet(?Carbon $asOf = null): array
    {
        $assets = $this->balances($asOf, ['asset']);
        $liabilities = $this->balances($asOf, ['liability']);
        $equity = $this->balances($asOf, ['equity']);

        $totalAssets = array_sum(array_column($assets, 'balance'));
        $totalLiabilities = array_sum(array_column($liabilities, 'balance'));
        $totalEquity = array_sum(array_column($equity, 'balance'));

        $netProfit = $this->netProfit($asOf);
        $totalLiabilitiesEquity = $totalLiabilities + $totalEquity + $netProfit;

        return [
            'assets' => $assets,
            'liabilities' => $liabilities,
            'equity' => $equity,
            'total_assets' => $totalAssets,
            'total_liabilities' => $totalLiabilities,
            'total_equity' => $totalEquity,
            'net_profit' => $netProfit,
            'total_liabilities_equity' => $totalLiabilitiesEquity,
            'difference' => round($totalAssets - $totalLiabilitiesEquity, 2),
        ];
    }

    public function netProfit(?Carbon $asOf = null): float
    {
        $from = $this->fiscalYearStart($asOf);

        $income = array_sum(array_map(
            fn ($row) => $this->accountBalance($row['account'], $asOf),
            $this->balances($asOf, ['income'])
        ));
        $expenses = array_sum(array_map(
            fn ($row) => $this->accountBalance($row['account'], $asOf),
            $this->balances($asOf, ['expense'])
        ));

        return round($income - $expenses, 2);
    }

    /**
     * Ledger for a single account with running balance.
     *
     * @return array{rows: array, opening: float, closing: float}
     */
    public function ledger(ChartOfAccount $account, ?Carbon $from = null, ?Carbon $to = null): array
    {
        $query = JournalEntryLine::query()
            ->where('account_id', $account->id)
            ->whereHas('entry', fn ($q) => $q->where('status', 'posted'))
            ->with(['entry', 'account'])
            ->orderByDesc('entry.entry_date')
            ->orderByDesc('id');

        $asOf = $to ?? now();
        $opening = 0;

        if ($from) {
            $opening = $this->accountBalance($account, $from->copy()->subDay());
        }

        $lines = $query->get()->filter(function (JournalEntryLine $line) use ($from, $to) {
            $date = $line->entry->entry_date;

            return (! $from || $date >= $from) && (! $to || $date <= $to);
        })->values();

        $running = $opening;
        $rows = [];

        foreach ($lines as $line) {
            $debit = (float) $line->debit;
            $credit = (float) $line->credit;
            $running = round($running + ($account->normal_balance === 'debit' ? $debit - $credit : $credit - $debit), 2);

            $rows[] = [
                'line' => $line,
                'debit' => $debit,
                'credit' => $credit,
                'balance' => $running,
            ];
        }

        return [
            'rows' => array_reverse($rows),
            'opening' => $opening,
            'closing' => round($this->accountBalance($account, $to ?? null), 2),
        ];
    }

    public function fiscalYearStart(?Carbon $asOf = null): Carbon
    {
        $month = AccountingSetting::current()->fiscal_year_start_month ?: 7;
        $date = $asOf ?? now();

        $startYear = $date->month >= $month ? $date->year : $date->year - 1;

        return Carbon::create($startYear, $month, 1)->startOfDay();
    }

    public function fiscalYearEnd(?Carbon $asOf = null): Carbon
    {
        return $this->fiscalYearStart($asOf)->addYear()->subDay()->endOfDay();
    }
}
