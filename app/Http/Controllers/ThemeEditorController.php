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
            'category_banner' => $data['category_banner'] ?? null,
            'features' => $data['features'] ?? [],
            'notices' => isset($data['notices']) ? $data['notices'] : null,
            'categories' => $data['categories'] ?? [],
            'all_categories' => $data['all_categories'] ?? [],
            'section_titles' => $data['section_titles'] ?? [],
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
            'banners.*.align' => 'nullable|string|in:left,center,right',
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

    public function updateCategories(Request $request)
    {
        $validated = $request->validate([
            'categories' => 'required|array|max:5',
            'categories.*.id' => 'required|integer|exists:categories,id',
            'categories.*.name' => 'nullable|string|max:255',
            'categories.*.slug' => 'nullable|string|max:255',
            'categories.*.image' => 'nullable|string|max:500',
            'categories.*.custom_image' => 'nullable|string|max:500',
            'categories.*.products_count' => 'nullable|integer|min:0',
        ]);

        $storefront = StorefrontSettings::first();
        if (!$storefront) {
            return response()->json(['message' => 'Storefront not found'], 404);
        }

        $sectionsData = $storefront->sections_data ?? [];
        $sectionsData['categories'] = $validated['categories'];

        $storefront->update(['sections_data' => $sectionsData]);

        return response()->json([
            'message' => 'Categories updated successfully',
            'categories' => $sectionsData['categories'],
        ]);
    }

    public function updateAllCategories(Request $request)
    {
        $validated = $request->validate([
            'all_categories' => 'required|array',
            'all_categories.*.id' => 'required|integer|exists:categories,id',
            'all_categories.*.name' => 'nullable|string|max:255',
            'all_categories.*.slug' => 'nullable|string|max:255',
            'all_categories.*.image' => 'nullable|string|max:500',
            'all_categories.*.custom_image' => 'nullable|string|max:500',
            'all_categories.*.products_count' => 'nullable|integer|min:0',
        ]);

        $storefront = StorefrontSettings::first();
        if (!$storefront) {
            return response()->json(['message' => 'Storefront not found'], 404);
        }

        $sectionsData = $storefront->sections_data ?? [];
        $sectionsData['all_categories'] = $validated['all_categories'];

        $storefront->update(['sections_data' => $sectionsData]);

        return response()->json([
            'message' => 'All categories updated successfully',
            'all_categories' => $sectionsData['all_categories'],
        ]);
    }

    public function updateSectionTitle(Request $request)
    {
        $validated = $request->validate([
            'section' => 'required|string|max:100',
            'title' => 'nullable|string|max:255',
        ]);

        $storefront = StorefrontSettings::first();
        if (!$storefront) {
            return response()->json(['message' => 'Storefront not found'], 404);
        }

        $sectionsData = $storefront->sections_data ?? [];
        $sectionsData['section_titles'] = array_merge(
            $sectionsData['section_titles'] ?? [],
            [$validated['section'] => $validated['title']]
        );

        $storefront->update(['sections_data' => $sectionsData]);

        return response()->json([
            'message' => 'Section title updated successfully',
            'section_titles' => $sectionsData['section_titles'],
        ]);
    }

    public function updateCategoryBanner(Request $request)
    {
        $validated = $request->validate([
            'image' => 'nullable|string|max:500',
            'title' => 'nullable|string|max:255',
            'subtitle' => 'nullable|string|max:500',
            'label' => 'nullable|string|max:100',
            'btn_text' => 'nullable|string|max:100',
            'link' => 'nullable|string|max:500',
        ]);

        $storefront = StorefrontSettings::first();
        if (!$storefront) {
            return response()->json(['message' => 'Storefront not found'], 404);
        }

        $sectionsData = $storefront->sections_data ?? [];
        $sectionsData['category_banner'] = $validated;

        $storefront->update(['sections_data' => $sectionsData]);

        return response()->json([
            'message' => 'Category banner updated successfully',
            'category_banner' => $sectionsData['category_banner'],
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
