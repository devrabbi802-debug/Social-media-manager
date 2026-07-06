<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\AttributeTemplate;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use App\Models\ProductAttributeValue;
use App\Models\ProductImage;
use App\Models\ProductVariant;
use App\Jobs\AnalyzeProductImageJob;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    public function index(Request $request)
    {
        $query = Product::with(['category', 'brand', 'images', 'variants']);

        if ($request->filled('search')) {
            $query->search($request->search);
        }

        if ($request->filled('category_id')) {
            $query->where('category_id', $request->category_id);
        }

        if ($request->filled('brand_id')) {
            $query->where('brand_id', $request->brand_id);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->boolean('featured')) {
            $query->featured();
        }

        $products = $query->latest()->paginate(20);
        $categories = Category::whereNull('parent_id')->orderBy('name')->get();
        $brands = Brand::where('is_active', true)->orderBy('name')->get();

        return view('dashboard.products.index', compact('products', 'categories', 'brands'));
    }

    public function create()
    {
        $categories = Category::where('is_active', true)->orderBy('name')->get();
        $brands = Brand::where('is_active', true)->orderBy('name')->get();

        return view('dashboard.products.create', compact('categories', 'brands'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'category_id' => 'required|exists:categories,id',
            'brand_id' => 'nullable|exists:brands,id',
            'name' => 'required|string|max:255',
            'sku' => 'required|string|max:255|unique:products,sku',
            'description' => 'nullable|string',
            'base_price' => 'required|numeric|min:0',
            'discount_price' => 'nullable|numeric|min:0',
            'stock_quantity' => 'nullable|integer|min:0',
            'unit' => 'required|string|max:50',
            'barcode' => 'nullable|string|max:255',
            'status' => 'required|in:active,inactive,out_of_stock',
            'is_featured' => 'boolean',
            'meta_title' => 'nullable|string|max:255',
            'meta_description' => 'nullable|string',
            'sort_order' => 'nullable|integer|min:0',
            'images' => 'nullable|array',
            'images.*' => 'image|max:5120',
            'attribute.*' => 'nullable|string',
        ]);

        $validated['is_featured'] = $request->boolean('is_featured');
        $stock = $validated['stock_quantity'] ?? 0;
        $validated['stock_quantity'] = $stock;

        if ($stock <= 0 && $validated['status'] === 'active') {
            $validated['status'] = 'out_of_stock';
        }

        if (!empty($validated['discount_price']) && $validated['discount_price'] >= $validated['base_price']) {
            $validated['discount_price'] = null;
        }

        $product = Product::create($validated);

        if ($request->has('attribute')) {
            foreach ($request->attribute as $attrId => $value) {
                if (!empty(trim($value))) {
                    ProductAttributeValue::create([
                        'product_id' => $product->id,
                        'attribute_template_id' => $attrId,
                        'value' => $value,
                    ]);
                }
            }
        }

        if ($request->hasFile('images')) {
            foreach ($request->file('images') as $index => $imageFile) {
                $imagePath = $imageFile->store('products', 'public');
                $productImage = ProductImage::create([
                    'product_id' => $product->id,
                    'image_path' => $imagePath,
                    'sort_order' => $index,
                ]);

                AnalyzeProductImageJob::dispatch($productImage, auth()->id());
            }
        }

        return redirect()->route('inventory.products.index')
            ->with('success', 'প্রোডাক্ট সফলভাবে তৈরি হয়েছে!');
    }

    public function show(Product $product)
    {
        $product->load(['category', 'brand', 'attributeValues.attributeTemplate', 'variants.product', 'images', 'inventoryAlert', 'stockMovements.warehouse']);

        return view('dashboard.products.show', compact('product'));
    }

    public function edit(Product $product)
    {
        $product->load(['attributeValues.attributeTemplate', 'images', 'variants']);
        $categories = Category::where('is_active', true)->orderBy('name')->get();
        $brands = Brand::where('is_active', true)->orderBy('name')->get();
        $attributeTemplates = AttributeTemplate::where('category_id', $product->category_id)
            ->orderBy('sort_order')
            ->get();

        return view('dashboard.products.edit', compact('product', 'categories', 'brands', 'attributeTemplates'));
    }

    public function update(Request $request, Product $product)
    {
        $validated = $request->validate([
            'category_id' => 'required|exists:categories,id',
            'brand_id' => 'nullable|exists:brands,id',
            'name' => 'required|string|max:255',
            'sku' => 'required|string|max:255|unique:products,sku,' . $product->id,
            'description' => 'nullable|string',
            'base_price' => 'required|numeric|min:0',
            'discount_price' => 'nullable|numeric|min:0',
            'stock_quantity' => 'nullable|integer|min:0',
            'unit' => 'required|string|max:50',
            'barcode' => 'nullable|string|max:255',
            'status' => 'required|in:active,inactive,out_of_stock',
            'is_featured' => 'boolean',
            'meta_title' => 'nullable|string|max:255',
            'meta_description' => 'nullable|string',
            'sort_order' => 'nullable|integer|min:0',
            'images' => 'nullable|array',
            'images.*' => 'image|max:5120',
            'attribute.*' => 'nullable|string',
            'delete_images' => 'nullable|array',
        ]);

        $validated['is_featured'] = $request->boolean('is_featured');

        $stock = $validated['stock_quantity'] ?? $product->stock_quantity;
        $validated['stock_quantity'] = $stock;

        if ($stock <= 0 && $validated['status'] === 'active') {
            $validated['status'] = 'out_of_stock';
        }

        if (!empty($validated['discount_price']) && $validated['discount_price'] >= $validated['base_price']) {
            $validated['discount_price'] = null;
        }

        $product->update($validated);

        if ($request->has('attribute')) {
            foreach ($request->attribute as $attrId => $value) {
                if (!empty(trim($value))) {
                    ProductAttributeValue::updateOrCreate(
                        ['product_id' => $product->id, 'attribute_template_id' => $attrId],
                        ['value' => $value]
                    );
                }
            }
        }

        if ($request->filled('delete_images')) {
            foreach ($request->delete_images as $imageId) {
                $image = ProductImage::find($imageId);
                if ($image && $image->product_id === $product->id) {
                    \Storage::disk('public')->delete($image->image_path);
                    $image->delete();
                }
            }
        }

        if ($request->hasFile('images')) {
            $maxSort = $product->images()->max('sort_order') ?? 0;
            foreach ($request->file('images') as $index => $imageFile) {
                $imagePath = $imageFile->store('products', 'public');
                $productImage = ProductImage::create([
                    'product_id' => $product->id,
                    'image_path' => $imagePath,
                    'sort_order' => $maxSort + $index + 1,
                ]);

                AnalyzeProductImageJob::dispatch($productImage, auth()->id());
            }
        }

        return redirect()->route('inventory.products.index')
            ->with('success', 'প্রোডাক্ট আপডেট হয়েছে!');
    }

    public function destroy(Product $product)
    {
        foreach ($product->images as $image) {
            \Storage::disk('public')->delete($image->image_path);
        }

        $product->delete();

        return redirect()->route('inventory.products.index')
            ->with('success', 'প্রোডাক্ট ডিলিট হয়েছে!');
    }

    public function getAttributes(Request $request)
    {
        $categoryId = $request->category_id;
        $attributes = AttributeTemplate::where('category_id', $categoryId)
            ->orderBy('sort_order')
            ->get();

        return response()->json($attributes);
    }
}
