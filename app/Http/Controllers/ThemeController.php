<?php

namespace App\Http\Controllers;

use App\Models\StorefrontSettings;
use Illuminate\Http\Request;

class ThemeController extends Controller
{
    const THEMES = [
        'clothing-fashion' => [
            'name' => 'Clothing Fashion',
            'thumbnail' => '/images/themes/clothing-fashion.jpg',
            'config' => [
                'colors' => [
                    'primary' => '#3B82F6',
                    'secondary' => '#10B981',
                    'accent' => '#F59E0B',
                    'background' => '#FFFFFF',
                    'text' => '#1F2937',
                    'header_bg' => '#1F2937',
                    'header_text' => '#FFFFFF',
                    'footer_bg' => '#111827',
                    'surface' => '#F9FAFB',
                    'border' => '#E5E7EB',
                ],
                'typography' => [
                    'font_family' => 'Hind Siliguri',
                    'heading_font' => 'Hind Siliguri',
                    'font_size_base' => 16,
                    'line_height' => 1.6,
                ],
                'components' => [
                    'card_style' => 'shadow',
                    'button_style' => 'rounded',
                ],
            ],
        ],
        'classic' => [
            'name' => 'Classic',
            'thumbnail' => '/images/themes/classic.jpg',
            'config' => [
                'colors' => [
                    'primary' => '#2563EB',
                    'secondary' => '#059669',
                    'accent' => '#D97706',
                    'background' => '#FFFFFF',
                    'text' => '#111827',
                    'header_bg' => '#FFFFFF',
                    'header_text' => '#111827',
                    'footer_bg' => '#F3F4F6',
                    'surface' => '#F9FAFB',
                    'border' => '#D1D5DB',
                ],
                'typography' => [
                    'font_family' => 'Hind Siliguri',
                    'heading_font' => 'Hind Siliguri',
                    'font_size_base' => 16,
                    'line_height' => 1.6,
                ],
                'components' => [
                    'card_style' => 'border',
                    'button_style' => 'sharp',
                ],
            ],
        ],
    ];

    /**
     * List all available themes
     */
    public function index()
    {
        $themes = collect(self::THEMES)->map(fn($theme, $key) => [
            'slug' => $key,
            'name' => $theme['name'],
            'thumbnail' => $theme['thumbnail'],
        ]);

        return response()->json($themes->values());
    }

    /**
     * Get theme config by slug
     */
    public function show(string $slug)
    {
        if (!isset(self::THEMES[$slug])) {
            return response()->json(['message' => 'Theme not found'], 404);
        }

        $theme = self::THEMES[$slug];
        return response()->json([
            'slug' => $slug,
            'name' => $theme['name'],
            'config' => $theme['config'],
        ]);
    }

    /**
     * Apply a theme (used by admin)
     */
    public function apply(Request $request)
    {
        $availableThemes = implode(',', array_keys(self::THEMES));
        $request->validate([
            'theme_slug' => "required|string|in:{$availableThemes}",
        ]);

        $storefront = StorefrontSettings::firstOrCreate(
            [],
            ['theme_slug' => $request->theme_slug]
        );

        if (!$storefront->wasRecentlyCreated) {
            $storefront->update(['theme_slug' => $request->theme_slug]);
        }

        return response()->json([
            'message' => 'Theme applied successfully',
            'theme' => [
                'slug' => $request->theme_slug,
                'config' => $storefront->resolvedTheme(),
            ],
        ]);
    }

    /**
     * Save theme overrides (custom colors, fonts, etc.)
     */
    public function customize(Request $request)
    {
        $validated = $request->validate([
            'overrides' => 'required|array',
            'overrides.colors' => 'nullable|array',
            'overrides.typography' => 'nullable|array',
            'overrides.components' => 'nullable|array',
        ]);

        $storefront = StorefrontSettings::first();

        if (!$storefront) {
            return response()->json(['message' => 'Storefront settings not found'], 404);
        }

        $storefront->update([
            'theme_overrides' => $validated['overrides'],
        ]);

        return response()->json([
            'message' => 'Theme customized successfully',
            'theme' => [
                'slug' => $storefront->theme_slug,
                'config' => $storefront->resolvedTheme(),
            ],
        ]);
    }

    /**
     * Reset theme to defaults
     */
    public function reset()
    {
        $storefront = StorefrontSettings::first();

        if (!$storefront) {
            return response()->json(['message' => 'Storefront settings not found'], 404);
        }

        $storefront->update([
            'theme_overrides' => null,
            'custom_css' => null,
        ]);

        return response()->json([
            'message' => 'Theme reset to defaults',
            'theme' => [
                'slug' => $storefront->theme_slug,
                'config' => $storefront->resolvedTheme(),
            ],
        ]);
    }
}