<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\AttributeTemplate;
use App\Models\Brand;
use App\Models\BusinessCategory;
use App\Models\Category;
use App\Models\Product;
use App\Models\ProductAttributeValue;
use App\Models\ProductImage;
use App\Models\ProductVariant;
use App\Models\BusinessSetting;
use App\Jobs\AnalyzeProductImageJob;
use App\Jobs\AnalyzeVariantImageJob;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class ProductController extends Controller
{
    public function index(Request $request)
    {
        $query = Product::with(['category', 'brand', 'images', 'variants']);

        if ($request->filled('search')) {
            $query->search($request->search);
        }

        if ($request->filled('category_id')) {
            $categoryIds = [$request->category_id];
            $childIds = Category::where('parent_id', $request->category_id)->pluck('id')->toArray();
            $categoryIds = array_merge($categoryIds, $childIds);
            $query->whereIn('category_id', $categoryIds);
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
        $categories = Category::whereNull('parent_id')->with('children')->orderBy('name')->get();
        $brands = Brand::where('is_active', true)->orderBy('name')->get();

        return view('tenant.products.index', compact('products', 'categories', 'brands'));
    }

    public function create()
    {
        $categories = Category::where('is_active', true)
            ->with('children')
            ->orderBy('name')
            ->get();
        $brands = Brand::where('is_active', true)->orderBy('name')->get();

        // Load user's business category extra fields (landlord DB)
        $businessSetting = BusinessSetting::where('user_id', auth()->id())->first();
        $businessCategory = null;
        $extraFields = [];
        $isDigital = false;
        $tenantCategory = null;

        if ($businessSetting && $businessSetting->category_id) {
            $businessCategory = BusinessCategory::on('mysql')->find($businessSetting->category_id);
            $extraFields = $businessCategory->extra_fields ?? [];
            $isDigital = in_array($businessCategory?->slug, ['digital-product', 'digital-product-service']);
            // Find matching tenant Category by slug (for attribute_templates FK)
            $tenantCategory = Category::where('slug', $businessCategory->slug)->first();
        }

        // Global variant options (Color, Size, Material, etc.)
        $variantOptions = AttributeTemplate::where('is_variant_option', true)
            ->where('is_global', true)
            ->orderBy('name')
            ->get(['id', 'name', 'options']);

        return view('tenant.products.create', compact(
            'categories', 'brands', 'extraFields', 'businessCategory',
            'isDigital', 'variantOptions', 'tenantCategory'
        ));
    }

    public function store(Request $request)
    {
        // Load business category for conditional validation
        $businessSetting = BusinessSetting::where('user_id', auth()->id())->first();
        $businessCategory = $businessSetting ? BusinessCategory::on('mysql')->find($businessSetting->category_id) : null;
        $isDigital = in_array($businessCategory?->slug, ['digital-product', 'digital-product-service']);
        $hasVariants = $request->filled('variants');
        // Find matching tenant Category for attribute_templates FK
        $tenantCategory = $businessCategory ? Category::where('slug', $businessCategory->slug)->first() : null;

        // --- Base validation rules ---
        $rules = [
            'category_id' => 'required|exists:categories,id',
            'brand_id' => 'nullable|exists:brands,id',
            'name' => 'required|string|max:255',
            'sku' => 'required|string|max:255|unique:products,sku',
            'description' => 'nullable|string',
            'base_price' => 'required|numeric|min:0',
            'discount_price' => 'nullable|numeric|min:0|lte:base_price',
            'barcode' => 'nullable|string|max:255|unique:products,barcode',
            'status' => 'required|in:active,inactive,out_of_stock',
            'is_featured' => 'boolean',
            'weight_kg' => 'nullable|numeric|min:0',
            'meta_title' => 'nullable|string|max:255',
            'meta_description' => 'nullable|string',
            'sort_order' => 'nullable|integer|min:0',
            'images' => 'nullable|array|max:10',
            'images.*' => 'image|max:5120',
        ];

        // Unit: required unless Digital (Decision 4)
        if (!$isDigital) {
            $rules['unit'] = 'required|string|max:50';
        } else {
            $rules['unit'] = 'nullable|string|max:50';
        }

        // Stock: mutually exclusive rules (Decision 2 & 8.2)
        if (!$hasVariants) {
            if (!$isDigital) {
                $rules['stock_quantity'] = 'required|integer|min:0';
            } else {
                $rules['stock_quantity'] = 'nullable|integer|min:0';
            }
        }
        // If hasVariants = true, stock_quantity NOT added (auto-calculated)

        // --- Variant rules ---
        if ($hasVariants) {
            $rules['variants'] = 'nullable|array|max:100';
            $rules['variants.*.sku'] = 'required_with:variants|string|max:255';
            $rules['variants.*.price'] = 'nullable|numeric|min:0';
            $rules['variants.*.barcode'] = 'nullable|string|max:255';
            $rules['variants.*.attributes'] = 'nullable|array';
            $rules['variants.*.images'] = 'nullable|array';
            $rules['variants.*.images.*'] = 'image|max:5120';

            if ($isDigital) {
                $rules['variants.*.stock_quantity'] = 'nullable|integer|min:0';
            } else {
                $rules['variants.*.stock_quantity'] = 'required_with:variants|integer|min:0';
            }
        }

        // --- Extra fields dynamic validation (Decision 8.5) ---
        $extraFieldsData = $request->input('extra', []);
        if ($businessCategory && !empty($extraFieldsData)) {
            $extraRules = [];
            foreach ($businessCategory->extra_fields ?? [] as $field) {
                $fieldName = $field['name'];
                $rule = 'nullable';

                if ($field['required'] ?? false) {
                    $rule = 'required';
                }

                switch ($field['type']) {
                    case 'text':
                    case 'textarea':
                        $rule .= '|string|max:1000';
                        break;
                    case 'number':
                        $rule .= '|numeric|min:0';
                        break;
                    case 'boolean':
                        $rule .= '|boolean';
                        break;
                    case 'select':
                        if (!empty($field['options'])) {
                            $rule .= '|in:' . implode(',', $field['options']);
                        }
                        break;
                }

                $extraRules["extra.{$fieldName}"] = $rule;
            }
            $rules = array_merge($rules, $extraRules);
        }

        $validated = $request->validate($rules);

        // Process extra: empty strings → null for booleans
        if ($businessCategory) {
            foreach ($businessCategory->extra_fields ?? [] as $field) {
                $key = "extra.{$field['name']}";
                if ($field['type'] === 'boolean' && array_key_exists($key, $validated)) {
                    $validated[$key] = $request->boolean("extra.{$field['name']}");
                }
            }
        }

        // Featured boolean
        $validated['is_featured'] = $request->boolean('is_featured');

        // Stock auto-out-of-stock check
        $stock = $validated['stock_quantity'] ?? 0;
        if ($stock <= 0 && $validated['status'] === 'active') {
            $validated['status'] = 'out_of_stock';
        }

        // Remove non-fillable fields before create
        $productData = collect($validated)->only([
            'category_id', 'brand_id', 'name', 'sku', 'description',
            'base_price', 'discount_price', 'stock_quantity', 'unit',
            'barcode', 'status', 'is_featured', 'weight_kg',
            'meta_title', 'meta_description', 'sort_order',
        ])->toArray();

        $product = Product::create($productData);

        // --- Save extra fields to product_attribute_values ---
        if ($businessCategory && !empty($extraFieldsData)) {
            foreach ($businessCategory->extra_fields ?? [] as $field) {
                $fieldName = $field['name'];
                $value = $extraFieldsData[$fieldName] ?? null;

                // Skip empty values (except required ones — already validated)
                if ($value === null || $value === '' || $value === false) {
                    // For boolean false, still save it
                    if ($field['type'] === 'boolean' && $value === false) {
                        $value = '0';
                    } else {
                        continue;
                    }
                }

                // Find matching AttributeTemplate (use tenant Category id)
                $template = $tenantCategory
                    ? AttributeTemplate::where('category_id', $tenantCategory->id)
                        ->where('slug', Str::slug($fieldName))
                        ->where('is_global', false)
                        ->first()
                    : null;

                if (!$template) {
                    // Template not found — skip silently (sync job will fix)
                    continue;
                }

                // Cast value to string for TEXT column
                $stringValue = is_bool($value) ? ($value ? '1' : '0') : (string) $value;

                ProductAttributeValue::create([
                    'product_id' => $product->id,
                    'attribute_template_id' => $template->id,
                    'value' => $stringValue,
                ]);
            }
        }

        // --- Handle variants from matrix ---
        if ($hasVariants) {
            $totalVariantStock = 0;
            foreach ($request->variants as $variantData) {
                if (empty($variantData['sku'])) continue;

                $variantStock = (int) ($variantData['stock_quantity'] ?? 0);
                $totalVariantStock += $variantStock;

                $variant = ProductVariant::create([
                    'product_id' => $product->id,
                    'name' => null,
                    'sku' => $variantData['sku'],
                    'price' => $variantData['price'] ?? null,
                    'stock_quantity' => $variantStock,
                    'barcode' => $variantData['barcode'] ?? null,
                    'attributes' => $variantData['attributes'] ?? [],
                    'is_active' => true,
                ]);

                // Save to relational table (variant_attribute_values)
                if (!empty($variantData['attributes'])) {
                    foreach ($variantData['attributes'] as $attrName => $attrValue) {
                        $attrTemplate = AttributeTemplate::where('name', $attrName)
                            ->where(function ($q) use ($product) {
                                $q->where('category_id', $product->category_id)
                                  ->orWhere('is_global', true);
                            })
                            ->first();

                        if ($attrTemplate) {
                            \App\Models\VariantAttributeValue::create([
                                'variant_id' => $variant->id,
                                'attribute_template_id' => $attrTemplate->id,
                                'value' => $attrValue,
                            ]);
                        }
                    }
                }

                // Handle variant images from matrix
                if (!empty($variantData['images'])) {
                    foreach ($variantData['images'] as $index => $imageFile) {
                        if ($imageFile instanceof \Illuminate\Http\UploadedFile) {
                            $imagePath = $imageFile->store('variants', 'public');
                            $variantImage = \App\Models\VariantImage::create([
                                'variant_id' => $variant->id,
                                'image_path' => $imagePath,
                                'sort_order' => $index,
                            ]);

                            AnalyzeVariantImageJob::dispatch($variantImage, auth()->id());
                        }
                    }
                }
            }

            // Auto-calculate stock from variants (Decision 2)
            $product->update(['stock_quantity' => $totalVariantStock]);
        }

        // --- Handle product images ---
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

        return redirect()->route('inventory.products.edit', $product)
            ->with('success', 'প্রোডাক্ট সফলভাবে তৈরি হয়েছে!');
    }

    public function show(Product $product)
    {
        $product->load([
            'category', 'brand', 'attributeValues.attributeTemplate',
            'variants.images', 'images', 'inventoryAlert', 'stockMovements.warehouse'
        ]);

        // Load business category for extra fields display
        $businessSetting = BusinessSetting::where('user_id', auth()->id())->first();
        $businessCategory = $businessSetting ? BusinessCategory::on('mysql')->find($businessSetting->category_id) : null;

        return view('tenant.products.show', compact('product', 'businessCategory'));
    }

    public function generateEmbeddings(Product $product)
    {
        $images = $product->images;
        $variantImages = $product->variants->flatMap->images;

        $processed = 0;
        $errors = 0;

        foreach ($images as $image) {
            try {
                AnalyzeProductImageJob::dispatch($image, auth()->id());
                $processed++;
            } catch (\Exception $e) {
                $errors++;
            }
        }

        foreach ($variantImages as $image) {
            try {
                AnalyzeVariantImageJob::dispatch($image, auth()->id());
                $processed++;
            } catch (\Exception $e) {
                $errors++;
            }
        }

        $message = "{$processed}টি ছবি AI দিয়ে চেনানোর জন্য পাঠানো হয়েছে।";
        if ($errors > 0) {
            $message .= " {$errors}টি ছবিতে সমস্যা হয়েছে।";
        }

        return redirect()->route('inventory.products.show', $product)
            ->with('success', $message);
    }

    public function generateVariantEmbeddings(Product $product)
    {
        $variantImages = $product->variants->flatMap->images;
        $processed = 0;
        $errors = 0;

        foreach ($variantImages as $image) {
            try {
                AnalyzeVariantImageJob::dispatch($image, auth()->id());
                $processed++;
            } catch (\Exception $e) {
                $errors++;
            }
        }

        $message = "{$processed}টি ভ্যারিয়েন্ট ছবি AI দিয়ে চেনানোর জন্য পাঠানো হয়েছে।";
        if ($errors > 0) {
            $message .= " {$errors}টি ছবিতে সমস্যা হয়েছে।";
        }

        return redirect()->route('inventory.products.show', $product)
            ->with('success', $message);
    }

    public function edit(Product $product)
    {
        $product->load([
            'attributeValues.attributeTemplate', 'images',
            'variants.attributeValues.attributeTemplate', 'variants.images'
        ]);

        $categories = Category::where('is_active', true)
            ->with('children')
            ->orderBy('name')
            ->get();
        $brands = Brand::where('is_active', true)->orderBy('name')->get();

        // Load business category extra fields
        $businessSetting = BusinessSetting::where('user_id', auth()->id())->first();
        $businessCategory = $businessSetting ? BusinessCategory::on('mysql')->find($businessSetting->category_id) : null;
        $extraFields = $businessCategory->extra_fields ?? [];
        $isDigital = in_array($businessCategory?->slug, ['digital-product', 'digital-product-service']);
        // Find matching tenant Category for attribute_templates FK
        $tenantCategory = $businessCategory ? Category::where('slug', $businessCategory->slug)->first() : null;

        // Map existing extra field values (key by field name to match $field['name'] in view)
        $existingExtraValues = [];
        foreach ($product->attributeValues as $av) {
            if ($av->attributeTemplate && !$av->attributeTemplate->is_global && !$av->attributeTemplate->is_variant_option) {
                $existingExtraValues[$av->attributeTemplate->slug] = $av->typed_value;
            }
        }
        // Also build slug→name map from business category extra_fields so view can look up by $field['name']
        $slugToFieldName = [];
        foreach ($extraFields as $field) {
            $slugToFieldName[Str::slug($field['name'])] = $field['name'];
        }
        $nameKeyedValues = [];
        foreach ($existingExtraValues as $slug => $value) {
            $fieldName = $slugToFieldName[$slug] ?? $slug;
            $nameKeyedValues[$fieldName] = $value;
        }
        $existingExtraValues = $nameKeyedValues;

        // Shudhu sei attribute templates dekhao jei ei product er variants e actually use hoy
        $productOptionNames = $product->variants->flatMap(function ($variant) {
            return is_array($variant->attributes) ? array_keys($variant->attributes) : [];
        })->unique()->values()->all();

        $attributeTemplates = AttributeTemplate::forCategory($product->category_id)
            ->where(function ($q) use ($productOptionNames) {
                $q->where(function ($q2) use ($productOptionNames) {
                    $q2->where('is_variant_option', true)
                       ->whereIn('name', $productOptionNames);
                });
                $q->orWhere('is_variant_option', false);
            })
            ->orderBy('is_global', 'desc')
            ->orderBy('sort_order')
            ->get();

        // Global variant options for adding new variants
        $variantOptions = AttributeTemplate::where('is_variant_option', true)
            ->where('is_global', true)
            ->orderBy('name')
            ->get(['id', 'name', 'options']);

        return view('tenant.products.edit', compact(
            'product', 'categories', 'brands', 'attributeTemplates',
            'extraFields', 'businessCategory', 'existingExtraValues',
            'isDigital', 'variantOptions', 'tenantCategory'
        ));
    }

    public function update(Request $request, Product $product)
    {
        // Load business category for conditional validation
        $businessSetting = BusinessSetting::where('user_id', auth()->id())->first();
        $businessCategory = $businessSetting ? BusinessCategory::on('mysql')->find($businessSetting->category_id) : null;
        $isDigital = in_array($businessCategory?->slug, ['digital-product', 'digital-product-service']);
        $hasVariants = $request->filled('variants') || $product->variants()->count() > 0;
        // Find matching tenant Category for attribute_templates FK
        $tenantCategory = $businessCategory ? Category::where('slug', $businessCategory->slug)->first() : null;

        // --- Base validation rules ---
        $rules = [
            'category_id' => 'required|exists:categories,id',
            'brand_id' => 'nullable|exists:brands,id',
            'name' => 'required|string|max:255',
            'sku' => 'required|string|max:255|unique:products,sku,' . $product->id,
            'description' => 'nullable|string',
            'base_price' => 'required|numeric|min:0',
            'discount_price' => 'nullable|numeric|min:0|lte:base_price',
            'barcode' => 'nullable|string|max:255|unique:products,barcode,' . $product->id,
            'status' => 'required|in:active,inactive,out_of_stock',
            'is_featured' => 'boolean',
            'weight_kg' => 'nullable|numeric|min:0',
            'meta_title' => 'nullable|string|max:255',
            'meta_description' => 'nullable|string',
            'sort_order' => 'nullable|integer|min:0',
            'images' => 'nullable|array',
            'images.*' => 'image|max:5120',
            'delete_images' => 'nullable|array',
            'delete_variant_images' => 'nullable|array',
            'attribute.*' => 'nullable|string',
        ];

        // Unit: required unless Digital
        if (!$isDigital) {
            $rules['unit'] = 'required|string|max:50';
        } else {
            $rules['unit'] = 'nullable|string|max:50';
        }

        // Stock: only validate if no variants exist
        if (!$hasVariants) {
            if (!$isDigital) {
                $rules['stock_quantity'] = 'required|integer|min:0';
            } else {
                $rules['stock_quantity'] = 'nullable|integer|min:0';
            }
        }

        // --- Variant rules ---
        if ($request->filled('variants')) {
            $rules['variants'] = 'nullable|array|max:100';
            $rules['variants.*.sku'] = 'required_with:variants|string|max:255';
            $rules['variants.*.price'] = 'nullable|numeric|min:0';
            $rules['variants.*.barcode'] = 'nullable|string|max:255';
            $rules['variants.*.attributes'] = 'nullable|array';
            $rules['variants.*.images'] = 'nullable|array';
            $rules['variants.*.images.*'] = 'image|max:5120';

            if ($isDigital) {
                $rules['variants.*.stock_quantity'] = 'nullable|integer|min:0';
            } else {
                $rules['variants.*.stock_quantity'] = 'required_with:variants|integer|min:0';
            }
        }

        // --- Extra fields dynamic validation ---
        $extraFieldsData = $request->input('extra', []);
        if ($businessCategory && !empty($extraFieldsData)) {
            $extraRules = [];
            foreach ($businessCategory->extra_fields ?? [] as $field) {
                $fieldName = $field['name'];
                $rule = 'nullable';

                if ($field['required'] ?? false) {
                    $rule = 'required';
                }

                switch ($field['type']) {
                    case 'text':
                    case 'textarea':
                        $rule .= '|string|max:1000';
                        break;
                    case 'number':
                        $rule .= '|numeric|min:0';
                        break;
                    case 'boolean':
                        $rule .= '|boolean';
                        break;
                    case 'select':
                        if (!empty($field['options'])) {
                            $rule .= '|in:' . implode(',', $field['options']);
                        }
                        break;
                }

                $extraRules["extra.{$fieldName}"] = $rule;
            }
            $rules = array_merge($rules, $extraRules);
        }

        $validated = $request->validate($rules);

        // Process extra: boolean handling
        if ($businessCategory) {
            foreach ($businessCategory->extra_fields ?? [] as $field) {
                $key = "extra.{$field['name']}";
                if ($field['type'] === 'boolean' && array_key_exists($key, $validated)) {
                    $validated[$key] = $request->boolean("extra.{$field['name']}");
                }
            }
        }

        $validated['is_featured'] = $request->boolean('is_featured');

        // Stock auto-out-of-stock check
        $stock = $validated['stock_quantity'] ?? $product->stock_quantity;
        if ($stock <= 0 && $validated['status'] === 'active') {
            $validated['status'] = 'out_of_stock';
        }

        // Remove non-fillable fields
        $productData = collect($validated)->only([
            'category_id', 'brand_id', 'name', 'sku', 'description',
            'base_price', 'discount_price', 'stock_quantity', 'unit',
            'barcode', 'status', 'is_featured', 'weight_kg',
            'meta_title', 'meta_description', 'sort_order',
        ])->toArray();

        $product->update($productData);

        // --- Save extra fields (full replacement) ---
        if ($businessCategory) {
            // Delete existing extra field values for this product
            $existingTemplateIds = $product->attributeValues()
                ->whereHas('attributeTemplate', function ($q) {
                    $q->where('is_global', false)->where('is_variant_option', false);
                })
                ->pluck('attribute_template_id')
                ->toArray();

            $product->attributeValues()
                ->whereIn('attribute_template_id', $existingTemplateIds)
                ->delete();

            // Recreate from form data
            foreach ($businessCategory->extra_fields ?? [] as $field) {
                $fieldName = $field['name'];
                $value = $extraFieldsData[$fieldName] ?? null;

                if ($value === null || $value === '') {
                    if ($field['type'] === 'boolean') {
                        $value = '0';
                    } else {
                        continue;
                    }
                }

                $template = $tenantCategory
                    ? AttributeTemplate::where('category_id', $tenantCategory->id)
                        ->where('slug', Str::slug($fieldName))
                        ->where('is_global', false)
                        ->first()
                    : null;

                if (!$template) continue;

                $stringValue = is_bool($value) ? ($value ? '1' : '0') : (string) $value;

                ProductAttributeValue::create([
                    'product_id' => $product->id,
                    'attribute_template_id' => $template->id,
                    'value' => $stringValue,
                ]);
            }
        }

        // Save product-level attributes
        if ($request->has('attribute')) {
            foreach ($request->attribute as $attrId => $value) {
                if (!empty(trim($value))) {
                    // Check if already exists
                    $existing = ProductAttributeValue::where('product_id', $product->id)
                        ->where('attribute_template_id', $attrId)
                        ->first();

                    if ($existing) {
                        $existing->update(['value' => $value]);
                    } else {
                        ProductAttributeValue::create([
                            'product_id' => $product->id,
                            'attribute_template_id' => $attrId,
                            'value' => $value,
                        ]);
                    }
                }
            }
        }

        // Delete product images
        if ($request->filled('delete_images')) {
            foreach ($request->delete_images as $imageId) {
                $image = ProductImage::find($imageId);
                if ($image && $image->product_id === $product->id) {
                    \Storage::disk('public')->delete($image->image_path);
                    $image->delete();
                }
            }
        }

        // Add new product images
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

        // Handle variant images delete
        if ($request->filled('delete_variant_images')) {
            foreach ($request->delete_variant_images as $variantId => $imageIds) {
                $variant = $product->variants()->find($variantId);
                if ($variant) {
                    foreach ($imageIds as $imageId) {
                        $img = $variant->images()->find($imageId);
                        if ($img) {
                            \Storage::disk('public')->delete($img->image_path);
                            $img->delete();
                        }
                    }
                }
            }
        }

        // Handle variant images upload
        $variantImageFiles = $request->file('variant_images', []);
        if (!empty($variantImageFiles)) {
            foreach ($variantImageFiles as $variantId => $files) {
                $variant = $product->variants()->find($variantId);
                if ($variant && !empty($files)) {
                    $existingCount = $variant->images()->count();
                    foreach ($files as $index => $imageFile) {
                        if ($imageFile instanceof \Illuminate\Http\UploadedFile) {
                            $imagePath = $imageFile->store('variants', 'public');
                            $variantImage = \App\Models\VariantImage::create([
                                'variant_id' => $variant->id,
                                'image_path' => $imagePath,
                                'sort_order' => $existingCount + $index,
                            ]);

                            AnalyzeVariantImageJob::dispatch($variantImage, auth()->id());
                        }
                    }
                }
            }
        }

        return redirect()->route('inventory.products.index')
            ->with('success', 'প্রোডাক্ট আপডেট হয়েছে!');
    }

    public function destroy(Product $product)
    {
        \DB::transaction(function () use ($product) {
            foreach ($product->images as $image) {
                \Storage::disk('public')->delete($image->image_path);
            }

            foreach ($product->variants as $variant) {
                foreach ($variant->images as $image) {
                    \Storage::disk('public')->delete($image->image_path);
                }
            }

            $product->stockMovements()->delete();
            $product->inventoryAlert()->delete();
            $product->attributeValues()->delete();
            $product->variants()->delete();
            $product->images()->delete();
            $product->delete();
        });

        return redirect()->route('inventory.products.index')
            ->with('success', 'প্রোডাক্ট ডিলিট হয়েছে!');
    }

    public function getAttributes(Request $request)
    {
        $categoryId = $request->category_id;
        $attributes = AttributeTemplate::forCategory($categoryId)
            ->orderBy('is_global', 'desc')
            ->orderBy('sort_order')
            ->get();

        return response()->json($attributes);
    }

    public function getVariantOptions(Request $request)
    {
        $categoryId = $request->category_id;
        if (!$categoryId) {
            return response()->json([]);
        }

        $options = AttributeTemplate::where('is_variant_option', true)
            ->where(function ($q) use ($categoryId) {
                $q->where('category_id', $categoryId)
                  ->orWhere('is_global', true);
            })
            ->orderBy('name')
            ->get(['id', 'name', 'options']);

        return response()->json($options);
    }

    public function getExtraFields(Request $request)
    {
        $categoryId = $request->category_id;
        if (!$categoryId) {
            return response()->json(['extraFields' => [], 'businessCategory' => null, 'isDigital' => false]);
        }

        $tenantCategory = Category::find($categoryId);
        if (!$tenantCategory) {
            return response()->json(['extraFields' => [], 'businessCategory' => null, 'isDigital' => false]);
        }

        $businessCategory = BusinessCategory::on('mysql')->where('slug', $tenantCategory->slug)->first();
        $extraFields = $businessCategory->extra_fields ?? [];
        $isDigital = in_array($businessCategory?->slug, ['digital-product', 'digital-product-service']);

        return response()->json([
            'extraFields' => $extraFields,
            'businessCategory' => $businessCategory ? ['id' => $businessCategory->id, 'name' => $businessCategory->name, 'slug' => $businessCategory->slug] : null,
            'isDigital' => $isDigital,
        ]);
    }

    public function storeVariant(Request $request, Product $product)
    {
        $validated = $request->validate([
            'sku' => 'required|string|max:255|unique:product_variants,sku',
            'name' => 'nullable|string|max:255',
            'price' => 'nullable|numeric|min:0',
            'stock_quantity' => 'required|integer|min:0',
            'attributes' => 'required|array',
            'barcode' => 'nullable|string|max:255',
            'images' => 'nullable|array',
            'images.*' => 'image|mimes:jpeg,png,jpg,gif,webp|max:5120',
        ]);

        $variant = ProductVariant::create(array_merge($validated, [
            'product_id' => $product->id,
            'is_active' => true,
        ]));

        // Handle variant images
        if ($request->hasFile('images')) {
            foreach ($request->file('images') as $index => $imageFile) {
                $imagePath = $imageFile->store('variants', 'public');
                $variantImage = \App\Models\VariantImage::create([
                    'variant_id' => $variant->id,
                    'image_path' => $imagePath,
                    'sort_order' => $index,
                ]);

                AnalyzeVariantImageJob::dispatch($variantImage, auth()->id());
            }
        }

        $product->recalculateStock();

        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'ভ্যারিয়েন্ট যোগ হয়েছে!',
                'variant' => $variant->load('images'),
            ]);
        }

        return redirect()->route('inventory.products.edit', $product)
            ->with('success', 'ভ্যারিয়েন্ট যোগ হয়েছে!');
    }

    public function updateVariant(Request $request, Product $product, ProductVariant $variant)
    {
        $validated = $request->validate([
            'sku' => 'required|string|max:255|unique:product_variants,sku,' . $variant->id,
            'name' => 'nullable|string|max:255',
            'price' => 'nullable|numeric|min:0',
            'stock_quantity' => 'required|integer|min:0',
            'attributes' => 'nullable|array',
            'barcode' => 'nullable|string|max:255',
            'is_active' => 'boolean',
            'images' => 'nullable|array',
            'images.*' => 'image|mimes:jpeg,png,jpg,gif,webp|max:5120',
            'delete_variant_images' => 'nullable|array',
        ]);

        // Delete selected images
        if (!empty($validated['delete_variant_images'])) {
            foreach ($validated['delete_variant_images'] as $imageIds) {
                if (is_array($imageIds)) {
                    $imagesToDelete = $variant->images()->whereIn('id', $imageIds)->get();
                    foreach ($imagesToDelete as $img) {
                        \Storage::disk('public')->delete($img->image_path);
                        $img->delete();
                    }
                }
            }
        }
        unset($validated['delete_variant_images']);

        $variant->update($validated);

        // Handle new variant images
        if ($request->hasFile('images')) {
            $existingCount = $variant->images()->count();
            foreach ($request->file('images') as $index => $imageFile) {
                $imagePath = $imageFile->store('variants', 'public');
                $variantImage = \App\Models\VariantImage::create([
                    'variant_id' => $variant->id,
                    'image_path' => $imagePath,
                    'sort_order' => $existingCount + $index,
                ]);

                AnalyzeVariantImageJob::dispatch($variantImage, auth()->id());
            }
        }

        $product->recalculateStock();

        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'ভ্যারিয়েন্ট আপডেট হয়েছে!',
                'variant' => $variant->load('images'),
            ]);
        }

        return redirect()->route('inventory.products.edit', $product)
            ->with('success', 'ভ্যারিয়েন্ট আপডেট হয়েছে!');
    }

    public function destroyVariant(Request $request, Product $product, ProductVariant $variant)
    {
        foreach ($variant->images as $image) {
            \Storage::disk('public')->delete($image->image_path);
        }

        $variant->delete();

        $product->recalculateStock();

        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'ভ্যারিয়েন্ট ডিলিট হয়েছে!',
            ]);
        }

        return redirect()->route('inventory.products.edit', $product)
            ->with('success', 'ভ্যারিয়েন্ট ডিলিট হয়েছে!');
    }
}
