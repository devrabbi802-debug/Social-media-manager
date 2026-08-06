<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AccountingSetting extends Model
{
    protected $fillable = [
        'currency_symbol',
        'currency',
        'fiscal_year_start_month',
        'post_pos_sales',
        'post_pos_refunds',
        'post_storefront_orders',
        'default_cash_account_id',
        'default_bank_account_id',
        'default_receivable_account_id',
        'default_inventory_account_id',
        'default_cogs_account_id',
        'default_sales_account_id',
        'default_discount_account_id',
        'default_tax_payable_account_id',
        'default_expense_account_id',
        'payment_account_map',
    ];

    protected function casts(): array
    {
        return [
            'fiscal_year_start_month' => 'integer',
            'post_pos_sales' => 'boolean',
            'post_pos_refunds' => 'boolean',
            'post_storefront_orders' => 'boolean',
            'payment_account_map' => 'array',
        ];
    }

    public static function current(): self
    {
        return static::query()->firstOrCreate(['id' => 1], [
            'currency_symbol' => '৳',
            'currency' => 'BDT',
            'fiscal_year_start_month' => 7,
        ]);
    }

    /**
     * Resolve the account mapped to a payment method.
     */
    public function paymentAccount(string $method): ?ChartOfAccount
    {
        $map = $this->payment_account_map ?? [];

        if (isset($map[$method])) {
            return ChartOfAccount::find($map[$method]);
        }

        $normalized = strtolower($method);

        if (in_array($normalized, ['cash', 'cod', 'cash_on_delivery'], true)) {
            return ChartOfAccount::find($this->default_cash_account_id);
        }

        if (in_array($normalized, ['bkash', 'nagad', 'rocket', 'upay', 'mobile', 'mobile_banking'], true)) {
            return ChartOfAccount::byCode('1030');
        }

        if (in_array($normalized, ['card', 'credit_card', 'debit_card', 'visa', 'mastercard', 'bank'], true)) {
            return ChartOfAccount::find($this->default_bank_account_id);
        }

        return ChartOfAccount::find($this->default_cash_account_id);
    }
}
