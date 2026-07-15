<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

class BusinessCategory extends Model
{
    protected $fillable = [
        'name',
        'slug',
        'icon',
        'extra_fields',
        'is_active',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'extra_fields' => 'array',
            'is_active' => 'boolean',
        ];
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    public function scopeOrdered(Builder $query): Builder
    {
        return $query->orderBy('sort_order')->orderBy('name');
    }

    public function getExtraFieldsByType(string $type): array
    {
        return collect($this->extra_fields ?? [])
            ->where('type', $type)
            ->toArray();
    }

    public function getRequiredFields(): array
    {
        return collect($this->extra_fields ?? [])
            ->where('required', true)
            ->toArray();
    }
}
