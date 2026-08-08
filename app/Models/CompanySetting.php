<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CompanySetting extends Model
{
    protected $fillable = [
        'business_name',
        'store_name',
        'store_phone',
        'store_email',
        'store_address',
        'logo_path',
        'default_warehouse_id',
        'currency',
        'currency_symbol',
        'tax_rate',
        'tax_type',
        'payment_methods',
        'default_payment_method',
    ];

    protected function casts(): array
    {
        return [
            'tax_rate' => 'decimal:2',
            'payment_methods' => 'array',
        ];
    }

    public static function current(): self
    {
        return static::firstOrCreate(
            ['id' => 1],
            [
                'business_name' => auth()->user()->company ?? 'My Store',
                'store_name' => auth()->user()->company ?? 'My Store',
                'currency' => 'BDT',
                'currency_symbol' => '৳',
                'tax_type' => 'inclusive',
            ]
        );
    }
}
