<?php

namespace App\Http\Controllers;

use App\Models\StorefrontBanner;
use App\Models\StorefrontSettings;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class StorefrontSettingsController extends Controller
{
    /**
     * Display storefront settings page
     */
    public function index()
    {
        $storefront = StorefrontSettings::firstOrCreate(
            [],
            ['theme_slug' => 'modern']
        );

        $themes = ThemeController::THEMES;
        $banners = $storefront->banners()->orderBy('sort_order')->get();

        return view('tenant.storefront-settings', compact('storefront', 'themes', 'banners'));
    }

    /**
     * Update storefront settings
     */
    public function update(Request $request)
    {
        $validated = $request->validate([
            'store_name' => 'nullable|string|max:255',
            'layout_style' => 'nullable|in:grid,list,masonry',
            'products_per_row' => 'nullable|integer|min:2|max:6',
            'products_per_row_mobile' => 'nullable|integer|min:1|max:4',
            'show_header_slider' => 'nullable|boolean',
            'show_brands_section' => 'nullable|boolean',
            'show_newsletter' => 'nullable|boolean',
            'footer_about_text' => 'nullable|string|max:1000',
            'facebook_url' => 'nullable|url|max:255',
            'instagram_url' => 'nullable|url|max:255',
            'youtube_url' => 'nullable|url|max:255',
            'whatsapp_number' => 'nullable|string|max:20',
            'footer_copyright_text' => 'nullable|string|max:255',
            'contact_phone' => 'nullable|string|max:20',
            'contact_email' => 'nullable|email|max:255',
            'contact_address' => 'nullable|string|max:1000',
            'custom_css' => 'nullable|string|max:10000',
        ]);

        $storefront = StorefrontSettings::first();

        if (!$storefront) {
            return back()->withErrors(['error' => 'Storefront settings not found.']);
        }

        $storefront->update($validated);

        return back()->with('success', 'Storefront settings updated successfully!');
    }

    /**
     * Apply a preset theme
     */
    public function applyTheme(Request $request)
    {
        $request->validate([
            'theme_slug' => 'required|string|in:modern,classic',
        ]);

        $storefront = StorefrontSettings::first();

        if (!$storefront) {
            $storefront = StorefrontSettings::create([
                'theme_slug' => $request->theme_slug,
            ]);
        } else {
            $storefront->update(['theme_slug' => $request->theme_slug]);
        }

        return back()->with('success', 'Theme applied successfully!');
    }

    /**
     * Upload logo
     */
    public function uploadLogo(Request $request)
    {
        $request->validate([
            'logo' => 'required|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
        ]);

        $storefront = StorefrontSettings::first();

        if (!$storefront) {
            return back()->withErrors(['error' => 'Storefront settings not found.']);
        }

        // Delete old logo if exists
        if ($storefront->store_logo && Storage::disk('public')->exists($storefront->store_logo)) {
            Storage::disk('public')->delete($storefront->store_logo);
        }

        $path = $request->file('logo')->store('storefront/logos', 'public');
        $storefront->update(['store_logo' => $path]);

        return back()->with('success', 'Logo uploaded successfully!');
    }

    /**
     * Upload favicon
     */
    public function uploadFavicon(Request $request)
    {
        $request->validate([
            'favicon' => 'required|image|mimes:ico,png,jpg|max:1024',
        ]);

        $storefront = StorefrontSettings::first();

        if (!$storefront) {
            return back()->withErrors(['error' => 'Storefront settings not found.']);
        }

        // Delete old favicon if exists
        if ($storefront->store_favicon && Storage::disk('public')->exists($storefront->store_favicon)) {
            Storage::disk('public')->delete($storefront->store_favicon);
        }

        $path = $request->file('favicon')->store('storefront/favicon', 'public');
        $storefront->update(['store_favicon' => $path]);

        return back()->with('success', 'Favicon uploaded successfully!');
    }

    /**
     * Create banner
     */
    public function storeBanner(Request $request)
    {
        $validated = $request->validate([
            'title' => 'nullable|string|max:255',
            'subtitle' => 'nullable|string|max:500',
            'image' => 'required|image|mimes:jpeg,png,jpg,gif,webp|max:5120',
            'link' => 'nullable|url|max:255',
            'btn_text' => 'nullable|string|max:100',
            'sort_order' => 'nullable|integer|min:0',
        ]);

        $storefront = StorefrontSettings::first();

        if (!$storefront) {
            return back()->withErrors(['error' => 'Storefront settings not found.']);
        }

        $path = $request->file('image')->store('storefront/banners', 'public');

        $storefront->banners()->create([
            'title' => $validated['title'] ?? null,
            'subtitle' => $validated['subtitle'] ?? null,
            'image' => $path,
            'link' => $validated['link'] ?? null,
            'btn_text' => $validated['btn_text'] ?? null,
            'sort_order' => $validated['sort_order'] ?? 0,
            'is_active' => true,
        ]);

        return back()->with('success', 'Banner created successfully!');
    }

    /**
     * Update banner
     */
    public function updateBanner(Request $request, StorefrontBanner $banner)
    {
        $validated = $request->validate([
            'title' => 'nullable|string|max:255',
            'subtitle' => 'nullable|string|max:500',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:5120',
            'link' => 'nullable|url|max:255',
            'btn_text' => 'nullable|string|max:100',
            'sort_order' => 'nullable|integer|min:0',
            'is_active' => 'nullable|boolean',
        ]);

        if ($request->hasFile('image')) {
            // Delete old image
            if ($banner->image && Storage::disk('public')->exists($banner->image)) {
                Storage::disk('public')->delete($banner->image);
            }
            $validated['image'] = $request->file('image')->store('storefront/banners', 'public');
        }

        $banner->update($validated);

        return back()->with('success', 'Banner updated successfully!');
    }

    /**
     * Delete banner
     */
    public function destroyBanner(StorefrontBanner $banner)
    {
        // Delete image file
        if ($banner->image && Storage::disk('public')->exists($banner->image)) {
            Storage::disk('public')->delete($banner->image);
        }

        $banner->delete();

        return back()->with('success', 'Banner deleted successfully!');
    }

    /**
     * Reorder banners
     */
    public function reorderBanners(Request $request)
    {
        $request->validate([
            'order' => 'required|array',
            'order.*' => 'required|integer|exists:storefront_banners,id',
        ]);

        foreach ($request->order as $index => $bannerId) {
            StorefrontBanner::where('id', $bannerId)->update(['sort_order' => $index]);
        }

        return response()->json(['message' => 'Banners reordered successfully!']);
    }
}