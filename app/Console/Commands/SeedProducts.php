<?php

namespace App\Console\Commands;

use App\Models\Product;
use App\Models\ProductImage;
use App\Models\Category;
use App\Models\Brand;
use Illuminate\Console\Command;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;
use Stancl\Tenancy\Facades\Tenancy;

class SeedProducts extends Command
{
    protected $signature = 'tenants:seed-products';
    protected $description = 'Seed products for noyan tenant from /home/noyan/Downloads/Product';

    private array $categoryMap = [
        'Mans Shirt' => ['parent_slug' => 'men', 'slug' => 'men-shirts', 'name' => 'Shirts'],
        'T-Shirt'    => ['parent_slug' => 'men', 'slug' => 'men-tshirts', 'name' => 'T-Shirts'],
        'Polo Shirt' => ['parent_slug' => 'men', 'slug' => 'men-polo', 'name' => 'Polo Shirts'],
        'Formal Pant'=> ['parent_slug' => 'men', 'slug' => 'men-pants', 'name' => 'Pants'],
        'Panjabi'    => ['parent_slug' => 'men', 'slug' => 'men-panjabi', 'name' => 'Panjabi'],
    ];

    private array $productNames = [
        'Mans Shirt' => [
            'Classic Formal Shirt', 'Casual Check Shirt', 'Striped Cotton Shirt',
            'Solid Linen Shirt', 'Printed Summer Shirt', 'Denim Shirt',
            'Flannel Plaid Shirt', 'Oxford Button Down', 'Chambray Shirt',
            'Hawaiian Print Shirt', 'Pinstripe Shirt', 'Mandarin Collar Shirt',
            'Slim Fit Shirt', 'Regular Fit Shirt', 'Textured Weave Shirt',
            'Embroidered Shirt', 'Geometric Print Shirt', 'Color Block Shirt',
            'Micro Pattern Shirt', 'Patchwork Shirt',
        ],
        'T-Shirt' => [
            'Classic Crew Neck Tee', 'V-Neck Cotton Tee', 'Graphic Print Tee',
            'Oversized Street Tee', 'Pocket Detail Tee', 'Striped Casual Tee',
            'Solid Basic Tee', 'Vintage Wash Tee', 'Longline Tee',
            'Henley Neck Tee',
        ],
        'Polo Shirt' => [
            'Classic Polo', 'Slim Fit Polo', 'Striped Polo',
            'Color Block Polo', 'Textured Polo', 'Pique Cotton Polo',
            'Contrast Collar Polo', 'Logo Embroidered Polo', 'Two Tone Polo',
            'Performance Polo',
        ],
        'Formal Pant' => [
            'Classic Formal Pant', 'Slim Fit Chino', 'Pleated Dress Pant',
            'Flat Front Trouser', 'Tapered Fit Pant', 'Wrinkle Free Pant',
            'Cotton Blend Pant', 'Stretch Formal Pant', 'High Waist Trouser',
            'Relaxed Fit Pant',
        ],
        'Panjabi' => [
            'Classic Panjabi', 'Embroidered Panjabi', 'Printed Cotton Panabi',
            'Silk Blend Panjabi', 'Kantha Stitch Panjabi', 'Indigo Panjabi',
            'Muslin Panjabi', 'Linen Panjabi', 'Block Print Panjabi',
            'Solid Cotton Panjabi',
        ],
    ];

    private array $prices = [
        'Mans Shirt' => ['base' => 1200, 'discount' => 999],
        'T-Shirt'    => ['base' => 800, 'discount' => 650],
        'Polo Shirt' => ['base' => 1000, 'discount' => 850],
        'Formal Pant'=> ['base' => 1500, 'discount' => 1299],
        'Panjabi'    => ['base' => 1800, 'discount' => 1500],
    ];

    private array $filePrefixMap = [
        'Mans Shirt' => 'shirt',
        'T-Shirt'    => 'tshirt',
        'Polo Shirt' => 'polo',
        'Formal Pant'=> 'pant',
        'Panjabi'    => 'panjabi',
    ];

