<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StorefrontBanner extends Model
{
    protected $fillable = [
        'storefront_settings_id',
        'title',
        'subtitle',
        'image',
        'link',
        'btn_text',
        'sort_order',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'sort_order' => 'integer',
        ];
    }

    public function storefrontSettings(): BelongsTo
    {
        return $this->belongsTo(StorefrontSettings::class, 'storefront_settings_id');
    }
}