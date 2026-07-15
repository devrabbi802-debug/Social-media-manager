<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\BusinessCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class BusinessCategoryController extends Controller
{
    public function index()
    {
        $categories = BusinessCategory::ordered()->paginate(20);
        return view('admin.business-categories.index', compact('categories'));
    }

    public function create()
    {
        return view('admin.business-categories.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'icon' => 'nullable|string|max:10',
            'extra_fields' => 'nullable|array',
            'extra_fields.*.name' => 'required|string|max:255',
            'extra_fields.*.label' => 'required|string|max:255',
            'extra_fields.*.type' => 'required|in:text,textarea,number,boolean,select',
            'extra_fields.*.required' => 'nullable|boolean',
            'extra_fields.*.placeholder' => 'nullable|string|max:500',
            'extra_fields.*.default' => 'nullable|string|max:255',
            'extra_fields.*.options' => 'nullable|array',
            'is_active' => 'nullable|boolean',
            'sort_order' => 'nullable|integer|min:0',
        ]);

        $validated['slug'] = Str::slug($validated['name']);
        $validated['is_active'] = $request->boolean('is_active', true);

        // Clean extra_fields
        if (!empty($validated['extra_fields'])) {
            $validated['extra_fields'] = collect($validated['extra_fields'])
                ->filter(fn($f) => !empty($f['name']) && !empty($f['label']))
                ->values()
                ->toArray();
        }

        BusinessCategory::create($validated);

        return redirect()->route('admin.business-categories.index')
            ->with('success', 'ক্যাটাগরি সফলভাবে তৈরি হয়েছে!');
    }

    public function edit(BusinessCategory $businessCategory)
    {
        return view('admin.business-categories.edit', ['category' => $businessCategory]);
    }

    public function update(Request $request, BusinessCategory $businessCategory)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'icon' => 'nullable|string|max:10',
            'extra_fields' => 'nullable|array',
            'extra_fields.*.name' => 'required|string|max:255',
            'extra_fields.*.label' => 'required|string|max:255',
            'extra_fields.*.type' => 'required|in:text,textarea,number,boolean,select',
            'extra_fields.*.required' => 'nullable|boolean',
            'extra_fields.*.placeholder' => 'nullable|string|max:500',
            'extra_fields.*.default' => 'nullable|string|max:255',
            'extra_fields.*.options' => 'nullable|array',
            'is_active' => 'nullable|boolean',
            'sort_order' => 'nullable|integer|min:0',
        ]);

        $validated['slug'] = Str::slug($validated['name']);
        $validated['is_active'] = $request->boolean('is_active', true);

        if (!empty($validated['extra_fields'])) {
            $validated['extra_fields'] = collect($validated['extra_fields'])
                ->filter(fn($f) => !empty($f['name']) && !empty($f['label']))
                ->values()
                ->toArray();
        }

        $businessCategory->update($validated);

        return redirect()->route('admin.business-categories.index')
            ->with('success', 'ক্যাটাগরি আপডেট হয়েছে!');
    }

    public function destroy(BusinessCategory $businessCategory)
    {
        $businessCategory->delete();
        return redirect()->route('admin.business-categories.index')
            ->with('success', 'ক্যাটাগরি মুছে ফেলা হয়েছে!');
    }
}
