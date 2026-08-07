<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StockMovement extends Model
{
    protected $fillable = [
        'product_id',
        'variant_id',
        'warehouse_id',
        'type',
        'quantity',
        'reference',
        'notes',
        'created_by',
    ];

    protected $casts = [
        'quantity' => 'integer',
    ];

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function variant(): BelongsTo
    {
        return $this->belongsTo(ProductVariant::class, 'variant_id');
    }

    public function warehouse(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public static function typeLabel(?string $type): string
    {
        return match ($type) {
            'in' => 'Stock In',
            'out' => 'Stock Out',
            'adjustment' => 'Adjustment',
            default => $type ?? '',
        };
    }

    public function getTypeLabelAttribute(): string
    {
        return static::typeLabel($this->type);
    }

    public function getQuantityDisplayAttribute(): string
    {
        return match ($this->type) {
            'in' => '+'.$this->quantity,
            'out' => '-'.$this->quantity,
            default => (string) $this->quantity,
        };
    }
}
