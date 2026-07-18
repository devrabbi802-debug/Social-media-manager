<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class StorefrontSettings extends Model
{
    protected $fillable = [
        'theme_slug',
        'theme_overrides',
        'store_name',
        'store_logo',
        'store_favicon',
        'layout_style',
        'products_per_row',
        'products_per_row_mobile',
        'show_header_slider',
        'show_brands_section',
        'show_newsletter',
        'footer_about_text',
        'footer_logo',
        'facebook_url',
        'instagram_url',
        'youtube_url',
        'whatsapp_number',
        'footer_copyright_text',
        'contact_phone',
        'contact_email',
        'contact_address',
        'custom_css',
    ];

    protected function casts(): array
    {
        return [
            'theme_overrides' => 'array',
            'show_header_slider' => 'boolean',
            'show_brands_section' => 'boolean',
            'show_newsletter' => 'boolean',
            'products_per_row' => 'integer',
            'products_per_row_mobile' => 'integer',
        ];
    }

    public function banners(): HasMany
    {
        return $this->hasMany(StorefrontBanner::class, 'storefront_settings_id');
    }

    /**
     * Get resolved theme config by merging preset + tenant overrides
     */
    public function resolvedTheme(): ?array
    {
        $preset = \App\Http\Controllers\ThemeController::THEMES[$this->theme_slug] ?? null;
        if (!$preset) {
            return null;
        }

        $config = $preset['config'];

        // Apply tenant overrides
        if ($this->theme_overrides) {
            foreach ($this->theme_overrides as $key => $value) {
                if (is_array($value) && isset($config[$key])) {
                    $config[$key] = array_merge($config[$key], $value);
                } else {
                    $config[$key] = $value;
                }
            }
        }

        return $config;
    }

    /**
     * Get active banners ordered by sort_order
     */
    public function getActiveBanners()
    {
        return $this->banners()
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->get();
    }
}