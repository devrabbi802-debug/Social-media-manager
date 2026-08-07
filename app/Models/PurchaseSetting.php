<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

class PurchaseSetting extends Model
{
    protected $fillable = [
        'default_warehouse_id',
        'purchase_account_id',
        'payment_methods',
        'default_payment_method',
        'payment_term_days',
        'default_tax_rate',
        'auto_create_invoice_on_receipt',
        'auto_post_purchases',
        'update_cost_price_on_receipt',
        'po_prefix',
        'grn_prefix',
        'inv_prefix',
        'pay_prefix',
        'rtn_prefix',
    ];

    protected $casts = [
        'payment_term_days' => 'integer',
        'default_tax_rate' => 'decimal:2',
        'payment_methods' => 'array',
        'auto_create_invoice_on_receipt' => 'boolean',
        'auto_post_purchases' => 'boolean',
        'update_cost_price_on_receipt' => 'boolean',
    ];

    public static function current(): self
    {
        return static::query()->firstOrCreate(['id' => 1], [
            'payment_term_days' => 30,
            'auto_create_invoice_on_receipt' => true,
            'update_cost_price_on_receipt' => true,
            'payment_methods' => ['1010', '1020', '1030'],
            'default_payment_method' => '1010',
        ]);
    }

    /**
     * Account debited when a purchase invoice is posted (Inventory by default).
     */
    public function purchaseAccount(): ?ChartOfAccount
    {
        if ($this->purchase_account_id) {
            return ChartOfAccount::find($this->purchase_account_id);
        }

        return ChartOfAccount::byCode('1200');
    }

    /**
     * Accounts that can be used as supplier payment methods.
     *
     * @return Collection<int, ChartOfAccount>
     */
    public function paymentAccounts(): Collection
    {
        $accounts = ChartOfAccount::active()->posPayment()->orderBy('code')->get();

        if ($accounts->isEmpty()) {
            $accounts = ChartOfAccount::active()->ofType('asset')->orderBy('code')->get()
                ->filter(fn ($a) => in_array($a->code, ['1010', '1020', '1030'], true))->values();
        }

        return $accounts;
    }

    /**
     * Enabled supplier payment method COA codes.
     *
     * @return array<int, string>
     */
    public function methods(): array
    {
        $enabled = $this->payment_methods ?? [];

        if (! is_array($enabled) || empty($enabled)) {
            return ['1010', '1020', '1030'];
        }

        return array_values(array_unique($enabled));
    }

    public function defaultMethod(): string
    {
        return $this->default_payment_method ?: ($this->methods()[0] ?? '1010');
    }

    public function isEnabled(string $code): bool
    {
        return in_array($code, $this->methods(), true);
    }
}
