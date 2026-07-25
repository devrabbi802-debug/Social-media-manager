<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Attribute extends Model
{
    protected $table = 'attributes';

    protected $fillable = [
        'category_id',
        'name',
        'slug',
        'data_type',
        'is_global',
        'is_variant',
        'is_filterable',
        'is_active',
        'sort_order',
        'placeholder',
        'default',
        'attribute_group_id',
    ];

    protected $casts = [
        'is_global' => 'boolean',
        'is_variant' => 'boolean',
        'is_filterable' => 'boolean',
        'is_active' => 'boolean',
    ];

    protected static function booted(): void
    {
        static::creating(function (Attribute $attr) {
            if (empty($attr->slug)) {
                $attr->slug = Str::slug($attr->name);
            }
        });
    }

    // --- Relationships ---

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function categories(): BelongsToMany
    {
        return $this->belongsToMany(Category::class, 'category_attributes')
            ->withPivot(['required', 'sort_order'])
            ->withTimestamps();
    }

    public function attributeValues(): HasMany
    {
        return $this->hasMany(AttributeValue::class, 'attribute_id');
    }

    public function productValues(): HasMany
    {
        return $this->hasMany(ProductAttrValue::class, 'attribute_id');
    }

    public function attributeGroup(): BelongsTo
    {
        return $this->belongsTo(AttributeGroup::class);
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
                ->orWhere('category_id', $categoryId)
                ->orWhereHas('categories', fn (Builder $cq) => $cq->where('categories.id', $categoryId));
        });
    }

    public function scopeVariant(Builder $query): Builder
    {
        return $query->where('is_variant', true);
    }

    public function scopeFilterable(Builder $query): Builder
    {
        return $query->where('is_filterable', true);
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    // --- Accessors ---

    public function getDisplayCategoryNameAttribute(): string
    {
        return $this->is_global ? 'সব ক্যাটাগরি (Global)' : ($this->category?->name ?? '-');
    }

    // --- Type Checks ---

    public function isSelect(): bool
    {
        return in_array($this->data_type, ['select', 'multiselect']);
    }

    public function isMultiselect(): bool
    {
        return $this->data_type === 'multiselect';
    }

    public function isDate(): bool
    {
        return $this->data_type === 'date';
    }

    public function isNumber(): bool
    {
        return $this->data_type === 'number';
    }

    public function isBoolean(): bool
    {
        return $this->data_type === 'boolean';
    }

    public function getOptionsAttribute(): array
    {
        if (array_key_exists('options', $this->attributes) && is_array($this->attributes['options'])) {
            return $this->attributes['options'];
        }

        return $this->relationLoaded('attributeValues')
            ? $this->attributeValues->pluck('value')->toArray()
            : $this->attributeValues()->pluck('value')->toArray();
    }
}
