<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class VariantImage extends Model
{
    protected $fillable = [
        'variant_id',
        'image_path',
        'alt_text',
        'sort_order',
        'image_analysis',
        'embedding',
    ];

    protected $casts = [
        'image_analysis' => 'array',
        'embedding' => 'array',
    ];

    public function variant(): BelongsTo
    {
        return $this->belongsTo(ProductVariant::class, 'variant_id');
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
            if ($value && $key !== 'raw_analysis') {
                $parts[] = ucfirst(str_replace('_', ' ', $key)) . ': ' . $value;
            }
        }

        return implode('. ', $parts);
    }
}
