<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Str;
use App\Models\Category;
use App\Models\Brand;
use App\Models\AttributeTemplate;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\ProductAttributeValue;
use App\Models\VariantAttributeValue;
use App\Models\Warehouse;
use App\Models\StockMovement;
use App\Models\InventoryAlert;

class ClothingInventorySeeder extends Seeder
{
    public function run(): void
    {
        $this->command->info("🗑️  Purono data mukhya kore notun data add hocche...");

        // ===== PURONO DATA REMOVE =====
        StockMovement::query()->delete();
        InventoryAlert::query()->delete();
        ProductVariant::query()->delete();
        ProductAttributeValue::query()->delete();
        Product::query()->delete();
        AttributeTemplate::query()->delete();
        Category::query()->delete();
        Brand::query()->delete();
        Warehouse::query()->delete();

        $this->command->info("✅ Purono data mukhya hoyeche!");

        $now = now();

        // ===== BRANDS =====
        $fashionbd = Brand::create(['name' => 'FashionBD', 'slug' => 'fashionbd', 'is_active' => true]);
        $stylehub = Brand::create(['name' => 'StyleHub', 'slug' => 'stylehub', 'is_active' => true]);
        $urban = Brand::create(['name' => 'Urban Wear', 'slug' => 'urban-wear', 'is_active' => true]);
        $trendy = Brand::create(['name' => 'Trendy Collection', 'slug' => 'trendy-collection', 'is_active' => true]);
        $elegant = Brand::create(['name' => 'Elegant Style', 'slug' => 'elegant-style', 'is_active' => true]);

        // ===== CATEGORIES (with subcategories) =====
        $men = Category::create(['name' => 'Men', 'slug' => 'men', 'description' => "Men's Clothing", 'sort_order' => 1]);
        $menShirt = Category::create(['name' => 'Shirts', 'slug' => 'men-shirts', 'parent_id' => $men->id, 'sort_order' => 1]);
        $menTshirt = Category::create(['name' => 'T-Shirts', 'slug' => 'men-tshirts', 'parent_id' => $men->id, 'sort_order' => 2]);
        $menPant = Category::create(['name' => 'Pants', 'slug' => 'men-pants', 'parent_id' => $men->id, 'sort_order' => 3]);
        $menPolo = Category::create(['name' => 'Polo Shirts', 'slug' => 'men-polo', 'parent_id' => $men->id, 'sort_order' => 4]);
        $menJacket = Category::create(['name' => 'Jackets', 'slug' => 'men-jackets', 'parent_id' => $men->id, 'sort_order' => 5]);

        $women = Category::create(['name' => 'Women', 'slug' => 'women', 'description' => "Women's Clothing", 'sort_order' => 2]);
        $womenKurti = Category::create(['name' => 'Kurti', 'slug' => 'women-kurti', 'parent_id' => $women->id, 'sort_order' => 1]);
        $womenSaree = Category::create(['name' => 'Saree', 'slug' => 'women-saree', 'parent_id' => $women->id, 'sort_order' => 2]);
        $womenSalwar = Category::create(['name' => 'Salwar Kameez', 'slug' => 'women-salwar', 'parent_id' => $women->id, 'sort_order' => 3]);
        $womenTshirt = Category::create(['name' => 'T-Shirts', 'slug' => 'women-tshirts', 'parent_id' => $women->id, 'sort_order' => 4]);
        $womenFrock = Category::create(['name' => 'Frocks', 'slug' => 'women-frocks', 'parent_id' => $women->id, 'sort_order' => 5]);

        $kids = Category::create(['name' => 'Kids', 'slug' => 'kids', 'description' => "Kids' Clothing", 'sort_order' => 3]);
        $kidsTshirt = Category::create(['name' => 'T-Shirts', 'slug' => 'kids-tshirts', 'parent_id' => $kids->id, 'sort_order' => 1]);
        $kidsPant = Category::create(['name' => 'Pants', 'slug' => 'kids-pants', 'parent_id' => $kids->id, 'sort_order' => 2]);
        $kidsFrock = Category::create(['name' => 'Frocks', 'slug' => 'kids-frocks', 'parent_id' => $kids->id, 'sort_order' => 3]);

        $acc = Category::create(['name' => 'Accessories', 'slug' => 'accessories', 'description' => 'Fashion Accessories', 'sort_order' => 4]);
        $accBag = Category::create(['name' => 'Bags', 'slug' => 'acc-bags', 'parent_id' => $acc->id, 'sort_order' => 1]);
        $accCap = Category::create(['name' => 'Caps & Hats', 'slug' => 'acc-caps', 'parent_id' => $acc->id, 'sort_order' => 2]);
        $accScarf = Category::create(['name' => 'Scarves', 'slug' => 'acc-scarves', 'parent_id' => $acc->id, 'sort_order' => 3]);

        $this->command->info("✅ Categories + Brands toiri!");

        // ===== WAREHOUSES =====
        $wh1 = Warehouse::create(['name' => 'Main Warehouse - Dhaka', 'address' => 'Gulshan, Dhaka 1212', 'phone' => '+8801711000001']);
        $wh2 = Warehouse::create(['name' => 'Chittagong Warehouse', 'address' => 'Agrabad, Chittagong 4100', 'phone' => '+8801711000002']);

        // ===== PRODUCTS (Shopify-Style: Options → Variants) =====
        $products = [
            // ━━━━━ MEN'S SHIRTS ━━━━━
            [
                'category_id' => $menShirt->id,
                'brand_id' => $fashionbd->id,
                'name' => 'Classic Formal Shirt',
                'sku' => 'MSH-001',
                'description' => 'Premium cotton formal shirt for office wear',
                'base_price' => 1200.00,
                'discount_price' => 999.00,
                'status' => 'active',
                'is_featured' => true,
                'options' => [
                    ['name' => 'Color', 'values' => ['Black', 'White', 'Navy']],
                    ['name' => 'Size', 'values' => ['S', 'M', 'L', 'XL']],
                ],
                'variants' => [
                    ['sku' => 'MSH-001-BLACK-S', 'price' => 1200.00, 'stock_quantity' => 25, 'attributes' => ['Color' => 'Black', 'Size' => 'S']],
                    ['sku' => 'MSH-001-BLACK-M', 'price' => 1200.00, 'stock_quantity' => 40, 'attributes' => ['Color' => 'Black', 'Size' => 'M']],
                    ['sku' => 'MSH-001-BLACK-L', 'price' => 1200.00, 'stock_quantity' => 35, 'attributes' => ['Color' => 'Black', 'Size' => 'L']],
                    ['sku' => 'MSH-001-BLACK-XL', 'price' => 1200.00, 'stock_quantity' => 20, 'attributes' => ['Color' => 'Black', 'Size' => 'XL']],
                    ['sku' => 'MSH-001-WHITE-S', 'price' => 1200.00, 'stock_quantity' => 30, 'attributes' => ['Color' => 'White', 'Size' => 'S']],
                    ['sku' => 'MSH-001-WHITE-M', 'price' => 1200.00, 'stock_quantity' => 45, 'attributes' => ['Color' => 'White', 'Size' => 'M']],
                    ['sku' => 'MSH-001-WHITE-L', 'price' => 1200.00, 'stock_quantity' => 30, 'attributes' => ['Color' => 'White', 'Size' => 'L']],
                    ['sku' => 'MSH-001-WHITE-XL', 'price' => 1200.00, 'stock_quantity' => 15, 'attributes' => ['Color' => 'White', 'Size' => 'XL']],
                    ['sku' => 'MSH-001-NAVY-S', 'price' => 1200.00, 'stock_quantity' => 22, 'attributes' => ['Color' => 'Navy', 'Size' => 'S']],
                    ['sku' => 'MSH-001-NAVY-M', 'price' => 1200.00, 'stock_quantity' => 38, 'attributes' => ['Color' => 'Navy', 'Size' => 'M']],
                    ['sku' => 'MSH-001-NAVY-L', 'price' => 1200.00, 'stock_quantity' => 28, 'attributes' => ['Color' => 'Navy', 'Size' => 'L']],
                    ['sku' => 'MSH-001-NAVY-XL', 'price' => 1200.00, 'stock_quantity' => 18, 'attributes' => ['Color' => 'Navy', 'Size' => 'XL']],
                ],
            ],
            [
                'category_id' => $menShirt->id,
                'brand_id' => $stylehub->id,
                'name' => 'Casual Check Shirt',
                'sku' => 'MSH-002',
                'description' => 'Trendy check pattern casual shirt',
                'base_price' => 1500.00,
                'status' => 'active',
                'is_featured' => false,
                'options' => [
                    ['name' => 'Color', 'values' => ['Blue', 'Red', 'Green']],
                    ['name' => 'Size', 'values' => ['S', 'M', 'L', 'XL', 'XXL']],
                ],
                'variants' => [
                    ['sku' => 'MSH-002-BLUE-S', 'price' => 1500.00, 'stock_quantity' => 20, 'attributes' => ['Color' => 'Blue', 'Size' => 'S']],
                    ['sku' => 'MSH-002-BLUE-M', 'price' => 1500.00, 'stock_quantity' => 35, 'attributes' => ['Color' => 'Blue', 'Size' => 'M']],
                    ['sku' => 'MSH-002-BLUE-L', 'price' => 1500.00, 'stock_quantity' => 25, 'attributes' => ['Color' => 'Blue', 'Size' => 'L']],
                    ['sku' => 'MSH-002-BLUE-XL', 'price' => 1500.00, 'stock_quantity' => 15, 'attributes' => ['Color' => 'Blue', 'Size' => 'XL']],
                    ['sku' => 'MSH-002-BLUE-XXL', 'price' => 1500.00, 'stock_quantity' => 10, 'attributes' => ['Color' => 'Blue', 'Size' => 'XXL']],
                    ['sku' => 'MSH-002-RED-S', 'price' => 1500.00, 'stock_quantity' => 18, 'attributes' => ['Color' => 'Red', 'Size' => 'S']],
                    ['sku' => 'MSH-002-RED-M', 'price' => 1500.00, 'stock_quantity' => 30, 'attributes' => ['Color' => 'Red', 'Size' => 'M']],
                    ['sku' => 'MSH-002-RED-L', 'price' => 1500.00, 'stock_quantity' => 22, 'attributes' => ['Color' => 'Red', 'Size' => 'L']],
                    ['sku' => 'MSH-002-GREEN-S', 'price' => 1500.00, 'stock_quantity' => 15, 'attributes' => ['Color' => 'Green', 'Size' => 'S']],
                    ['sku' => 'MSH-002-GREEN-M', 'price' => 1500.00, 'stock_quantity' => 28, 'attributes' => ['Color' => 'Green', 'Size' => 'M']],
                    ['sku' => 'MSH-002-GREEN-L', 'price' => 1500.00, 'stock_quantity' => 20, 'attributes' => ['Color' => 'Green', 'Size' => 'L']],
                ],
            ],

            // ━━━━━ MEN'S T-SHIRTS ━━━━━
            [
                'category_id' => $menTshirt->id,
                'brand_id' => $urban->id,
                'name' => 'Urban Classic Tee',
                'sku' => 'MTS-001',
                'description' => 'Comfortable cotton t-shirt with urban design',
                'base_price' => 650.00,
                'discount_price' => 499.00,
                'status' => 'active',
                'is_featured' => true,
                'options' => [
                    ['name' => 'Color', 'values' => ['Black', 'White', 'Navy', 'Grey']],
                    ['name' => 'Size', 'values' => ['S', 'M', 'L', 'XL']],
                ],
                'variants' => [
                    ['sku' => 'MTS-001-BLACK-S', 'price' => 650.00, 'stock_quantity' => 50, 'attributes' => ['Color' => 'Black', 'Size' => 'S']],
                    ['sku' => 'MTS-001-BLACK-M', 'price' => 650.00, 'stock_quantity' => 60, 'attributes' => ['Color' => 'Black', 'Size' => 'M']],
                    ['sku' => 'MTS-001-BLACK-L', 'price' => 650.00, 'stock_quantity' => 45, 'attributes' => ['Color' => 'Black', 'Size' => 'L']],
                    ['sku' => 'MTS-001-BLACK-XL', 'price' => 650.00, 'stock_quantity' => 30, 'attributes' => ['Color' => 'Black', 'Size' => 'XL']],
                    ['sku' => 'MTS-001-WHITE-S', 'price' => 650.00, 'stock_quantity' => 40, 'attributes' => ['Color' => 'White', 'Size' => 'S']],
                    ['sku' => 'MTS-001-WHITE-M', 'price' => 650.00, 'stock_quantity' => 55, 'attributes' => ['Color' => 'White', 'Size' => 'M']],
                    ['sku' => 'MTS-001-WHITE-L', 'price' => 650.00, 'stock_quantity' => 40, 'attributes' => ['Color' => 'White', 'Size' => 'L']],
                    ['sku' => 'MTS-001-WHITE-XL', 'price' => 650.00, 'stock_quantity' => 25, 'attributes' => ['Color' => 'White', 'Size' => 'XL']],
                    ['sku' => 'MTS-001-NAVY-S', 'price' => 650.00, 'stock_quantity' => 35, 'attributes' => ['Color' => 'Navy', 'Size' => 'S']],
                    ['sku' => 'MTS-001-NAVY-M', 'price' => 650.00, 'stock_quantity' => 50, 'attributes' => ['Color' => 'Navy', 'Size' => 'M']],
                    ['sku' => 'MTS-001-NAVY-L', 'price' => 650.00, 'stock_quantity' => 30, 'attributes' => ['Color' => 'Navy', 'Size' => 'L']],
                    ['sku' => 'MTS-001-NAVY-XL', 'price' => 650.00, 'stock_quantity' => 20, 'attributes' => ['Color' => 'Navy', 'Size' => 'XL']],
                    ['sku' => 'MTS-001-GREY-S', 'price' => 650.00, 'stock_quantity' => 28, 'attributes' => ['Color' => 'Grey', 'Size' => 'S']],
                    ['sku' => 'MTS-001-GREY-M', 'price' => 650.00, 'stock_quantity' => 42, 'attributes' => ['Color' => 'Grey', 'Size' => 'M']],
                    ['sku' => 'MTS-001-GREY-L', 'price' => 650.00, 'stock_quantity' => 35, 'attributes' => ['Color' => 'Grey', 'Size' => 'L']],
                    ['sku' => 'MTS-001-GREY-XL', 'price' => 650.00, 'stock_quantity' => 18, 'attributes' => ['Color' => 'Grey', 'Size' => 'XL']],
                ],
            ],
            [
                'category_id' => $menTshirt->id,
                'brand_id' => $urban->id,
                'name' => 'Graphic Print Tee',
                'sku' => 'MTS-002',
                'description' => 'Trendy graphic print t-shirt',
                'base_price' => 750.00,
                'status' => 'active',
                'is_featured' => false,
                'options' => [
                    ['name' => 'Color', 'values' => ['Red', 'Green', 'Black']],
                    ['name' => 'Size', 'values' => ['S', 'M', 'L', 'XL']],
                ],
                'variants' => [
                    ['sku' => 'MTS-002-RED-S', 'price' => 750.00, 'stock_quantity' => 20, 'attributes' => ['Color' => 'Red', 'Size' => 'S']],
                    ['sku' => 'MTS-002-RED-M', 'price' => 750.00, 'stock_quantity' => 30, 'attributes' => ['Color' => 'Red', 'Size' => 'M']],
                    ['sku' => 'MTS-002-RED-L', 'price' => 750.00, 'stock_quantity' => 25, 'attributes' => ['Color' => 'Red', 'Size' => 'L']],
                    ['sku' => 'MTS-002-RED-XL', 'price' => 750.00, 'stock_quantity' => 15, 'attributes' => ['Color' => 'Red', 'Size' => 'XL']],
                    ['sku' => 'MTS-002-GREEN-S', 'price' => 750.00, 'stock_quantity' => 18, 'attributes' => ['Color' => 'Green', 'Size' => 'S']],
                    ['sku' => 'MTS-002-GREEN-M', 'price' => 750.00, 'stock_quantity' => 28, 'attributes' => ['Color' => 'Green', 'Size' => 'M']],
                    ['sku' => 'MTS-002-GREEN-L', 'price' => 750.00, 'stock_quantity' => 22, 'attributes' => ['Color' => 'Green', 'Size' => 'L']],
                    ['sku' => 'MTS-002-GREEN-XL', 'price' => 750.00, 'stock_quantity' => 12, 'attributes' => ['Color' => 'Green', 'Size' => 'XL']],
                    ['sku' => 'MTS-002-BLACK-S', 'price' => 750.00, 'stock_quantity' => 22, 'attributes' => ['Color' => 'Black', 'Size' => 'S']],
                    ['sku' => 'MTS-002-BLACK-M', 'price' => 750.00, 'stock_quantity' => 35, 'attributes' => ['Color' => 'Black', 'Size' => 'M']],
                    ['sku' => 'MTS-002-BLACK-L', 'price' => 750.00, 'stock_quantity' => 28, 'attributes' => ['Color' => 'Black', 'Size' => 'L']],
                    ['sku' => 'MTS-002-BLACK-XL', 'price' => 750.00, 'stock_quantity' => 18, 'attributes' => ['Color' => 'Black', 'Size' => 'XL']],
                ],
            ],

            // ━━━━━ MEN'S PANTS ━━━━━
            [
                'category_id' => $menPant->id,
                'brand_id' => $trendy->id,
                'name' => 'Slim Fit Jeans',
                'sku' => 'MPT-001',
                'description' => 'Premium denim slim fit jeans',
                'base_price' => 2200.00,
                'discount_price' => 1899.00,
                'status' => 'active',
                'is_featured' => true,
                'options' => [
                    ['name' => 'Color', 'values' => ['Blue', 'Black', 'Grey']],
                    ['name' => 'Size', 'values' => ['S', 'M', 'L', 'XL']],
                ],
                'variants' => [
                    ['sku' => 'MPT-001-BLUE-S', 'price' => 2200.00, 'stock_quantity' => 30, 'attributes' => ['Color' => 'Blue', 'Size' => 'S']],
                    ['sku' => 'MPT-001-BLUE-M', 'price' => 2200.00, 'stock_quantity' => 45, 'attributes' => ['Color' => 'Blue', 'Size' => 'M']],
                    ['sku' => 'MPT-001-BLUE-L', 'price' => 2200.00, 'stock_quantity' => 35, 'attributes' => ['Color' => 'Blue', 'Size' => 'L']],
                    ['sku' => 'MPT-001-BLUE-XL', 'price' => 2200.00, 'stock_quantity' => 20, 'attributes' => ['Color' => 'Blue', 'Size' => 'XL']],
                    ['sku' => 'MPT-001-BLACK-S', 'price' => 2200.00, 'stock_quantity' => 25, 'attributes' => ['Color' => 'Black', 'Size' => 'S']],
                    ['sku' => 'MPT-001-BLACK-M', 'price' => 2200.00, 'stock_quantity' => 40, 'attributes' => ['Color' => 'Black', 'Size' => 'M']],
                    ['sku' => 'MPT-001-BLACK-L', 'price' => 2200.00, 'stock_quantity' => 30, 'attributes' => ['Color' => 'Black', 'Size' => 'L']],
                    ['sku' => 'MPT-001-BLACK-XL', 'price' => 2200.00, 'stock_quantity' => 18, 'attributes' => ['Color' => 'Black', 'Size' => 'XL']],
                    ['sku' => 'MPT-001-GREY-S', 'price' => 2200.00, 'stock_quantity' => 22, 'attributes' => ['Color' => 'Grey', 'Size' => 'S']],
                    ['sku' => 'MPT-001-GREY-M', 'price' => 2200.00, 'stock_quantity' => 35, 'attributes' => ['Color' => 'Grey', 'Size' => 'M']],
                    ['sku' => 'MPT-001-GREY-L', 'price' => 2200.00, 'stock_quantity' => 28, 'attributes' => ['Color' => 'Grey', 'Size' => 'L']],
                    ['sku' => 'MPT-001-GREY-XL', 'price' => 2200.00, 'stock_quantity' => 15, 'attributes' => ['Color' => 'Grey', 'Size' => 'XL']],
                ],
            ],
            [
                'category_id' => $menPant->id,
                'brand_id' => $trendy->id,
                'name' => 'Chino Pants',
                'sku' => 'MPT-002',
                'description' => 'Comfortable chino pants for casual wear',
                'base_price' => 1800.00,
                'status' => 'active',
                'is_featured' => false,
                'options' => [
                    ['name' => 'Color', 'values' => ['Khaki', 'Navy', 'Olive']],
                    ['name' => 'Size', 'values' => ['S', 'M', 'L', 'XL']],
                ],
                'variants' => [
                    ['sku' => 'MPT-002-KHAKI-S', 'price' => 1800.00, 'stock_quantity' => 22, 'attributes' => ['Color' => 'Khaki', 'Size' => 'S']],
                    ['sku' => 'MPT-002-KHAKI-M', 'price' => 1800.00, 'stock_quantity' => 35, 'attributes' => ['Color' => 'Khaki', 'Size' => 'M']],
                    ['sku' => 'MPT-002-KHAKI-L', 'price' => 1800.00, 'stock_quantity' => 28, 'attributes' => ['Color' => 'Khaki', 'Size' => 'L']],
                    ['sku' => 'MPT-002-KHAKI-XL', 'price' => 1800.00, 'stock_quantity' => 15, 'attributes' => ['Color' => 'Khaki', 'Size' => 'XL']],
                    ['sku' => 'MPT-002-NAVY-S', 'price' => 1800.00, 'stock_quantity' => 20, 'attributes' => ['Color' => 'Navy', 'Size' => 'S']],
                    ['sku' => 'MPT-002-NAVY-M', 'price' => 1800.00, 'stock_quantity' => 32, 'attributes' => ['Color' => 'Navy', 'Size' => 'M']],
                    ['sku' => 'MPT-002-NAVY-L', 'price' => 1800.00, 'stock_quantity' => 25, 'attributes' => ['Color' => 'Navy', 'Size' => 'L']],
                    ['sku' => 'MPT-002-OLIVE-S', 'price' => 1800.00, 'stock_quantity' => 18, 'attributes' => ['Color' => 'Olive', 'Size' => 'S']],
                    ['sku' => 'MPT-002-OLIVE-M', 'price' => 1800.00, 'stock_quantity' => 30, 'attributes' => ['Color' => 'Olive', 'Size' => 'M']],
                    ['sku' => 'MPT-002-OLIVE-L', 'price' => 1800.00, 'stock_quantity' => 22, 'attributes' => ['Color' => 'Olive', 'Size' => 'L']],
                ],
            ],

            // ━━━━━ MEN'S POLO ━━━━━
            [
                'category_id' => $menPolo->id,
                'brand_id' => $stylehub->id,
                'name' => 'Classic Polo',
                'sku' => 'MPL-001',
                'description' => 'Premium cotton polo shirt',
                'base_price' => 1100.00,
                'discount_price' => 899.00,
                'status' => 'active',
                'is_featured' => true,
                'options' => [
                    ['name' => 'Color', 'values' => ['Black', 'White', 'Navy', 'Red']],
                    ['name' => 'Size', 'values' => ['S', 'M', 'L', 'XL']],
                ],
                'variants' => [
                    ['sku' => 'MPL-001-BLACK-S', 'price' => 1100.00, 'stock_quantity' => 30, 'attributes' => ['Color' => 'Black', 'Size' => 'S']],
                    ['sku' => 'MPL-001-BLACK-M', 'price' => 1100.00, 'stock_quantity' => 45, 'attributes' => ['Color' => 'Black', 'Size' => 'M']],
                    ['sku' => 'MPL-001-BLACK-L', 'price' => 1100.00, 'stock_quantity' => 35, 'attributes' => ['Color' => 'Black', 'Size' => 'L']],
                    ['sku' => 'MPL-001-BLACK-XL', 'price' => 1100.00, 'stock_quantity' => 20, 'attributes' => ['Color' => 'Black', 'Size' => 'XL']],
                    ['sku' => 'MPL-001-WHITE-S', 'price' => 1100.00, 'stock_quantity' => 25, 'attributes' => ['Color' => 'White', 'Size' => 'S']],
                    ['sku' => 'MPL-001-WHITE-M', 'price' => 1100.00, 'stock_quantity' => 40, 'attributes' => ['Color' => 'White', 'Size' => 'M']],
                    ['sku' => 'MPL-001-WHITE-L', 'price' => 1100.00, 'stock_quantity' => 30, 'attributes' => ['Color' => 'White', 'Size' => 'L']],
                    ['sku' => 'MPL-001-WHITE-XL', 'price' => 1100.00, 'stock_quantity' => 18, 'attributes' => ['Color' => 'White', 'Size' => 'XL']],
                    ['sku' => 'MPL-001-NAVY-S', 'price' => 1100.00, 'stock_quantity' => 22, 'attributes' => ['Color' => 'Navy', 'Size' => 'S']],
                    ['sku' => 'MPL-001-NAVY-M', 'price' => 1100.00, 'stock_quantity' => 38, 'attributes' => ['Color' => 'Navy', 'Size' => 'M']],
                    ['sku' => 'MPL-001-RED-S', 'price' => 1100.00, 'stock_quantity' => 20, 'attributes' => ['Color' => 'Red', 'Size' => 'S']],
                    ['sku' => 'MPL-001-RED-M', 'price' => 1100.00, 'stock_quantity' => 35, 'attributes' => ['Color' => 'Red', 'Size' => 'M']],
                ],
            ],

            // ━━━━━ MEN'S JACKETS ━━━━━
            [
                'category_id' => $menJacket->id,
                'brand_id' => $elegant->id,
                'name' => 'Denim Jacket',
                'sku' => 'MJT-001',
                'description' => 'Classic denim jacket for all seasons',
                'base_price' => 3500.00,
                'discount_price' => 2999.00,
                'status' => 'active',
                'is_featured' => true,
                'options' => [
                    ['name' => 'Color', 'values' => ['Blue', 'Black']],
                    ['name' => 'Size', 'values' => ['M', 'L', 'XL']],
                ],
                'variants' => [
                    ['sku' => 'MJT-001-BLUE-M', 'price' => 3500.00, 'stock_quantity' => 15, 'attributes' => ['Color' => 'Blue', 'Size' => 'M']],
                    ['sku' => 'MJT-001-BLUE-L', 'price' => 3500.00, 'stock_quantity' => 20, 'attributes' => ['Color' => 'Blue', 'Size' => 'L']],
                    ['sku' => 'MJT-001-BLUE-XL', 'price' => 3500.00, 'stock_quantity' => 12, 'attributes' => ['Color' => 'Blue', 'Size' => 'XL']],
                    ['sku' => 'MJT-001-BLACK-M', 'price' => 3500.00, 'stock_quantity' => 18, 'attributes' => ['Color' => 'Black', 'Size' => 'M']],
                    ['sku' => 'MJT-001-BLACK-L', 'price' => 3500.00, 'stock_quantity' => 22, 'attributes' => ['Color' => 'Black', 'Size' => 'L']],
                    ['sku' => 'MJT-001-BLACK-XL', 'price' => 3500.00, 'stock_quantity' => 10, 'attributes' => ['Color' => 'Black', 'Size' => 'XL']],
                ],
            ],

            // ━━━━━ WOMEN'S KURTI ━━━━━
            [
                'category_id' => $womenKurti->id,
                'brand_id' => $elegant->id,
                'name' => 'Printed Cotton Kurti',
                'sku' => 'WKU-001',
                'description' => 'Beautiful printed cotton kurti for daily wear',
                'base_price' => 1400.00,
                'discount_price' => 1199.00,
                'status' => 'active',
                'is_featured' => true,
                'options' => [
                    ['name' => 'Color', 'values' => ['Blue', 'Red', 'Green', 'Pink']],
                    ['name' => 'Size', 'values' => ['S', 'M', 'L', 'XL']],
                ],
                'variants' => [
                    ['sku' => 'WKU-001-BLUE-S', 'price' => 1400.00, 'stock_quantity' => 25, 'attributes' => ['Color' => 'Blue', 'Size' => 'S']],
                    ['sku' => 'WKU-001-BLUE-M', 'price' => 1400.00, 'stock_quantity' => 40, 'attributes' => ['Color' => 'Blue', 'Size' => 'M']],
                    ['sku' => 'WKU-001-BLUE-L', 'price' => 1400.00, 'stock_quantity' => 30, 'attributes' => ['Color' => 'Blue', 'Size' => 'L']],
                    ['sku' => 'WKU-001-BLUE-XL', 'price' => 1400.00, 'stock_quantity' => 15, 'attributes' => ['Color' => 'Blue', 'Size' => 'XL']],
                    ['sku' => 'WKU-001-RED-S', 'price' => 1400.00, 'stock_quantity' => 20, 'attributes' => ['Color' => 'Red', 'Size' => 'S']],
                    ['sku' => 'WKU-001-RED-M', 'price' => 1400.00, 'stock_quantity' => 35, 'attributes' => ['Color' => 'Red', 'Size' => 'M']],
                    ['sku' => 'WKU-001-RED-L', 'price' => 1400.00, 'stock_quantity' => 28, 'attributes' => ['Color' => 'Red', 'Size' => 'L']],
                    ['sku' => 'WKU-001-GREEN-S', 'price' => 1400.00, 'stock_quantity' => 18, 'attributes' => ['Color' => 'Green', 'Size' => 'S']],
                    ['sku' => 'WKU-001-GREEN-M', 'price' => 1400.00, 'stock_quantity' => 32, 'attributes' => ['Color' => 'Green', 'Size' => 'M']],
                    ['sku' => 'WKU-001-PINK-S', 'price' => 1400.00, 'stock_quantity' => 22, 'attributes' => ['Color' => 'Pink', 'Size' => 'S']],
                    ['sku' => 'WKU-001-PINK-M', 'price' => 1400.00, 'stock_quantity' => 38, 'attributes' => ['Color' => 'Pink', 'Size' => 'M']],
                ],
            ],
            [
                'category_id' => $womenKurti->id,
                'brand_id' => $elegant->id,
                'name' => 'Embroidered Kurti',
                'sku' => 'WKU-002',
                'description' => 'Elegant embroidered kurti for special occasions',
                'base_price' => 2500.00,
                'status' => 'active',
                'is_featured' => false,
                'options' => [
                    ['name' => 'Color', 'values' => ['Pink', 'Maroon', 'Navy']],
                    ['name' => 'Size', 'values' => ['S', 'M', 'L']],
                ],
                'variants' => [
                    ['sku' => 'WKU-002-PINK-S', 'price' => 2500.00, 'stock_quantity' => 15, 'attributes' => ['Color' => 'Pink', 'Size' => 'S']],
                    ['sku' => 'WKU-002-PINK-M', 'price' => 2500.00, 'stock_quantity' => 25, 'attributes' => ['Color' => 'Pink', 'Size' => 'M']],
                    ['sku' => 'WKU-002-PINK-L', 'price' => 2500.00, 'stock_quantity' => 20, 'attributes' => ['Color' => 'Pink', 'Size' => 'L']],
                    ['sku' => 'WKU-002-MAROON-S', 'price' => 2500.00, 'stock_quantity' => 12, 'attributes' => ['Color' => 'Maroon', 'Size' => 'S']],
                    ['sku' => 'WKU-002-MAROON-M', 'price' => 2500.00, 'stock_quantity' => 22, 'attributes' => ['Color' => 'Maroon', 'Size' => 'M']],
                    ['sku' => 'WKU-002-MAROON-L', 'price' => 2500.00, 'stock_quantity' => 18, 'attributes' => ['Color' => 'Maroon', 'Size' => 'L']],
                ],
            ],

            // ━━━━━ WOMEN'S T-SHIRTS ━━━━━
            [
                'category_id' => $womenTshirt->id,
                'brand_id' => $urban->id,
                'name' => 'Crop Top Tee',
                'sku' => 'WTS-001',
                'description' => 'Trendy crop top t-shirt for young women',
                'base_price' => 550.00,
                'discount_price' => 449.00,
                'status' => 'active',
                'is_featured' => true,
                'options' => [
                    ['name' => 'Color', 'values' => ['White', 'Pink', 'Yellow']],
                    ['name' => 'Size', 'values' => ['S', 'M', 'L']],
                ],
                'variants' => [
                    ['sku' => 'WTS-001-WHITE-S', 'price' => 550.00, 'stock_quantity' => 40, 'attributes' => ['Color' => 'White', 'Size' => 'S']],
                    ['sku' => 'WTS-001-WHITE-M', 'price' => 550.00, 'stock_quantity' => 50, 'attributes' => ['Color' => 'White', 'Size' => 'M']],
                    ['sku' => 'WTS-001-WHITE-L', 'price' => 550.00, 'stock_quantity' => 35, 'attributes' => ['Color' => 'White', 'Size' => 'L']],
                    ['sku' => 'WTS-001-PINK-S', 'price' => 550.00, 'stock_quantity' => 35, 'attributes' => ['Color' => 'Pink', 'Size' => 'S']],
                    ['sku' => 'WTS-001-PINK-M', 'price' => 550.00, 'stock_quantity' => 45, 'attributes' => ['Color' => 'Pink', 'Size' => 'M']],
                    ['sku' => 'WTS-001-PINK-L', 'price' => 550.00, 'stock_quantity' => 30, 'attributes' => ['Color' => 'Pink', 'Size' => 'L']],
                    ['sku' => 'WTS-001-YELLOW-S', 'price' => 550.00, 'stock_quantity' => 25, 'attributes' => ['Color' => 'Yellow', 'Size' => 'S']],
                    ['sku' => 'WTS-001-YELLOW-M', 'price' => 550.00, 'stock_quantity' => 40, 'attributes' => ['Color' => 'Yellow', 'Size' => 'M']],
                ],
            ],

            // ━━━━━ WOMEN'S FROCKS ━━━━━
            [
                'category_id' => $womenFrock->id,
                'brand_id' => $fashionbd->id,
                'name' => 'Floral Print Frock',
                'sku' => 'WFR-001',
                'description' => 'Beautiful floral print frock for casual outings',
                'base_price' => 1800.00,
                'discount_price' => 1599.00,
                'status' => 'active',
                'is_featured' => true,
                'options' => [
                    ['name' => 'Color', 'values' => ['Pink', 'Blue', 'Yellow']],
                    ['name' => 'Size', 'values' => ['S', 'M', 'L']],
                ],
                'variants' => [
                    ['sku' => 'WFR-001-PINK-S', 'price' => 1800.00, 'stock_quantity' => 20, 'attributes' => ['Color' => 'Pink', 'Size' => 'S']],
                    ['sku' => 'WFR-001-PINK-M', 'price' => 1800.00, 'stock_quantity' => 30, 'attributes' => ['Color' => 'Pink', 'Size' => 'M']],
                    ['sku' => 'WFR-001-PINK-L', 'price' => 1800.00, 'stock_quantity' => 25, 'attributes' => ['Color' => 'Pink', 'Size' => 'L']],
                    ['sku' => 'WFR-001-BLUE-S', 'price' => 1800.00, 'stock_quantity' => 18, 'attributes' => ['Color' => 'Blue', 'Size' => 'S']],
                    ['sku' => 'WFR-001-BLUE-M', 'price' => 1800.00, 'stock_quantity' => 28, 'attributes' => ['Color' => 'Blue', 'Size' => 'M']],
                    ['sku' => 'WFR-001-BLUE-L', 'price' => 1800.00, 'stock_quantity' => 22, 'attributes' => ['Color' => 'Blue', 'Size' => 'L']],
                    ['sku' => 'WFR-001-YELLOW-S', 'price' => 1800.00, 'stock_quantity' => 15, 'attributes' => ['Color' => 'Yellow', 'Size' => 'S']],
                    ['sku' => 'WFR-001-YELLOW-M', 'price' => 1800.00, 'stock_quantity' => 25, 'attributes' => ['Color' => 'Yellow', 'Size' => 'M']],
                ],
            ],

            // ━━━━━ KIDS T-SHIRTS ━━━━━
            [
                'category_id' => $kidsTshirt->id,
                'brand_id' => $trendy->id,
                'name' => 'Kids Cartoon Tee',
                'sku' => 'KTS-001',
                'description' => 'Fun cartoon printed t-shirt for kids',
                'base_price' => 350.00,
                'discount_price' => 299.00,
                'status' => 'active',
                'is_featured' => true,
                'options' => [
                    ['name' => 'Color', 'values' => ['White', 'Blue', 'Red']],
                    ['name' => 'Size', 'values' => ['4-5 Years', '6-7 Years', '8-9 Years', '10-11 Years']],
                ],
                'variants' => [
                    ['sku' => 'KTS-001-WHITE-4-5', 'price' => 350.00, 'stock_quantity' => 30, 'attributes' => ['Color' => 'White', 'Size' => '4-5 Years']],
                    ['sku' => 'KTS-001-WHITE-6-7', 'price' => 350.00, 'stock_quantity' => 35, 'attributes' => ['Color' => 'White', 'Size' => '6-7 Years']],
                    ['sku' => 'KTS-001-WHITE-8-9', 'price' => 350.00, 'stock_quantity' => 28, 'attributes' => ['Color' => 'White', 'Size' => '8-9 Years']],
                    ['sku' => 'KTS-001-WHITE-10-11', 'price' => 350.00, 'stock_quantity' => 22, 'attributes' => ['Color' => 'White', 'Size' => '10-11 Years']],
                    ['sku' => 'KTS-001-BLUE-4-5', 'price' => 350.00, 'stock_quantity' => 25, 'attributes' => ['Color' => 'Blue', 'Size' => '4-5 Years']],
                    ['sku' => 'KTS-001-BLUE-6-7', 'price' => 350.00, 'stock_quantity' => 32, 'attributes' => ['Color' => 'Blue', 'Size' => '6-7 Years']],
                    ['sku' => 'KTS-001-BLUE-8-9', 'price' => 350.00, 'stock_quantity' => 25, 'attributes' => ['Color' => 'Blue', 'Size' => '8-9 Years']],
                    ['sku' => 'KTS-001-RED-4-5', 'price' => 350.00, 'stock_quantity' => 22, 'attributes' => ['Color' => 'Red', 'Size' => '4-5 Years']],
                    ['sku' => 'KTS-001-RED-6-7', 'price' => 350.00, 'stock_quantity' => 28, 'attributes' => ['Color' => 'Red', 'Size' => '6-7 Years']],
                ],
            ],
            [
                'category_id' => $kidsTshirt->id,
                'brand_id' => $trendy->id,
                'name' => 'Kids Solid Tee',
                'sku' => 'KTS-002',
                'description' => 'Simple solid color t-shirt for kids',
                'base_price' => 280.00,
                'status' => 'active',
                'is_featured' => false,
                'options' => [
                    ['name' => 'Color', 'values' => ['Red', 'Green', 'Blue']],
                    ['name' => 'Size', 'values' => ['4-5 Years', '6-7 Years', '8-9 Years']],
                ],
                'variants' => [
                    ['sku' => 'KTS-002-RED-4-5', 'price' => 280.00, 'stock_quantity' => 20, 'attributes' => ['Color' => 'Red', 'Size' => '4-5 Years']],
                    ['sku' => 'KTS-002-RED-6-7', 'price' => 280.00, 'stock_quantity' => 25, 'attributes' => ['Color' => 'Red', 'Size' => '6-7 Years']],
                    ['sku' => 'KTS-002-RED-8-9', 'price' => 280.00, 'stock_quantity' => 22, 'attributes' => ['Color' => 'Red', 'Size' => '8-9 Years']],
                    ['sku' => 'KTS-002-GREEN-4-5', 'price' => 280.00, 'stock_quantity' => 18, 'attributes' => ['Color' => 'Green', 'Size' => '4-5 Years']],
                    ['sku' => 'KTS-002-GREEN-6-7', 'price' => 280.00, 'stock_quantity' => 22, 'attributes' => ['Color' => 'Green', 'Size' => '6-7 Years']],
                    ['sku' => 'KTS-002-GREEN-8-9', 'price' => 280.00, 'stock_quantity' => 18, 'attributes' => ['Color' => 'Green', 'Size' => '8-9 Years']],
                ],
            ],

            // ━━━━━ KIDS PANTS ━━━━━
            [
                'category_id' => $kidsPant->id,
                'brand_id' => $trendy->id,
                'name' => 'Kids Cargo Pants',
                'sku' => 'KPT-001',
                'description' => 'Durable cargo pants for active kids',
                'base_price' => 500.00,
                'discount_price' => 399.00,
                'status' => 'active',
                'is_featured' => false,
                'options' => [
                    ['name' => 'Color', 'values' => ['Black', 'Khaki', 'Navy']],
                    ['name' => 'Size', 'values' => ['4-5 Years', '6-7 Years', '8-9 Years', '10-11 Years']],
                ],
                'variants' => [
                    ['sku' => 'KPT-001-BLACK-4-5', 'price' => 500.00, 'stock_quantity' => 20, 'attributes' => ['Color' => 'Black', 'Size' => '4-5 Years']],
                    ['sku' => 'KPT-001-BLACK-6-7', 'price' => 500.00, 'stock_quantity' => 25, 'attributes' => ['Color' => 'Black', 'Size' => '6-7 Years']],
                    ['sku' => 'KPT-001-BLACK-8-9', 'price' => 500.00, 'stock_quantity' => 22, 'attributes' => ['Color' => 'Black', 'Size' => '8-9 Years']],
                    ['sku' => 'KPT-001-KHAKI-4-5', 'price' => 500.00, 'stock_quantity' => 18, 'attributes' => ['Color' => 'Khaki', 'Size' => '4-5 Years']],
                    ['sku' => 'KPT-001-KHAKI-6-7', 'price' => 500.00, 'stock_quantity' => 22, 'attributes' => ['Color' => 'Khaki', 'Size' => '6-7 Years']],
                    ['sku' => 'KPT-001-NAVY-4-5', 'price' => 500.00, 'stock_quantity' => 15, 'attributes' => ['Color' => 'Navy', 'Size' => '4-5 Years']],
                ],
            ],

            // ━━━━━ KIDS FROCKS ━━━━━
            [
                'category_id' => $kidsFrock->id,
                'brand_id' => $fashionbd->id,
                'name' => 'Kids Party Frock',
                'sku' => 'KFR-001',
                'description' => 'Beautiful party frock for little girls',
                'base_price' => 800.00,
                'discount_price' => 699.00,
                'status' => 'active',
                'is_featured' => true,
                'options' => [
                    ['name' => 'Color', 'values' => ['Pink', 'White', 'Yellow']],
                    ['name' => 'Size', 'values' => ['4-5 Years', '6-7 Years', '8-9 Years']],
                ],
                'variants' => [
                    ['sku' => 'KFR-001-PINK-4-5', 'price' => 800.00, 'stock_quantity' => 15, 'attributes' => ['Color' => 'Pink', 'Size' => '4-5 Years']],
                    ['sku' => 'KFR-001-PINK-6-7', 'price' => 800.00, 'stock_quantity' => 20, 'attributes' => ['Color' => 'Pink', 'Size' => '6-7 Years']],
                    ['sku' => 'KFR-001-PINK-8-9', 'price' => 800.00, 'stock_quantity' => 18, 'attributes' => ['Color' => 'Pink', 'Size' => '8-9 Years']],
                    ['sku' => 'KFR-001-WHITE-4-5', 'price' => 800.00, 'stock_quantity' => 12, 'attributes' => ['Color' => 'White', 'Size' => '4-5 Years']],
                    ['sku' => 'KFR-001-WHITE-6-7', 'price' => 800.00, 'stock_quantity' => 15, 'attributes' => ['Color' => 'White', 'Size' => '6-7 Years']],
                    ['sku' => 'KFR-001-YELLOW-4-5', 'price' => 800.00, 'stock_quantity' => 10, 'attributes' => ['Color' => 'Yellow', 'Size' => '4-5 Years']],
                ],
            ],

            // ━━━━━ ACCESSORIES - BAGS (No variants) ━━━━━
            [
                'category_id' => $accBag->id,
                'brand_id' => $urban->id,
                'name' => 'Leather Backpack',
                'sku' => 'ABG-001',
                'description' => 'Premium leather backpack for daily use',
                'base_price' => 3500.00,
                'discount_price' => 2999.00,
                'stock_quantity' => 15,
                'status' => 'active',
                'is_featured' => true,
            ],
            [
                'category_id' => $accBag->id,
                'brand_id' => $urban->id,
                'name' => 'Canvas Tote Bag',
                'sku' => 'ABG-002',
                'description' => 'Stylish canvas tote bag for casual use',
                'base_price' => 1200.00,
                'stock_quantity' => 25,
                'status' => 'active',
                'is_featured' => false,
            ],

            // ━━━━━ ACCESSORIES - CAPS ━━━━━
            [
                'category_id' => $accCap->id,
                'brand_id' => $urban->id,
                'name' => 'Baseball Cap',
                'sku' => 'ACP-001',
                'description' => 'Adjustable baseball cap',
                'base_price' => 450.00,
                'discount_price' => 399.00,
                'status' => 'active',
                'is_featured' => false,
                'options' => [
                    ['name' => 'Color', 'values' => ['Black', 'White', 'Navy', 'Red']],
                ],
                'variants' => [
                    ['sku' => 'ACP-001-BLACK', 'price' => 450.00, 'stock_quantity' => 30, 'attributes' => ['Color' => 'Black']],
                    ['sku' => 'ACP-001-WHITE', 'price' => 450.00, 'stock_quantity' => 25, 'attributes' => ['Color' => 'White']],
                    ['sku' => 'ACP-001-NAVY', 'price' => 450.00, 'stock_quantity' => 20, 'attributes' => ['Color' => 'Navy']],
                    ['sku' => 'ACP-001-RED', 'price' => 450.00, 'stock_quantity' => 18, 'attributes' => ['Color' => 'Red']],
                ],
            ],

            // ━━━━━ ACCESSORIES - SCARVES (No variants) ━━━━━
            [
                'category_id' => $accScarf->id,
                'brand_id' => $elegant->id,
                'name' => 'Silk Scarf',
                'sku' => 'ASC-001',
                'description' => 'Elegant silk scarf for women',
                'base_price' => 800.00,
                'stock_quantity' => 20,
                'status' => 'active',
                'is_featured' => false,
            ],
        ];

        // ===== CREATE PRODUCTS =====
        foreach ($products as $pData) {
            $variants = $pData['variants'] ?? [];
            $options = $pData['options'] ?? [];
            unset($pData['variants'], $pData['options']);

            $pData['slug'] = Str::slug($pData['name']);
            $pData['stock_quantity'] = $pData['stock_quantity'] ?? 0;

            $product = Product::create($pData);

            // Save options as AttributeTemplate (Shopify-style)
            // updateOrCreate because same category e "Color"/"Size" already thakte pare
            foreach ($options as $opt) {
                $values = is_array($opt['values']) ? $opt['values'] : array_map('trim', explode(',', $opt['values']));
                AttributeTemplate::updateOrCreate(
                    [
                        'category_id' => $product->category_id,
                        'slug' => Str::slug($opt['name']),
                        'is_global' => false,
                    ],
                    [
                        'name' => $opt['name'],
                        'type' => 'select',
                        'options' => $values,
                        'is_required' => true,
                        'is_variant_option' => true,
                    ]
                );
            }

            // Create variants
            $totalStock = 0;
            foreach ($variants as $vData) {
                $variant = ProductVariant::create([
                    'product_id' => $product->id,
                    'sku' => $vData['sku'],
                    'name' => null,
                    'price' => $vData['price'],
                    'stock_quantity' => $vData['stock_quantity'],
                    'attributes' => $vData['attributes'],
                    'barcode' => null,
                    'is_active' => true,
                ]);

                // Save to relational table (variant_attribute_values)
                if (!empty($vData['attributes'])) {
                    foreach ($vData['attributes'] as $attrName => $attrValue) {
                        $attrTemplate = AttributeTemplate::where('name', $attrName)
                            ->where(function ($q) use ($product) {
                                $q->where('category_id', $product->category_id)
                                  ->orWhere('is_global', true);
                            })
                            ->first();

                        if ($attrTemplate) {
                            VariantAttributeValue::create([
                                'variant_id' => $variant->id,
                                'attribute_template_id' => $attrTemplate->id,
                                'value' => $attrValue,
                            ]);
                        }
                    }
                }

                $totalStock += $vData['stock_quantity'];
            }

            if (!empty($variants)) {
                $product->update(['stock_quantity' => $totalStock]);
            }

            // Inventory alert
            InventoryAlert::create([
                'product_id' => $product->id,
                'threshold' => 10,
                'is_active' => true,
            ]);

            // Stock movement
            if ($totalStock > 0) {
                StockMovement::create([
                    'product_id' => $product->id,
                    'variant_id' => null,
                    'warehouse_id' => $wh1->id,
                    'type' => 'in',
                    'quantity' => $totalStock,
                    'reference' => 'INV-INIT-' . strtoupper($product->sku),
                    'notes' => 'Initial inventory stock',
                    'created_by' => null,
                ]);
            }
        }

        $this->command->info("");
        $this->command->info("🎉 Clothing Inventory seeded successfully!");
        $this->command->info("━━━━━━━━━━━━━━━━━━━━━━━━━━━━━");
        $this->command->info("📦 Products: " . Product::count());
        $this->command->info("🔄 Variants: " . ProductVariant::count());
        $this->command->info("📂 Categories: " . Category::count());
        $this->command->info("🏷️  Brands: " . Brand::count());
        $this->command->info("🏭 Warehouses: " . Warehouse::count());
        $this->command->info("⚙️  Options (AttributeTemplates): " . AttributeTemplate::where('is_variant_option', true)->count());
        $this->command->info("🔗 Variant Attributes: " . \App\Models\VariantAttributeValue::count());
        $this->command->info("━━━━━━━━━━━━━━━━━━━━━━━━━━━━━");
    }
}
