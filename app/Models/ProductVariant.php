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
        'cost_price',
        'stock_quantity',
        'attributes',
        'barcode',
        'is_active',
        'text_embedding',
    ];

    protected $casts = [
        'attributes' => 'array',
        'price' => 'decimal:2',
        'cost_price' => 'decimal:2',
        'stock_quantity' => 'integer',
        'is_active' => 'boolean',
        'text_embedding' => 'array',
    ];

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function stockMovements(): HasMany
    {
        return $this->hasMany(StockMovement::class, 'variant_id');
    }

    // Relational attribute values
    public function attributeValues(): HasMany
    {
        return $this->hasMany(ProductAttrValue::class, 'variant_id');
    }

    // Variant images
    public function images(): HasMany
    {
        return $this->hasMany(VariantImage::class, 'variant_id');
    }

    public function getEffectivePriceAttribute(): float
    {
        return $this->price ?? $this->product->base_price;
    }

    public function getDisplayAttribute(): string
    {
        $jsonAttributes = $this->getAttribute('attributes') ?? [];

        if (count($jsonAttributes)) {
            $parts = [];
            foreach ($jsonAttributes as $key => $value) {
                $parts[] = ucfirst($key).': '.$value;
            }

            return $this->name ?? implode(' / ', $parts);
        }

        if ($this->relationLoaded('attributeValues') && $this->attributeValues->count()) {
            $parts = [];
            foreach ($this->attributeValues as $av) {
                $parts[] = ($av->attribute?->name ?? '?').': '.$av->value;
            }

            return $this->name ?? implode(' / ', $parts);
        }

        return $this->name ?? '';
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
