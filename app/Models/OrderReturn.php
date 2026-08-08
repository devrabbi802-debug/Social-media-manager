<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class OrderReturn extends Model
{
    protected $fillable = [
        'return_number',
        'order_id',
        'user_id',
        'type',
        'amount',
        'method',
        'status',
        'reason',
        'returned_at',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'returned_at' => 'datetime',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (OrderReturn $return) {
            if (empty($return->return_number)) {
                $return->return_number = 'RTN-'.date('Ymd-His').'-'.random_int(10, 99);
            }
        });
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(OrderReturnItem::class);
    }

    public function isExchange(): bool
    {
        return $this->type === 'exchange';
    }

    public function getTypeLabelAttribute(): string
    {
        return $this->isExchange() ? 'Exchange' : 'Return';
    }
}
