<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
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
        'is_global',
        'is_variant_option',
        'is_color',
        'is_active',
        'sort_order',
    ];

    protected $casts = [
        'options' => 'array',
        'is_required' => 'boolean',
        'is_global' => 'boolean',
        'is_variant_option' => 'boolean',
        'is_color' => 'boolean',
        'is_active' => 'boolean',
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

    public function options(): HasMany
    {
        return $this->hasMany(AttributeOption::class);
    }

    // --- Scopes ---

    public function scopeGlobal(Builder $query): Builder
    {
        return $query->where('is_global', true);
    }

    public function scopeForCategory(Builder $query, ?int $categoryId): Builder
    {
        return $query->where(function (Builder $q) use ($categoryId) {
            $q->where('is_global', true)
              ->orWhere('category_id', $categoryId);
        });
    }

    // --- Accessors ---

    public function getDisplayCategoryNameAttribute(): string
    {
        return $this->is_global ? 'সব ক্যাটাগরি (Global)' : ($this->category?->name ?? '-');
    }

    // --- Type Checks ---

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

    public function isColor(): bool
    {
        return $this->is_color && $this->type === 'select';
    }
}
