<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

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
            'payment_methods' => ['cash', 'card', 'mobile'],
            'default_payment_method' => 'cash',
            'receipt_size' => '80mm',
        ]);
    }

    public function methods(): array
    {
        return $this->payment_methods ?? ['cash', 'card', 'mobile'];
    }
}
