<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ProductVariant extends Model
{
    protected $fillable = [
        'product_id',
        'sku',
        'name',
        'price',
        'stock_quantity',
        'attributes',
        'barcode',
        'is_active',
    ];

    protected $casts = [
        'attributes' => 'array',
        'price' => 'decimal:2',
        'stock_quantity' => 'integer',
        'is_active' => 'boolean',
    ];

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function stockMovements(): HasMany
    {
        return $this->hasMany(StockMovement::class, 'variant_id');
    }

    // Relational attribute values (new system)
    public function attributeValues(): HasMany
    {
        return $this->hasMany(VariantAttributeValue::class, 'variant_id');
    }

    public function getEffectivePriceAttribute(): float
    {
        return $this->price ?? $this->product->base_price;
    }

    // Display name: relational data theke asbe, fallback JSON
    public function getDisplayAttribute(): string
    {
        // Relational data优先
        if ($this->relationLoaded('attributeValues') && $this->attributeValues->count()) {
            $parts = [];
            foreach ($this->attributeValues as $av) {
                $parts[] = $av->attributeTemplate->name . ': ' . $av->value;
            }
            return $this->name ?? implode(' / ', $parts);
        }

        // Fallback: JSON attributes
        $parts = [];
        foreach ($this->attributes as $key => $value) {
            $parts[] = ucfirst($key) . ': ' . $value;
        }

        return $this->name ?? implode(' / ', $parts);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
