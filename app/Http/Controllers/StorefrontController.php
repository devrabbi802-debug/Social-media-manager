<?php

namespace App\Http\Controllers;

use App\Models\StorefrontSettings;

class StorefrontController extends Controller
{
    /**
     * Display the React storefront SPA
     */
    public function index()
    {
        $storefront = StorefrontSettings::first();
        $themeConfig = $storefront?->resolvedTheme();
        $themeSlug = $storefront?->theme_slug ?? 'modern';

        return view('storefront', compact('storefront', 'themeConfig', 'themeSlug'));
    }
}