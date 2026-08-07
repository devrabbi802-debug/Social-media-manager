<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PosSession extends Model
{
    protected $fillable = [
        'user_id',
        'warehouse_id',
        'opened_at',
        'closed_at',
        'opening_cash',
        'closing_cash',
        'expected_cash',
        'cash_difference',
        'total_sales',
        'total_tax',
        'total_discount',
        'cash_sales',
        'card_sales',
        'mobile_sales',
        'other_sales',
        'refunds_total',
        'sales_count',
        'status',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'opened_at' => 'datetime',
            'closed_at' => 'datetime',
            'opening_cash' => 'decimal:2',
            'closing_cash' => 'decimal:2',
            'expected_cash' => 'decimal:2',
            'cash_difference' => 'decimal:2',
            'total_sales' => 'decimal:2',
            'total_tax' => 'decimal:2',
            'total_discount' => 'decimal:2',
            'cash_sales' => 'decimal:2',
            'card_sales' => 'decimal:2',
            'mobile_sales' => 'decimal:2',
            'other_sales' => 'decimal:2',
            'refunds_total' => 'decimal:2',
            'sales_count' => 'integer',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function warehouse(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class);
    }

    public function orders(): HasMany
    {
        return $this->hasMany(PosOrder::class);
    }

    public function cashEvents(): HasMany
    {
        return $this->hasMany(PosCashEvent::class);
    }

    public function scopeOpen($query)
    {
        return $query->where('status', 'open');
    }
}