    public function handle(): int
    {
        $tenant = app(\Stancl\Tenancy\Tenancy::class)->find('noyan');
        if (!$tenant) {
            $this->error('Tenant "noyan" not found.');
            return 1;
        }
        Tenancy::initialize($tenant);

        $this->info('Connected to tenant: noyan');

        $this->deleteExistingProducts();

        $brand = Brand::first();
        if (!$brand) {
            $this->error('No brand found. Please seed brands first.');
            return 1;
        }
        $this->info("Using brand: {$brand->name}");

        $basePath = '/tmp/product-images';
        $productCount = 0;
        $globalIndex = 0;

        foreach ($this->categoryMap as $folder => $catInfo) {
            $folderPath = "{$basePath}/{$folder}";
            if (!is_dir($folderPath)) {
                $this->warn("Folder not found: {$folderPath}, skipping.");
                continue;
            }

            $images = collect(scandir($folderPath))
                ->filter(fn($f) => in_array(strtolower(pathinfo($f, PATHINFO_EXTENSION)), ['jpg', 'jpeg', 'png', 'webp']))
                ->values();

            if ($images->isEmpty()) {
                $this->warn("No images found in {$folder}, skipping.");
                continue;
            }

            $category = Category::where('slug', $catInfo['slug'])->first();
            if (!$category) {
                $parent = Category::where('slug', $catInfo['parent_slug'])->first();
                $category = Category::create([
                    'name' => $catInfo['name'],
                    'slug' => $catInfo['slug'],
                    'parent_id' => $parent?->id,
                ]);
                $this->info("Created category: {$category->name} (ID: {$category->id})");
            }

            $names = $this->productNames[$folder] ?? [];
            $price = $this->prices[$folder] ?? ['base' => 1000, 'discount' => 800];

            $this->info("Processing {$folder} ({$images->count()} images) → {$category->name}");

            foreach ($images as $index => $imageFile) {
                $name = $names[$index] ?? "{$folder} " . ($index + 1);
                $slug = Str::slug($name);
                $sku = strtoupper(substr($slug, 0, 3)) . '-' . str_pad($index + 1, 3, '0', STR_PAD_LEFT);
                $diskPath = "products/{$this->filePrefixMap[$folder]}_{$index}.png";
                $fullDestPath = Storage::disk('public')->path($diskPath);

                $product = Product::create([
                    'category_id' => $category->id,
                    'brand_id' => $brand->id,
                    'name' => $name,
                    'slug' => $slug . '-' . Str::random(4),
                    'sku' => $sku . '-' . Str::random(3),
                    'description' => "High quality {$name} from our collection.",
                    'base_price' => $price['base'],
                    'discount_price' => $price['discount'],
                    'stock_quantity' => rand(10, 50),
                    'unit' => 'pcs',
                    'status' => 'active',
                    'is_featured' => $index < 3,
                    'sort_order' => $index,
                ]);

                $sourcePath = "/tmp/product-images/{$folder}/{$imageFile}";

                ProductImage::create([
                    'product_id' => $product->id,
                    'image_path' => $diskPath,
                    'alt_text' => $name,
                    'sort_order' => 0,
                ]);

                $productCount++;
                $globalIndex++;
                $this->line("  ✓ [{$product->id}] {$name} ({$imageFile}) → {$diskPath}");
            }
        }

        Tenancy::end();

        $this->info("Done! Created {$productCount} products across " . count($this->categoryMap) . " categories.");
        return 0;
    }

    private function deleteExistingProducts(): void
    {
        $products = Product::with('images')->get();
        $count = $products->count();

        if ($count === 0) {
            $this->info('No existing products to delete.');
            return;
        }

        $this->info("Deleting {$count} existing products...");

        $productIds = $products->pluck('id')->toArray();

        \DB::table('stock_movements')->whereIn('product_id', $productIds)->delete();
        \DB::table('inventory_alerts')->whereIn('product_id', $productIds)->delete();
        \DB::table('product_attribute_values')->whereIn('product_id', $productIds)->delete();

        $variantIds = \DB::table('product_variants')->whereIn('product_id', $productIds)->pluck('id')->toArray();
        if (!empty($variantIds)) {
            \DB::table('variant_images')->whereIn('variant_id', $variantIds)->delete();
            \DB::table('variant_attribute_values')->whereIn('variant_id', $variantIds)->delete();
            \DB::table('product_variants')->whereIn('id', $variantIds)->delete();
        }

        foreach ($products as $product) {
            foreach ($product->images as $image) {
                $diskPath = Storage::disk('public')->path($image->image_path);
                if (file_exists($diskPath)) {
                    unlink($diskPath);
                }
                // Also try tenant-specific path
                $tenantPath = storage_path("app/public/{$image->image_path}");
                if (file_exists($tenantPath)) {
                    unlink($tenantPath);
                }
            }
        }

        Product::whereIn('id', $productIds)->delete();

        $this->info("Deleted {$count} products and their images.");
    }
}
