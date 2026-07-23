<?php

namespace App\Http\Controllers;

use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use App\Models\StorefrontSettings;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class StorefrontApiController extends Controller
{
    /**
     * Get storefront config (theme + business info)
     */
    public function config()
    {
        $storefront = StorefrontSettings::first();

        if (!$storefront) {
            return response()->json([
                'store_name' => config('app.name'),
                'theme' => null,
            ]);
        }

        return response()->json([
            'store_name' => $storefront->store_name ?? config('app.name'),
            'store_logo' => $storefront->store_logo ? Storage::disk('public')->url($storefront->store_logo) : null,
            'store_favicon' => $storefront->store_favicon ? Storage::disk('public')->url($storefront->store_favicon) : null,
            'theme' => [
                'slug' => $storefront->theme_slug,
                'config' => $storefront->resolvedTheme(),
            ],
            'layout' => [
                'style' => $storefront->layout_style,
                'products_per_row' => $storefront->products_per_row,
                'products_per_row_mobile' => $storefront->products_per_row_mobile,
            ],
            'sections' => [
                'show_header_slider' => $storefront->show_header_slider,
                'show_brands_section' => $storefront->show_brands_section,
                'show_newsletter' => $storefront->show_newsletter,
            ],
            'contact' => [
                'phone' => $storefront->contact_phone,
                'email' => $storefront->contact_email,
                'address' => $storefront->contact_address,
            ],
            'social' => [
                'facebook' => $storefront->facebook_url,
                'instagram' => $storefront->instagram_url,
                'youtube' => $storefront->youtube_url,
                'whatsapp' => $storefront->whatsapp_number,
            ],
            'footer' => [
                'about_text' => $storefront->footer_about_text,
                'logo' => $storefront->footer_logo,
                'copyright' => $storefront->footer_copyright_text,
            ],
        ]);
    }

    /**
     * Get all home page data (combined)
     */
    public function home()
    {
        $storefront = StorefrontSettings::first();

        // Active banners (from sections_data JSON)
        $banners = collect();
        if ($storefront && $storefront->sections_data) {
            $raw = $storefront->sections_data['banners'] ?? [];
            $banners = collect($raw)->filter(fn($b) => $b['is_active'] ?? true)
                ->sortBy('sort_order')
                ->values();
        }

        $productWith = ['category', 'brand', 'images', 'variants.attributeValues.attributeTemplate', 'variants.images'];

        // Featured products
        $featuredProducts = Product::active()
            ->featured()
            ->with($productWith)
            ->limit(8)
            ->get()
            ->map(fn($product) => $this->formatProduct($product, true));

        // New arrivals (latest 10)
        $newArrivals = Product::active()
            ->with($productWith)
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get()
            ->map(fn($product) => $this->formatProduct($product, true));

        // Category products (from editor settings with per-category config)
        $editorCategoryProducts = $storefront?->sections_data['category_products'] ?? null;
        $categoryProducts = collect();

        if ($editorCategoryProducts && !empty($editorCategoryProducts['categories'])) {
            foreach ($editorCategoryProducts['categories'] as $catConfig) {
                $products = Product::active()
                    ->whereHas('category', fn($q) => $q->where('id', $catConfig['id']))
                    ->with($productWith)
                    ->limit($catConfig['product_count'] ?? 4)
                    ->get()
                    ->map(fn($product) => $this->formatProduct($product, true));

                $categoryProducts->push([
                    'id' => $catConfig['id'],
                    'name' => $catConfig['name'] ?? '',
                    'slug' => $catConfig['slug'] ?? '',
                    'banner_image' => $catConfig['banner_image'] ?? null,
                    'product_count' => $catConfig['product_count'] ?? 4,
                    'products' => $products,
                ]);
            }
        } else {
            // Fallback: first parent category
            $firstCategory = Category::where('is_active', true)->whereNull('parent_id')->orderBy('sort_order')->first();
            $fallbackSlug = $firstCategory?->slug;
            $fallbackName = $firstCategory?->name ?? '';
            if ($fallbackSlug) {
                $products = Product::active()
                    ->whereHas('category', fn($q) => $q->where('slug', $fallbackSlug))
                    ->with($productWith)
                    ->limit(10)
                    ->get()
                    ->map(fn($product) => $this->formatProduct($product, true));

                $categoryProducts->push([
                    'id' => $firstCategory?->id,
                    'name' => $fallbackName,
                    'slug' => $fallbackSlug,
                    'banner_image' => null,
                    'product_count' => 10,
                    'products' => $products,
                ]);
            }
        }

        // Categories (from editor settings if set, otherwise DB)
        $editorCategories = $storefront?->sections_data['categories'] ?? [];
        if (!empty($editorCategories)) {
            $categories = $editorCategories;
        } else {
            $categories = Category::where('is_active', true)
                ->whereNull('parent_id')
                ->orderBy('sort_order')
                ->limit(6)
                ->get()
                ->map(fn($cat) => [
                    'id' => $cat->id,
                    'name' => $cat->name,
                    'slug' => $cat->slug,
                    'image' => $cat->image ? Storage::disk('public')->url($cat->image) : null,
                    'products_count' => $cat->products()->active()->count(),
                ]);
        }

        // Active brands
        $brands = Brand::where('is_active', true)
            ->orderBy('name')
            ->get()
            ->map(fn($brand) => [
                'id' => $brand->id,
                'name' => $brand->name,
                'slug' => $brand->slug,
                'logo' => $brand->logo,
            ]);

        // All categories (from editor settings if set, otherwise DB)
        $editorAllCategories = $storefront?->sections_data['all_categories'] ?? [];
        if (!empty($editorAllCategories)) {
            $allCategories = $editorAllCategories;
        } else {
            $allCategories = $categories;
        }

        return response()->json([
            'banners' => $banners,
            'featured_products' => $featuredProducts,
            'new_arrivals' => $newArrivals,
            'category_products' => $categoryProducts,
            'categories' => $categories,
            'all_categories' => $allCategories,
            'section_titles' => $storefront?->sections_data['section_titles'] ?? [],
            'category_banner' => isset($storefront->sections_data['category_banner']) ? $storefront->sections_data['category_banner'] : null,
            'notices' => isset($storefront->sections_data['notices']) ? $storefront->sections_data['notices'] : null,
            'brands' => $brands,
        ]);
    }

    /**
     * Product list with pagination and filters
     */
    public function products(Request $request)
    {
        $query = Product::active()->with(['category', 'brand', 'images']);

        // Search
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhereHas('category', fn($cq) => $cq->where('name', 'like', "%{$search}%"))
                  ->orWhereHas('brand', fn($bq) => $bq->where('name', 'like', "%{$search}%"));
            });
        }

        // Filters (comma-separated for multiple values)
        if ($request->filled('category')) {
            $categorySlugs = explode(',', $request->category);
            $categoryIds = Category::whereIn('slug', $categorySlugs)
                ->orWhereHas('parent', fn($q) => $q->whereIn('slug', $categorySlugs))
                ->pluck('id');
            $query->whereIn('category_id', $categoryIds);
        }

        if ($request->filled('brand')) {
            $brands = explode(',', $request->brand);
            $query->whereHas('brand', fn($q) => $q->whereIn('slug', $brands));
        }

        if ($request->filled('min_price')) {
            $query->where('base_price', '>=', $request->min_price);
        }

        if ($request->filled('max_price')) {
            $query->where('base_price', '<=', $request->max_price);
        }

        // Sort
        switch ($request->get('sort', 'newest')) {
            case 'price_asc':
                $query->orderBy('base_price', 'asc');
                break;
            case 'price_desc':
                $query->orderBy('base_price', 'desc');
                break;
            case 'newest':
            default:
                $query->orderBy('created_at', 'desc');
                break;
        }

        $perPage = $request->get('per_page', 12);
        $products = $query->paginate($perPage);

        return response()->json([
            'data' => $products->getCollection()->map(fn($product) => $this->formatProduct($product)),
            'meta' => [
                'current_page' => $products->currentPage(),
                'last_page' => $products->lastPage(),
                'per_page' => $products->perPage(),
                'total' => $products->total(),
            ],
        ]);
    }

    /**
     * Single product detail
     */
    public function product(string $slug)
    {
        $product = Product::active()
            ->where('slug', $slug)
            ->with(['category', 'brand', 'images', 'variants.images', 'attributeValues.attributeTemplate'])
            ->firstOrFail();

        return response()->json([
            'data' => $this->formatProduct($product, true),
        ]);
    }

    /**
     * All active categories (parent + children)
     */
    public function categories()
    {
        $categories = Category::where('is_active', true)
            ->whereNull('parent_id')
            ->with(['children' => fn($q) => $q->where('is_active', true)->orderBy('sort_order')])
            ->withCount(['products' => fn($q) => $q->active()])
            ->orderBy('sort_order')
            ->get()
            ->map(fn($cat) => [
                'id' => $cat->id,
                'name' => $cat->name,
                'slug' => $cat->slug,
                'image' => $cat->image ? Storage::disk('public')->url($cat->image) : null,
                'products_count' => (int) $cat->products_count,
                'children' => $cat->children->map(fn($child) => [
                    'id' => $child->id,
                    'name' => $child->name,
                    'slug' => $child->slug,
                    'image' => $child->image ? Storage::disk('public')->url($child->image) : null,
                    'products_count' => (int) $child->products()->active()->count(),
                ]),
            ]);

        return response()->json($categories);
    }

    /**
     * All active brands
     */
    public function brands()
    {
        $brands = Brand::where('is_active', true)
            ->withCount(['products' => fn($q) => $q->active()])
            ->orderBy('name')
            ->get();

        return response()->json($brands);
    }

    /**
     * Related products based on same category
     */
    public function relatedProducts(string $slug)
    {
        $product = Product::active()->where('slug', $slug)->firstOrFail();

        $related = Product::active()
            ->where('id', '!=', $product->id)
            ->where('category_id', $product->category_id)
            ->with(['category', 'brand', 'images', 'variants.images'])
            ->limit(8)
            ->get()
            ->map(fn($p) => $this->formatProduct($p, true))
            ->values();

        // If not enough in same category, fallback to latest products
        if ($related->count() < 4) {
            $excludeIds = collect([$product->id])->merge($related->pluck('id'));
            $fallback = Product::active()
                ->whereNotIn('id', $excludeIds)
                ->with(['category', 'brand', 'images', 'variants.images'])
                ->inRandomOrder()
                ->limit(8 - $related->count())
                ->get()
                ->map(fn($p) => $this->formatProduct($p, true));

            $related = $related->concat($fallback)->values();
        }

        return response()->json($related);
    }

    /**
     * Featured products only
     */
    public function featured()
    {
        $products = Product::active()
            ->featured()
            ->with(['category', 'brand', 'images'])
            ->limit(8)
            ->get()
            ->map(fn($product) => $this->formatProduct($product));

        return response()->json($products);
    }

    /**
     * Format product for API response
     */
    private function formatProduct(Product $product, bool $detailed = false): array
    {
        $data = [
            'id' => $product->id,
            'name' => $product->name,
            'slug' => $product->slug,
            'sku' => $product->sku,
            'description' => $product->description,
            'base_price' => $product->base_price,
            'discount_price' => $product->discount_price,
            'effective_price' => $product->effective_price,
            'stock_quantity' => $product->total_stock,
            'status' => $product->status,
            'is_featured' => $product->is_featured,
            'category' => $product->category ? [
                'id' => $product->category->id,
                'name' => $product->category->name,
                'slug' => $product->category->slug,
            ] : null,
            'brand' => $product->brand ? [
                'id' => $product->brand->id,
                'name' => $product->brand->name,
                'slug' => $product->brand->slug,
            ] : null,
            'image' => $product->images->first()?->image_url ?? null,
            'images' => $product->images->map(fn($img) => $img->image_url),
        ];

        if ($detailed) {
            $variants = $product->variants->map(fn($variant) => [
                'id' => $variant->id,
                'name' => $variant->name,
                'sku' => $variant->sku,
                'price' => $variant->price,
                'stock_quantity' => $variant->stock_quantity,
                'stock' => $variant->stock_quantity,
                'attributes' => $variant->attributeValues->map(fn($attr) => [
                    'attribute' => $attr->attributeTemplate->name ?? $attr->attribute_template_id,
                    'value' => $attr->value,
                ]),
                'image' => $variant->images->first()?->image_url ?? null,
            ]);

            $variants = $variants->map(fn($v) => array_merge(
                $v,
                collect($v['attributes'])->mapWithKeys(fn($a) => [strtolower($a['attribute']) => $a['value']])->toArray()
            ));

            $data['variants'] = $variants;

            $data['attributes'] = $product->attributeValues->map(fn($attr) => [
                'attribute' => $attr->attributeTemplate->name ?? $attr->attribute_template_id,
                'value' => $attr->value,
            ]);
        }

        return $data;
    }
}