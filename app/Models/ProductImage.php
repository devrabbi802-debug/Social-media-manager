<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProductImage extends Model
{
    protected $fillable = [
        'product_id',
        'image_path',
        'alt_text',
        'sort_order',
        'image_analysis',
    ];

    protected $casts = [
        'image_analysis' => 'array',
    ];

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function getImageUrlAttribute(): string
    {
        return asset('storage/' . $this->image_path);
    }

    public function hasAnalysis(): bool
    {
        return !empty($this->image_analysis);
    }

    public function getAnalysisSummaryAttribute(): ?string
    {
        if (!$this->image_analysis) {
            return null;
        }

        $parts = [];
        foreach ($this->image_analysis as $key => $value) {
            $parts[] = ucfirst(str_replace('_', ' ', $key)) . ': ' . $value;
        }

        return implode('. ', $parts);
    }
}
