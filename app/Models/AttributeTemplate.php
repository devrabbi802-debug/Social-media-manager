<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class AttributeTemplate extends Model
{
    protected $fillable = [
        'category_id',
        'name',
        'slug',
        'type',
        'options',
        'is_required',
        'sort_order',
    ];

    protected $casts = [
        'options' => 'array',
        'is_required' => 'boolean',
    ];

    protected static function booted(): void
    {
        static::creating(function (AttributeTemplate $attr) {
            if (empty($attr->slug)) {
                $attr->slug = Str::slug($attr->name);
            }
        });
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function attributeValues(): HasMany
    {
        return $this->hasMany(ProductAttributeValue::class, 'attribute_template_id');
    }

    public function isSelect(): bool
    {
        return $this->type === 'select';
    }

    public function isDate(): bool
    {
        return $this->type === 'date';
    }

    public function isNumber(): bool
    {
        return $this->type === 'number';
    }

    public function isBoolean(): bool
    {
        return $this->type === 'boolean';
    }
}
