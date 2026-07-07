<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class AttributeOption extends Model
{
    protected $fillable = [
        'attribute_template_id',
        'value',
        'slug',
        'sort_order',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    protected static function booted(): void
    {
        static::creating(function (AttributeOption $option) {
            if (empty($option->slug)) {
                $option->slug = Str::slug($option->value);
            }
        });
    }

    public function attributeTemplate(): BelongsTo
    {
        return $this->belongsTo(AttributeTemplate::class);
    }
}
