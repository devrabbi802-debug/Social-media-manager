<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

class PosSetting extends Model
{
    protected $fillable = [
        'store_name',
        'store_phone',
        'store_email',
        'store_address',
        'receipt_footer',
        'tax_rate',
        'tax_type',
        'currency',
        'currency_symbol',
        'payment_methods',
        'default_payment_method',
        'default_warehouse_id',
        'auto_print_receipt',
        'receipt_size',
    ];

    protected $casts = [
        'tax_rate' => 'decimal:2',
        'payment_methods' => 'array',
        'auto_print_receipt' => 'boolean',
    ];

    public static function current(): self
    {
        return static::query()->firstOrCreate(['id' => 1], [
            'store_name' => auth()->user()->company ?? 'My Store',
            'tax_type' => 'inclusive',
            'currency' => 'BDT',
            'currency_symbol' => '৳',
            'payment_methods' => ['1010', '1020', '1030'],
            'default_payment_method' => '1010',
            'receipt_size' => '80mm',
        ]);
    }

    /**
     * Accounts that can be used as POS payment methods.
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
     * Enabled payment method COA codes.
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
