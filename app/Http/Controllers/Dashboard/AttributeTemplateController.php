<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\AttributeTemplate;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class AttributeTemplateController extends Controller
{
    public function index(Request $request)
    {
        $query = AttributeTemplate::with('category');

        if ($request->filled('category_id')) {
            $query->where('category_id', $request->category_id);
        }

        $attributes = $query->orderBy('category_id')->orderBy('sort_order')->orderBy('name')->paginate(30);
        $categories = Category::whereNull('parent_id')->orderBy('name')->get();

        return view('dashboard.attribute-templates.index', compact('attributes', 'categories'));
    }

    public function create(Request $request)
    {
        $categories = Category::orderBy('name')->get();
        $categoryId = $request->category_id;

        return view('dashboard.attribute-templates.create', compact('categories', 'categoryId'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'category_id' => 'required|exists:categories,id',
            'name' => 'required|string|max:255',
            'type' => 'required|in:text,number,select,boolean,date',
            'options' => 'nullable|string',
            'is_required' => 'boolean',
            'sort_order' => 'nullable|integer|min:0',
        ]);

        $validated['slug'] = Str::slug($validated['name']);
        $validated['is_required'] = $request->boolean('is_required');

        if ($validated['type'] === 'select' && !empty($validated['options'])) {
            $validated['options'] = array_map('trim', explode(',', $validated['options']));
        } else {
            $validated['options'] = null;
        }

        AttributeTemplate::create($validated);

        return redirect()->route('inventory.attributes.index')
            ->with('success', 'অ্যাট্রিবিউট টেমপ্লেট তৈরি হয়েছে!');
    }

    public function edit(AttributeTemplate $attribute)
    {
        $categories = Category::orderBy('name')->get();
        $attribute->load('category');

        return view('dashboard.attribute-templates.edit', compact('attribute', 'categories'));
    }

    public function update(Request $request, AttributeTemplate $attribute)
    {
        $validated = $request->validate([
            'category_id' => 'required|exists:categories,id',
            'name' => 'required|string|max:255',
            'type' => 'required|in:text,number,select,boolean,date',
            'options' => 'nullable|string',
            'is_required' => 'boolean',
            'sort_order' => 'nullable|integer|min:0',
        ]);

        $validated['slug'] = Str::slug($validated['name']);
        $validated['is_required'] = $request->boolean('is_required');

        if ($validated['type'] === 'select' && !empty($validated['options'])) {
            $validated['options'] = array_map('trim', explode(',', $validated['options']));
        } else {
            $validated['options'] = null;
        }

        $attribute->update($validated);

        return redirect()->route('inventory.attributes.index')
            ->with('success', 'অ্যাট্রিবিউট টেমপ্লেট আপডেট হয়েছে!');
    }

    public function destroy(AttributeTemplate $attribute)
    {
        if ($attribute->attributeValues()->count() > 0) {
            return back()->with('error', 'এই অ্যাট্রিবিউট প্রোডাক্টে ব্যবহৃত হচ্ছে, তাই ডিলিট করা যাবে না!');
        }

        $attribute->delete();

        return redirect()->route('inventory.attributes.index')
            ->with('success', 'অ্যাট্রিবিউট টেমপ্লেট ডিলিট হয়েছে!');
    }
}
