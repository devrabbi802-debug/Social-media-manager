<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class VariantAttributeValue extends Model
{
    protected $fillable = [
        'variant_id',
        'attribute_template_id',
        'value',
    ];

    public function variant(): BelongsTo
    {
        return $this->belongsTo(ProductVariant::class, 'variant_id');
    }

    public function attributeTemplate(): BelongsTo
    {
        return $this->belongsTo(AttributeTemplate::class);
    }
}
