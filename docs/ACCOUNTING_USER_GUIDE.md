# Accounting Module — User Guide

A complete, non-accountant-friendly guide to the Accounting System in **Get ERP Store**. This module runs on the double-entry bookkeeping method but you do **not** need to know accounting to use it. Think of it as "every business transaction automatically recorded and summarized."

**Short version:** Sell at POS → money + inventory recorded. Customer orders online → receivable recorded. You pay a bill → expense recorded. Reports show how much money you have, owe, and made.

---

## 1. What is this module for?

It answers three everyday business questions:

| Question | Where to look |
|---|---|
| How much money do I have right now? | Accounting Dashboard |
| Who owes me money / who do I owe? | Dashboard → Receivable / Payable |
| Am I making a profit this month? | Reports → Income Statement |

It records automatically:

- **POS sales** and **refunds**
- **Storefront online orders** and **payments received**
- **Manual income** (e.g. a service you provided) and **expenses** (salary, rent, marketing)

---

## 2. First-time setup (5 minutes, do once)

The dashboard shows a **Getting Started** checklist until you finish. Follow these three steps:

### Step 1 — Set default accounts & auto-posting
Go to **Accounting → Settings**.

- **Default accounts:** each category (cash, bank, inventory, sales, expenses) points to an account. The defaults are already sensible, so you can leave them as-is.
- **Auto-posting:** three checkboxes that turn automatic recording ON/OFF:
  - `POS sales` — record every POS sale in the books
  - `POS refunds` — record refunds
  - `Orders` — record storefront orders
- Save.

### Step 2 — Enter opening balance
On the same Settings page, under **Opening balances**, enter the money / assets you already had when you started using this module. For example: cash on hand 50,000, bank 100,000.

- This is optional but recommended — without it your balance sheet starts from zero.
- Once saved it posts an entry to the ledger immediately.

### Step 3 — Record your first transaction
Use **+ Add Income** or **+ Add Expense** on the dashboard (or the Journal page for anything custom). See section 4.

When all three steps are done, the checklist disappears.

---

## 3. Everyday usage

### Record an expense (bill, salary, rent…)
1. Dashboard → **+ Add Expense**.
2. **Amount** — how much you paid.
3. **Expense category** — e.g. Salary, Rent (create new accounts under Chart of Accounts if needed).
4. **Payment method** — where the money went (Cash / bKash / Bank…).
5. Save. The expense is posted to the ledger.

### Record income (other than sales)
Same flow with **+ Add Income**. Choose an **Income category** and a **payment method**.

### POS / Storefront — nothing to do!
Sales at the POS terminal and online orders are posted automatically when the related settings toggle is on. You will see them appear in **Journal Entries** and in reports.

---

## 4. Journal Entries (the record book)

Every transaction becomes a **journal entry** — one row with debit and credit columns that always balance. You normally never create these by hand because POS/orders/income/expense do it for you. The Journal page is mainly for:

- **Viewing** every recorded transaction (filter by date, type, keyword).
- **Manual entries** for things auto-posting can't handle (e.g. transferring money between two banks, owner investment).

To create a manual entry:
1. **+ New Entry**.
2. Pick a date and short description.
3. Add rows: choose an **Account**, put the amount in **Debit** or **Credit**.
4. The green "Balanced" message tells you the entry is correct. Then **Post Entry**.

> Rule of thumb: every journal entry must have total Debit = total Credit. The form enforces this and blocks unbalanced entries.

---

## 5. Correcting mistakes

### Reverse an entry
Open any journal entry → **Reverse**. This creates an opposite entry that cancels the original in the books. The original stays visible (marked "Reversed") so you keep an audit trail.

### Refunds
- **POS refund** → recorded automatically as a reversal (if auto-posting is on). Stock is restored.
- **Order cancel/refund** → its accounting entries are reversed automatically.

---

## 6. Reports

| Report | What it shows |
|---|---|
| **Trial Balance** | All accounts with debit vs credit totals. If they match, the books are correct. |
| **Income Statement** | Income, expenses and net profit for a period. |
| **Balance Sheet** | What you own (assets), what you owe (liabilities), and owner's equity. |
| **Account Ledger** | All transactions of a single account with a running balance. |
| **All Transactions** | Every journal entry, line by line. |

Each report lets you pick a **date range** (or "as of" date) before viewing.

---

## 7. Chart of Accounts (your account list)

All accounts grouped into **Assets, Liabilities, Equity, Income, Expenses**.

- You can **add accounts** (e.g. a "bKash" or "Payroll" account) and **edit/rename** them.
- **System accounts** (the defaults) can't be deleted.
- An account with any transactions can't be deleted (to protect your records).

---

## 8. Payment method mapping

When a sale/payment happens, the module decides **which account** receives the money based on the payment method:

| Payment method | Default account |
|---|---|
| Cash, Cash on Delivery | Cash on hand |
| bKash, Nagad, Rocket, Upay | Mobile wallet |
| Bank, Card | Bank |

You can override this on **Settings → Payment method → Account** if you want, for example, bKash money to go to a specific bank account.

---

## 9. Dashboard — what you see

- **Top row:** Cash on hand, Bank, Mobile wallet, Receivable (customers' dues), Payable (our dues), Inventory value.
- **This month summary:** total income, total expense, profit/loss.
- **Overall position:** simplified balance sheet with a ✓ "Balanced" check.
- **Recent transactions:** latest 10 entries, click any to open.

---

## 10. FAQ

**Q: I don't know accounting. Can I still use this?**
Yes. Everything is in plain language (Bangla UI available). The system does the debit/credit work for you.

**Q: Why do I see a "difference" in the balance sheet?**
It should always be zero. If it isn't, you likely entered an unbalanced manual journal entry — but the form blocks those, so contact support if this appears.

**Q: Can I go back and delete an old expense?**
Don't delete — **reverse** it. That keeps your records accurate and auditable.

**Q: My POS sale didn't appear in the journal.**
Check Settings → auto-posting → `POS sales` is ON. Also confirm the sale wasn't made while that toggle was off.
