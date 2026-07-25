<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\Attribute;
use App\Models\AttributeValue;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;

class AttributeController extends Controller
{
    public function index(Request $request)
    {
        Log::info('AttributeController@index HIT', [
            'model_class' => get_class(new Attribute),
            'model_table' => (new Attribute)->getTable(),
        ]);
        $query = Attribute::with('category');

        if ($request->filled('category_id')) {
            $query->forCategory($request->category_id);
        }

        if ($request->boolean('global_only')) {
            $query->global();
        }

        if ($request->filled('data_type')) {
            $query->where('data_type', $request->data_type);
        }

        $attributes = $query->orderBy('is_global', 'desc')
            ->orderBy('category_id')
            ->orderBy('sort_order')
            ->orderBy('name')
            ->paginate(30);

        $categories = Category::whereNull('parent_id')->orderBy('name')->get();

        return view('tenant.attributes.index', compact('attributes', 'categories'));
    }

    public function create(Request $request)
    {
        $categories = Category::orderBy('name')->get();
        $categoryId = $request->category_id;

        return view('tenant.attributes.create', compact('categories', 'categoryId'));
    }

    public function store(Request $request)
    {
        $isGlobal = $request->boolean('is_global');

        $validated = $request->validate([
            'category_id' => $isGlobal ? 'nullable' : 'required|exists:categories,id',
            'name' => 'required|string|max:255',
            'data_type' => 'required|in:text,textarea,number,select,multiselect,boolean,date',
            'is_global' => 'boolean',
            'is_variant' => 'boolean',
            'is_filterable' => 'boolean',
            'sort_order' => 'nullable|integer|min:0',
        ]);

        $validated['slug'] = Str::slug($validated['name']);
        $validated['is_global'] = $isGlobal;
        $validated['is_variant'] = $request->boolean('is_variant');
        $validated['is_filterable'] = $request->boolean('is_filterable');

        if ($isGlobal) {
            $validated['category_id'] = null;
        }

        $attribute = Attribute::create($validated);

        // Save attribute values for select/multiselect types
        if (in_array($validated['data_type'], ['select', 'multiselect']) && $request->filled('values')) {
            $values = json_decode($request->values, true) ?? [];
            foreach ($values as $idx => $valData) {
                AttributeValue::create([
                    'attribute_id' => $attribute->id,
                    'value' => $valData['value'] ?? '',
                    'swatch_hex' => $valData['swatch_hex'] ?? null,
                    'sort_order' => $idx,
                ]);
            }
        }

        return redirect()->route('inventory.attributes.index')
            ->with('success', $isGlobal ? 'গ্লোবাল অ্যাট্রিবিউট তৈরি হয়েছে!' : 'অ্যাট্রিবিউট তৈরি হয়েছে!');
    }

    public function edit(Attribute $attribute)
    {
        $categories = Category::orderBy('name')->get();
        $attribute->load(['category', 'attributeValues' => fn ($q) => $q->orderBy('sort_order')]);

        return view('tenant.attributes.edit', compact('attribute', 'categories'));
    }

    public function update(Request $request, Attribute $attribute)
    {
        $isGlobal = $request->boolean('is_global');

        $validated = $request->validate([
            'category_id' => $isGlobal ? 'nullable' : 'required|exists:categories,id',
            'name' => 'required|string|max:255',
            'data_type' => 'required|in:text,textarea,number,select,multiselect,boolean,date',
            'is_global' => 'boolean',
            'is_variant' => 'boolean',
            'is_filterable' => 'boolean',
            'sort_order' => 'nullable|integer|min:0',
        ]);

        $validated['slug'] = Str::slug($validated['name']);
        $validated['is_global'] = $isGlobal;
        $validated['is_variant'] = $request->boolean('is_variant');
        $validated['is_filterable'] = $request->boolean('is_filterable');

        if ($isGlobal) {
            $validated['category_id'] = null;
        }

        $attribute->update($validated);

        // Save attribute values for select/multiselect types
        if (in_array($validated['data_type'], ['select', 'multiselect']) && $request->filled('values')) {
            $values = json_decode($request->values, true) ?? [];

            // Get existing IDs to track deletions
            $existingIds = $attribute->attributeValues()->pluck('id')->toArray();
            $submittedIds = [];

            foreach ($values as $idx => $valData) {
                $valId = $valData['id'] ?? null;
                $valDataSaved = AttributeValue::updateOrCreate(
                    ['id' => $valId, 'attribute_id' => $attribute->id],
                    [
                        'value' => $valData['value'] ?? '',
                        'swatch_hex' => $valData['swatch_hex'] ?? null,
                        'sort_order' => $idx,
                    ]
                );
                $submittedIds[] = $valDataSaved->id;
            }

            // Delete removed values
            $toDelete = array_diff($existingIds, $submittedIds);
            if (! empty($toDelete)) {
                AttributeValue::whereIn('id', $toDelete)->delete();
            }
        } elseif (! in_array($validated['data_type'], ['select', 'multiselect'])) {
            // If type changed from select to something else, clear values
            $attribute->attributeValues()->delete();
        }

        return redirect()->route('inventory.attributes.index')
            ->with('success', $isGlobal ? 'গ্লোবাল অ্যাট্রিবিউট আপডেট হয়েছে!' : 'অ্যাট্রিবিউট আপডেট হয়েছে!');
    }

    public function destroy(Attribute $attribute)
    {
        if ($attribute->productValues()->count() > 0) {
            return back()->with('error', 'এই অ্যাট্রিবিউট প্রোডাক্টে ব্যবহৃত হচ্ছে, তাই ডিলিট করা যাবে না!');
        }

        $attribute->delete();

        return redirect()->route('inventory.attributes.index')
            ->with('success', 'অ্যাট্রিবিউট ডিলিট হয়েছে!');
    }
}
