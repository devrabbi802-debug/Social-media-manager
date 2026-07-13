<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class CategoryController extends Controller
{
    public function index(Request $request)
    {
        $query = Category::with('parent')->withCount('products');

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where('name', 'like', "%{$search}%");
        }

        if ($request->filled('parent_id')) {
            if ($request->parent_id === 'root') {
                $query->whereNull('parent_id');
            } else {
                $query->where('parent_id', $request->parent_id);
            }
        } elseif (!$request->filled('search')) {
            $query->whereNull('parent_id');
        }

        $categories = $query->orderBy('sort_order')->orderBy('name')->paginate(20);
        $rootCategories = Category::whereNull('parent_id')->orderBy('name')->get();

        return view('tenant.categories.index', compact('categories', 'rootCategories'));
    }

    public function create(Request $request)
    {
        $parentCategories = Category::whereNull('parent_id')->orderBy('name')->get();
        $parentId = $request->parent_id;

        return view('tenant.categories.create', compact('parentCategories', 'parentId'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'parent_id' => 'nullable|exists:categories,id',
            'description' => 'nullable|string',
            'image' => 'nullable|image|max:2048',
            'sort_order' => 'nullable|integer|min:0',
            'is_active' => 'boolean',
        ]);

        $validated['slug'] = Str::slug($validated['name']);
        $validated['is_active'] = $request->boolean('is_active');

        if ($request->hasFile('image')) {
            $validated['image'] = $request->file('image')->store('categories', 'public');
        }

        Category::create($validated);

        return redirect()->route('inventory.categories.index')
            ->with('success', 'ক্যাটাগরি সফলভাবে তৈরি হয়েছে!');
    }

    public function edit(Category $category)
    {
        $parentCategories = Category::whereNull('parent_id')
            ->where('id', '!=', $category->id)
            ->orderBy('name')
            ->get();

        return view('tenant.categories.edit', compact('category', 'parentCategories'));
    }

    public function update(Request $request, Category $category)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'parent_id' => 'nullable|exists:categories,id',
            'description' => 'nullable|string',
            'image' => 'nullable|image|max:2048',
            'sort_order' => 'nullable|integer|min:0',
            'is_active' => 'boolean',
        ]);

        $validated['slug'] = Str::slug($validated['name']);
        $validated['is_active'] = $request->boolean('is_active');

        if ($category->id == ($validated['parent_id'] ?? null)) {
            return back()->withErrors(['parent_id' => 'একটি ক্যাটাগরি নিজের উপর ক্যাটাগরি হতে পারে না।']);
        }

        if ($request->hasFile('image')) {
            if ($category->image) {
                \Storage::disk('public')->delete($category->image);
            }
            $validated['image'] = $request->file('image')->store('categories', 'public');
        }

        $category->update($validated);

        return redirect()->route('inventory.categories.index')
            ->with('success', 'ক্যাটাগরি আপডেট হয়েছে!');
    }

    public function destroy(Category $category)
    {
        if ($category->products()->count() > 0) {
            return back()->with('error', 'এই ক্যাটাগরিতে প্রোডাক্ট আছে, তাই ডিলিট করা যাবে না!');
        }

        if ($category->children()->count() > 0) {
            return back()->with('error', 'এই ক্যাটাগরির সাব-ক্যাটাগরি আছে, তাই ডিলিট করা যাবে না!');
        }

        if ($category->image) {
            \Storage::disk('public')->delete($category->image);
        }

        $category->delete();

        return redirect()->route('inventory.categories.index')
            ->with('success', 'ক্যাটাগরি ডিলিট হয়েছে!');
    }
}
