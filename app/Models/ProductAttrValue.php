<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProductAttrValue extends Model
{
    protected $table = 'product_attr_values';

    protected $fillable = [
        'product_id',
        'variant_id',
        'attribute_id',
        'value_id',
        'value',
    ];

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function variant(): BelongsTo
    {
        return $this->belongsTo(ProductVariant::class);
    }

    public function attribute(): BelongsTo
    {
        return $this->belongsTo(Attribute::class, 'attribute_id');
    }

    public function attributeValue(): BelongsTo
    {
        return $this->belongsTo(AttributeValue::class, 'value_id');
    }

    public function getTypedValueAttribute(): mixed
    {
        $attr = $this->attribute;

        if (! $attr) {
            return $this->value;
        }

        if ($this->value_id && $this->relationLoaded('attributeValue')) {
            return $this->attributeValue?->value ?? $this->value;
        }

        return match ($attr->data_type) {
            'number' => (float) $this->value,
            'boolean' => in_array($this->value, ['1', 'true', 1, true], true),
            'date' => $this->value,
            default => $this->value,
        };
    }

    public function getTypedValue(): mixed
    {
        if ($this->value_id) {
            $av = $this->attributeValue()->first();

            return $av?->value ?? $this->value;
        }

        $attr = $this->attribute()->first();

        return match ($attr?->data_type) {
            'number' => (float) $this->value,
            'boolean' => in_array($this->value, ['1', 'true', 1, true], true),
            default => $this->value,
        };
    }
}
