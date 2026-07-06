<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProductAttributeValue extends Model
{
    protected $fillable = [
        'product_id',
        'attribute_template_id',
        'value',
    ];

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function attributeTemplate(): BelongsTo
    {
        return $this->belongsTo(AttributeTemplate::class, 'attribute_template_id');
    }

    public function getTypedValueAttribute(): mixed
    {
        return match ($this->attributeTemplate?->type) {
            'number' => (float) $this->value,
            'boolean' => (bool) $this->value,
            'date' => $this->value,
            default => $this->value,
        };
    }
}
