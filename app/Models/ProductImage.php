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
        'embedding',
    ];

    protected $casts = [
        'image_analysis' => 'array',
        'embedding' => 'array',
    ];

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function getImageUrlAttribute(): string
    {
        // Use MEDIA_URL if set (e.g. Ngrok tunnel or public domain) so
        // Facebook/Zernio can fetch the image from outside local network.
        $baseUrl = config('services.media_url', config('app.url'));

        return $baseUrl.'/storage/'.$this->image_path;
    }

    public function hasAnalysis(): bool
    {
        return ! empty($this->image_analysis);
    }

    public function getAnalysisSummaryAttribute(): ?string
    {
        if (! $this->image_analysis) {
            return null;
        }

        $parts = [];
        foreach ($this->image_analysis as $key => $value) {
            $parts[] = ucfirst(str_replace('_', ' ', $key)).': '.$value;
        }

        return implode('. ', $parts);
    }
}
