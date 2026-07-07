<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InventoryAlert extends Model
{
    protected $fillable = [
        'product_id',
        'threshold',
        'is_active',
    ];

    protected $casts = [
        'threshold' => 'integer',
        'is_active' => 'boolean',
    ];

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function isLowStock(): bool
    {
        $stock = $this->product->variants->count() > 0
            ? $this->product->variants->sum('stock_quantity')
            : $this->product->stock_quantity;

        return $stock <= $this->threshold;
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeLowStock($query)
    {
        return $query->whereHas('product', function ($q) {
            $q->whereColumn('inventory_alerts.threshold', '>=', 'products.stock_quantity');
        });
    }
}
