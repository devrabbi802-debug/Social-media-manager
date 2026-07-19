<?php

namespace App\Http\Controllers;

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

        return view('tenant.storefront-settings', compact('storefront', 'themes'));
    }

    /**
     * Update storefront settings
     */
    public function update(Request $request)
    {
        $validated = $request->validate([
            'store_name' => 'nullable|string|max:255',

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
            'logo' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'favicon' => 'nullable|image|mimes:ico,png,jpg|max:1024',
        ]);

        $storefront = StorefrontSettings::first();

        if (!$storefront) {
            return back()->withErrors(['error' => 'Storefront settings not found.']);
        }

        // Handle logo upload
        if ($request->hasFile('logo')) {
            if ($storefront->store_logo && Storage::disk('public')->exists($storefront->store_logo)) {
                Storage::disk('public')->delete($storefront->store_logo);
            }
            $validated['store_logo'] = $request->file('logo')->store('storefront/logos', 'public');
        }
        unset($validated['logo']);

        // Handle favicon upload
        if ($request->hasFile('favicon')) {
            if ($storefront->store_favicon && Storage::disk('public')->exists($storefront->store_favicon)) {
                Storage::disk('public')->delete($storefront->store_favicon);
            }
            $validated['store_favicon'] = $request->file('favicon')->store('storefront/favicons', 'public');
        }
        unset($validated['favicon']);

        $storefront->update($validated);

        return back()->with('success', 'Storefront settings updated successfully!');
    }

    /**
     * Apply a preset theme
     */
    public function applyTheme(Request $request)
    {
        $availableThemes = implode(',', array_keys(ThemeController::THEMES));
        $request->validate([
            'theme_slug' => "required|string|in:{$availableThemes}",
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

        $path = $request->file('favicon')->store('storefront/favicons', 'public');
        $storefront->update(['store_favicon' => $path]);

        return back()->with('success', 'Favicon uploaded successfully!');
    }
}