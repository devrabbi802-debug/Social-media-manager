<?php

namespace App\Http\Controllers;

use App\Models\StorefrontSettings;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ThemeEditorController extends Controller
{
    public function sections()
    {
        $storefront = StorefrontSettings::first();
        $data = $storefront?->sections_data;

        return response()->json([
            'banners' => $data['banners'] ?? [],
            'features' => $data['features'] ?? [],
            'notices' => $data['notices'] ?? [],
        ]);
    }

    public function updateBanners(Request $request)
    {
        $validated = $request->validate([
            'banners' => 'required|array',
            'banners.*.title' => 'nullable|string|max:255',
            'banners.*.subtitle' => 'nullable|string|max:500',
            'banners.*.link' => 'nullable|string|max:500',
            'banners.*.btn_text' => 'nullable|string|max:100',
            'banners.*.image' => 'nullable|string|max:500',
            'banners.*.sort_order' => 'nullable|integer|min:0',
            'banners.*.is_active' => 'nullable|boolean',
        ]);

        $storefront = StorefrontSettings::first();
        if (!$storefront) {
            return response()->json(['message' => 'Storefront not found'], 404);
        }

        $sectionsData = $storefront->sections_data ?? [];
        $sectionsData['banners'] = $validated['banners'];

        $storefront->update(['sections_data' => $sectionsData]);

        return response()->json([
            'message' => 'Banners updated successfully',
            'banners' => $sectionsData['banners'],
        ]);
    }

    public function updateNotices(Request $request)
    {
        $validated = $request->validate([
            'notices' => 'required|array',
            'notices.*' => 'nullable|string|max:500',
        ]);

        $storefront = StorefrontSettings::first();
        if (!$storefront) {
            return response()->json(['message' => 'Storefront not found'], 404);
        }

        $sectionsData = $storefront->sections_data ?? [];
        $sectionsData['notices'] = $validated['notices'];

        $storefront->update(['sections_data' => $sectionsData]);

        return response()->json([
            'message' => 'Notices updated successfully',
            'notices' => $sectionsData['notices'],
        ]);
    }

    public function uploadImage(Request $request)
    {
        $request->validate([
            'image' => 'required|image|mimes:jpeg,png,jpg,webp|max:5120',
        ]);

        $path = $request->file('image')->store('editor/banners', 'public');

        return response()->json([
            'message' => 'Image uploaded successfully',
            'url' => Storage::disk('public')->url($path),
            'path' => $path,
        ]);
    }
}
