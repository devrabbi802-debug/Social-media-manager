<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PosRefund extends Model
{
    protected $fillable = [
        'refund_number',
        'pos_order_id',
        'user_id',
        'amount',
        'method',
        'status',
        'reason',
        'items',
        'refunded_at',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'items' => 'array',
            'refunded_at' => 'datetime',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (PosRefund $refund) {
            if (empty($refund->refund_number)) {
                $refund->refund_number = 'RFD-'.date('Ymd-His').'-'.random_int(10, 99);
            }
        });
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(PosOrder::class, 'pos_order_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function getMethodNameAttribute(): string
    {
        $account = $this->method ? ChartOfAccount::byCode($this->method) : null;

        return $account?->name ?? ucfirst(str_replace('_', ' ', $this->method ?? ''));
    }
}
