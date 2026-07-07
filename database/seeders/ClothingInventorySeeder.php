<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Str;
use App\Models\Category;
use App\Models\Brand;
use App\Models\AttributeTemplate;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\Warehouse;
use App\Models\StockMovement;
use App\Models\InventoryAlert;

class ClothingInventorySeeder extends Seeder
{
    public function run(): void
    {
        if (Brand::count() > 0) {
            $this->command->info("Inventory already seeded. Skipping...");
            return;
        }

        $now = now();

        // ===== BRANDS =====
        Brand::insert([
            ['name' => 'FashionBD', 'slug' => 'fashionbd', 'is_active' => true, 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'StyleHub', 'slug' => 'stylehub', 'is_active' => true, 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'Urban Wear', 'slug' => 'urban-wear', 'is_active' => true, 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'Trendy Collection', 'slug' => 'trendy-collection', 'is_active' => true, 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'Elegant Style', 'slug' => 'elegant-style', 'is_active' => true, 'created_at' => $now, 'updated_at' => $now],
        ]);

        // ===== CATEGORIES =====
        $menCat = Category::create(['name' => 'Men', 'slug' => 'men', 'description' => "Men's Clothing", 'sort_order' => 1]);
        $womenCat = Category::create(['name' => 'Women', 'slug' => 'women', 'description' => "Women's Clothing", 'sort_order' => 2]);
        $kidsCat = Category::create(['name' => 'Kids', 'slug' => 'kids', 'description' => "Kids' Clothing", 'sort_order' => 3]);
        $accessoriesCat = Category::create(['name' => 'Accessories', 'slug' => 'accessories', 'description' => 'Fashion Accessories', 'sort_order' => 4]);

        // Men subcategories
        $menShirt = Category::create(['name' => 'Shirts', 'slug' => 'men-shirts', 'parent_id' => $menCat->id, 'description' => "Men's Shirts", 'sort_order' => 1]);
        $menTshirt = Category::create(['name' => 'T-Shirts', 'slug' => 'men-tshirts', 'parent_id' => $menCat->id, 'description' => "Men's T-Shirts", 'sort_order' => 2]);
        $menPant = Category::create(['name' => 'Pants', 'slug' => 'men-pants', 'parent_id' => $menCat->id, 'description' => "Men's Pants", 'sort_order' => 3]);
        $menPolo = Category::create(['name' => 'Polo Shirts', 'slug' => 'men-polo', 'parent_id' => $menCat->id, 'description' => "Men's Polo Shirts", 'sort_order' => 4]);
        $menJacket = Category::create(['name' => 'Jackets', 'slug' => 'men-jackets', 'parent_id' => $menCat->id, 'description' => "Men's Jackets", 'sort_order' => 5]);

        // Women subcategories
        $womenKurti = Category::create(['name' => 'Kurti', 'slug' => 'women-kurti', 'parent_id' => $womenCat->id, 'description' => "Women's Kurti", 'sort_order' => 1]);
        $womenSaree = Category::create(['name' => 'Saree', 'slug' => 'women-saree', 'parent_id' => $womenCat->id, 'description' => "Women's Saree", 'sort_order' => 2]);
        $womenSalwar = Category::create(['name' => 'Salwar Kameez', 'slug' => 'women-salwar', 'parent_id' => $womenCat->id, 'description' => "Women's Salwar Kameez", 'sort_order' => 3]);
        $womenTshirt = Category::create(['name' => 'T-Shirts', 'slug' => 'women-tshirts', 'parent_id' => $womenCat->id, 'description' => "Women's T-Shirts", 'sort_order' => 4]);
        $womenFrock = Category::create(['name' => 'Frocks', 'slug' => 'women-frocks', 'parent_id' => $womenCat->id, 'description' => "Women's Frocks", 'sort_order' => 5]);

        // Kids subcategories
        $kidsTshirt = Category::create(['name' => 'T-Shirts', 'slug' => 'kids-tshirts', 'parent_id' => $kidsCat->id, 'description' => "Kids' T-Shirts", 'sort_order' => 1]);
        $kidsPant = Category::create(['name' => 'Pants', 'slug' => 'kids-pants', 'parent_id' => $kidsCat->id, 'description' => "Kids' Pants", 'sort_order' => 2]);
        $kidsFrock = Category::create(['name' => 'Frocks', 'slug' => 'kids-frocks', 'parent_id' => $kidsCat->id, 'description' => "Kids' Frocks", 'sort_order' => 3]);

        // Accessories subcategories
        $accBag = Category::create(['name' => 'Bags', 'slug' => 'acc-bags', 'parent_id' => $accessoriesCat->id, 'description' => 'Fashion Bags', 'sort_order' => 1]);
        $accCap = Category::create(['name' => 'Caps & Hats', 'slug' => 'acc-caps', 'parent_id' => $accessoriesCat->id, 'description' => 'Caps and Hats', 'sort_order' => 2]);
        $accScarf = Category::create(['name' => 'Scarves', 'slug' => 'acc-scarves', 'parent_id' => $accessoriesCat->id, 'description' => 'Scarves', 'sort_order' => 3]);

        // ===== ATTRIBUTE TEMPLATES =====
        $allClothingCats = [$menShirt, $menTshirt, $menPant, $menPolo, $menJacket, $womenKurti, $womenSaree, $womenSalwar, $womenTshirt, $womenFrock, $kidsTshirt, $kidsPant, $kidsFrock];
        $adultCats = [$menShirt, $menTshirt, $menPant, $menPolo, $menJacket, $womenKurti, $womenSaree, $womenSalwar, $womenTshirt, $womenFrock];
        $kidsCats = [$kidsTshirt, $kidsPant, $kidsFrock];
        $materialCats = [$menShirt, $menPant, $womenKurti, $womenSaree, $womenSalwar, $womenFrock];

        foreach ($allClothingCats as $cat) {
            $isKids = in_array($cat->id, array_column($kidsCats, 'id'));
            AttributeTemplate::create([
                'category_id' => $cat->id,
                'name' => 'Size',
                'slug' => 'size',
                'type' => 'select',
                'options' => $isKids ? ['4-5 Years', '6-7 Years', '8-9 Years', '10-11 Years', '12-13 Years'] : ['S', 'M', 'L', 'XL', 'XXL'],
                'is_required' => true,
                'sort_order' => 1,
            ]);
        }

        foreach ($allClothingCats as $cat) {
            AttributeTemplate::create([
                'category_id' => $cat->id,
                'name' => 'Color',
                'slug' => 'color',
                'type' => 'select',
                'options' => ['Black', 'White', 'Blue', 'Red', 'Green', 'Navy', 'Grey', 'Pink', 'Yellow', 'Maroon'],
                'is_required' => true,
                'sort_order' => 2,
            ]);
        }

        foreach ($materialCats as $cat) {
            AttributeTemplate::create([
                'category_id' => $cat->id,
                'name' => 'Material',
                'slug' => 'material',
                'type' => 'select',
                'options' => ['Cotton', 'Polyester', 'Silk', 'Denim', 'Linen', 'Viscose', 'Georgette', 'Chiffon'],
                'is_required' => false,
                'sort_order' => 3,
            ]);
        }

        // ===== WAREHOUSES =====
        $wh1 = Warehouse::create(['name' => 'Main Warehouse - Dhaka', 'address' => 'Gulshan, Dhaka 1212', 'phone' => '+8801711000001']);
        $wh2 = Warehouse::create(['name' => 'Chittagong Warehouse', 'address' => 'Agrabad, Chittagong 4100', 'phone' => '+8801711000002']);

        // ===== PRODUCTS WITH VARIANTS =====
        $products = [
            // MEN'S SHIRTS
            [
                'category_id' => $menShirt->id, 'brand_id' => 1,
                'name' => 'Classic Formal Shirt', 'sku' => 'MSH-001',
                'description' => 'Premium cotton formal shirt for office wear',
                'base_price' => 1200.00, 'discount_price' => 999.00,
                'stock_quantity' => 0, 'status' => 'active', 'is_featured' => true,
                'variants' => [
                    ['sku' => 'MSH-001-S-BL', 'name' => 'Small / Black', 'price' => 1200.00, 'stock_quantity' => 25, 'attributes' => ['size' => 'S', 'color' => 'Black']],
                    ['sku' => 'MSH-001-M-BL', 'name' => 'Medium / Black', 'price' => 1200.00, 'stock_quantity' => 40, 'attributes' => ['size' => 'M', 'color' => 'Black']],
                    ['sku' => 'MSH-001-L-BL', 'name' => 'Large / Black', 'price' => 1200.00, 'stock_quantity' => 35, 'attributes' => ['size' => 'L', 'color' => 'Black']],
                    ['sku' => 'MSH-001-XL-BL', 'name' => 'XL / Black', 'price' => 1200.00, 'stock_quantity' => 20, 'attributes' => ['size' => 'XL', 'color' => 'Black']],
                    ['sku' => 'MSH-001-S-WH', 'name' => 'Small / White', 'price' => 1200.00, 'stock_quantity' => 30, 'attributes' => ['size' => 'S', 'color' => 'White']],
                    ['sku' => 'MSH-001-M-WH', 'name' => 'Medium / White', 'price' => 1200.00, 'stock_quantity' => 45, 'attributes' => ['size' => 'M', 'color' => 'White']],
                    ['sku' => 'MSH-001-L-WH', 'name' => 'Large / White', 'price' => 1200.00, 'stock_quantity' => 30, 'attributes' => ['size' => 'L', 'color' => 'White']],
                    ['sku' => 'MSH-001-XL-WH', 'name' => 'XL / White', 'price' => 1200.00, 'stock_quantity' => 15, 'attributes' => ['size' => 'XL', 'color' => 'White']],
                ],
            ],
            [
                'category_id' => $menShirt->id, 'brand_id' => 2,
                'name' => 'Casual Check Shirt', 'sku' => 'MSH-002',
                'description' => 'Trendy check pattern casual shirt',
                'base_price' => 1500.00, 'discount_price' => null,
                'stock_quantity' => 0, 'status' => 'active', 'is_featured' => false,
                'variants' => [
                    ['sku' => 'MSH-002-S-BL', 'name' => 'Small / Blue', 'price' => 1500.00, 'stock_quantity' => 20, 'attributes' => ['size' => 'S', 'color' => 'Blue']],
                    ['sku' => 'MSH-002-M-BL', 'name' => 'Medium / Blue', 'price' => 1500.00, 'stock_quantity' => 35, 'attributes' => ['size' => 'M', 'color' => 'Blue']],
                    ['sku' => 'MSH-002-L-BL', 'name' => 'Large / Blue', 'price' => 1500.00, 'stock_quantity' => 25, 'attributes' => ['size' => 'L', 'color' => 'Blue']],
                    ['sku' => 'MSH-002-XL-BL', 'name' => 'XL / Blue', 'price' => 1500.00, 'stock_quantity' => 15, 'attributes' => ['size' => 'XL', 'color' => 'Blue']],
                    ['sku' => 'MSH-002-XXL-BL', 'name' => 'XXL / Blue', 'price' => 1500.00, 'stock_quantity' => 10, 'attributes' => ['size' => 'XXL', 'color' => 'Blue']],
                ],
            ],

            // MEN'S T-SHIRTS
            [
                'category_id' => $menTshirt->id, 'brand_id' => 3,
                'name' => 'Urban Classic Tee', 'sku' => 'MTS-001',
                'description' => 'Comfortable cotton t-shirt with urban design',
                'base_price' => 650.00, 'discount_price' => 499.00,
                'stock_quantity' => 0, 'status' => 'active', 'is_featured' => true,
                'variants' => [
                    ['sku' => 'MTS-001-S-BK', 'name' => 'Small / Black', 'price' => 650.00, 'stock_quantity' => 50, 'attributes' => ['size' => 'S', 'color' => 'Black']],
                    ['sku' => 'MTS-001-M-BK', 'name' => 'Medium / Black', 'price' => 650.00, 'stock_quantity' => 60, 'attributes' => ['size' => 'M', 'color' => 'Black']],
                    ['sku' => 'MTS-001-L-BK', 'name' => 'Large / Black', 'price' => 650.00, 'stock_quantity' => 45, 'attributes' => ['size' => 'L', 'color' => 'Black']],
                    ['sku' => 'MTS-001-XL-BK', 'name' => 'XL / Black', 'price' => 650.00, 'stock_quantity' => 30, 'attributes' => ['size' => 'XL', 'color' => 'Black']],
                    ['sku' => 'MTS-001-S-WH', 'name' => 'Small / White', 'price' => 650.00, 'stock_quantity' => 40, 'attributes' => ['size' => 'S', 'color' => 'White']],
                    ['sku' => 'MTS-001-M-WH', 'name' => 'Medium / White', 'price' => 650.00, 'stock_quantity' => 55, 'attributes' => ['size' => 'M', 'color' => 'White']],
                    ['sku' => 'MTS-001-L-WH', 'name' => 'Large / White', 'price' => 650.00, 'stock_quantity' => 40, 'attributes' => ['size' => 'L', 'color' => 'White']],
                    ['sku' => 'MTS-001-XL-WH', 'name' => 'XL / White', 'price' => 650.00, 'stock_quantity' => 25, 'attributes' => ['size' => 'XL', 'color' => 'White']],
                    ['sku' => 'MTS-001-S-NA', 'name' => 'Small / Navy', 'price' => 650.00, 'stock_quantity' => 35, 'attributes' => ['size' => 'S', 'color' => 'Navy']],
                    ['sku' => 'MTS-001-M-NA', 'name' => 'Medium / Navy', 'price' => 650.00, 'stock_quantity' => 50, 'attributes' => ['size' => 'M', 'color' => 'Navy']],
                    ['sku' => 'MTS-001-L-NA', 'name' => 'Large / Navy', 'price' => 650.00, 'stock_quantity' => 30, 'attributes' => ['size' => 'L', 'color' => 'Navy']],
                    ['sku' => 'MTS-001-XL-NA', 'name' => 'XL / Navy', 'price' => 650.00, 'stock_quantity' => 20, 'attributes' => ['size' => 'XL', 'color' => 'Navy']],
                ],
            ],
            [
                'category_id' => $menTshirt->id, 'brand_id' => 3,
                'name' => 'Graphic Print Tee', 'sku' => 'MTS-002',
                'description' => 'Trendy graphic print t-shirt',
                'base_price' => 750.00, 'discount_price' => null,
                'stock_quantity' => 0, 'status' => 'active', 'is_featured' => false,
                'variants' => [
                    ['sku' => 'MTS-002-S-RD', 'name' => 'Small / Red', 'price' => 750.00, 'stock_quantity' => 20, 'attributes' => ['size' => 'S', 'color' => 'Red']],
                    ['sku' => 'MTS-002-M-RD', 'name' => 'Medium / Red', 'price' => 750.00, 'stock_quantity' => 30, 'attributes' => ['size' => 'M', 'color' => 'Red']],
                    ['sku' => 'MTS-002-L-RD', 'name' => 'Large / Red', 'price' => 750.00, 'stock_quantity' => 25, 'attributes' => ['size' => 'L', 'color' => 'Red']],
                    ['sku' => 'MTS-002-XL-RD', 'name' => 'XL / Red', 'price' => 750.00, 'stock_quantity' => 15, 'attributes' => ['size' => 'XL', 'color' => 'Red']],
                    ['sku' => 'MTS-002-S-GR', 'name' => 'Small / Green', 'price' => 750.00, 'stock_quantity' => 18, 'attributes' => ['size' => 'S', 'color' => 'Green']],
                    ['sku' => 'MTS-002-M-GR', 'name' => 'Medium / Green', 'price' => 750.00, 'stock_quantity' => 28, 'attributes' => ['size' => 'M', 'color' => 'Green']],
                    ['sku' => 'MTS-002-L-GR', 'name' => 'Large / Green', 'price' => 750.00, 'stock_quantity' => 22, 'attributes' => ['size' => 'L', 'color' => 'Green']],
                ],
            ],

            // MEN'S PANTS
            [
                'category_id' => $menPant->id, 'brand_id' => 4,
                'name' => 'Slim Fit Jeans', 'sku' => 'MPT-001',
                'description' => 'Premium denim slim fit jeans',
                'base_price' => 2200.00, 'discount_price' => 1899.00,
                'stock_quantity' => 0, 'status' => 'active', 'is_featured' => true,
                'variants' => [
                    ['sku' => 'MPT-001-S-BL', 'name' => 'Small / Blue', 'price' => 2200.00, 'stock_quantity' => 30, 'attributes' => ['size' => 'S', 'color' => 'Blue']],
                    ['sku' => 'MPT-001-M-BL', 'name' => 'Medium / Blue', 'price' => 2200.00, 'stock_quantity' => 45, 'attributes' => ['size' => 'M', 'color' => 'Blue']],
                    ['sku' => 'MPT-001-L-BL', 'name' => 'Large / Blue', 'price' => 2200.00, 'stock_quantity' => 35, 'attributes' => ['size' => 'L', 'color' => 'Blue']],
                    ['sku' => 'MPT-001-XL-BL', 'name' => 'XL / Blue', 'price' => 2200.00, 'stock_quantity' => 20, 'attributes' => ['size' => 'XL', 'color' => 'Blue']],
                    ['sku' => 'MPT-001-S-BK', 'name' => 'Small / Black', 'price' => 2200.00, 'stock_quantity' => 25, 'attributes' => ['size' => 'S', 'color' => 'Black']],
                    ['sku' => 'MPT-001-M-BK', 'name' => 'Medium / Black', 'price' => 2200.00, 'stock_quantity' => 40, 'attributes' => ['size' => 'M', 'color' => 'Black']],
                    ['sku' => 'MPT-001-L-BK', 'name' => 'Large / Black', 'price' => 2200.00, 'stock_quantity' => 30, 'attributes' => ['size' => 'L', 'color' => 'Black']],
                    ['sku' => 'MPT-001-XL-BK', 'name' => 'XL / Black', 'price' => 2200.00, 'stock_quantity' => 18, 'attributes' => ['size' => 'XL', 'color' => 'Black']],
                ],
            ],
            [
                'category_id' => $menPant->id, 'brand_id' => 4,
                'name' => 'Chino Pants', 'sku' => 'MPT-002',
                'description' => 'Comfortable chino pants for casual wear',
                'base_price' => 1800.00, 'discount_price' => null,
                'stock_quantity' => 0, 'status' => 'active', 'is_featured' => false,
                'variants' => [
                    ['sku' => 'MPT-002-S-GY', 'name' => 'Small / Grey', 'price' => 1800.00, 'stock_quantity' => 22, 'attributes' => ['size' => 'S', 'color' => 'Grey']],
                    ['sku' => 'MPT-002-M-GY', 'name' => 'Medium / Grey', 'price' => 1800.00, 'stock_quantity' => 35, 'attributes' => ['size' => 'M', 'color' => 'Grey']],
                    ['sku' => 'MPT-002-L-GY', 'name' => 'Large / Grey', 'price' => 1800.00, 'stock_quantity' => 28, 'attributes' => ['size' => 'L', 'color' => 'Grey']],
                    ['sku' => 'MPT-002-XL-GY', 'name' => 'XL / Grey', 'price' => 1800.00, 'stock_quantity' => 15, 'attributes' => ['size' => 'XL', 'color' => 'Grey']],
                ],
            ],

            // MEN'S POLO SHIRTS
            [
                'category_id' => $menPolo->id, 'brand_id' => 2,
                'name' => 'Classic Polo', 'sku' => 'MPL-001',
                'description' => 'Premium cotton polo shirt',
                'base_price' => 1100.00, 'discount_price' => 899.00,
                'stock_quantity' => 0, 'status' => 'active', 'is_featured' => true,
                'variants' => [
                    ['sku' => 'MPL-001-S-BK', 'name' => 'Small / Black', 'price' => 1100.00, 'stock_quantity' => 30, 'attributes' => ['size' => 'S', 'color' => 'Black']],
                    ['sku' => 'MPL-001-M-BK', 'name' => 'Medium / Black', 'price' => 1100.00, 'stock_quantity' => 45, 'attributes' => ['size' => 'M', 'color' => 'Black']],
                    ['sku' => 'MPL-001-L-BK', 'name' => 'Large / Black', 'price' => 1100.00, 'stock_quantity' => 35, 'attributes' => ['size' => 'L', 'color' => 'Black']],
                    ['sku' => 'MPL-001-XL-BK', 'name' => 'XL / Black', 'price' => 1100.00, 'stock_quantity' => 20, 'attributes' => ['size' => 'XL', 'color' => 'Black']],
                    ['sku' => 'MPL-001-S-WH', 'name' => 'Small / White', 'price' => 1100.00, 'stock_quantity' => 25, 'attributes' => ['size' => 'S', 'color' => 'White']],
                    ['sku' => 'MPL-001-M-WH', 'name' => 'Medium / White', 'price' => 1100.00, 'stock_quantity' => 40, 'attributes' => ['size' => 'M', 'color' => 'White']],
                    ['sku' => 'MPL-001-L-WH', 'name' => 'Large / White', 'price' => 1100.00, 'stock_quantity' => 30, 'attributes' => ['size' => 'L', 'color' => 'White']],
                    ['sku' => 'MPL-001-XL-WH', 'name' => 'XL / White', 'price' => 1100.00, 'stock_quantity' => 18, 'attributes' => ['size' => 'XL', 'color' => 'White']],
                ],
            ],

            // MEN'S JACKETS
            [
                'category_id' => $menJacket->id, 'brand_id' => 5,
                'name' => 'Denim Jacket', 'sku' => 'MJT-001',
                'description' => 'Classic denim jacket for all seasons',
                'base_price' => 3500.00, 'discount_price' => 2999.00,
                'stock_quantity' => 0, 'status' => 'active', 'is_featured' => true,
                'variants' => [
                    ['sku' => 'MJT-001-M-BL', 'name' => 'Medium / Blue', 'price' => 3500.00, 'stock_quantity' => 15, 'attributes' => ['size' => 'M', 'color' => 'Blue']],
                    ['sku' => 'MJT-001-L-BL', 'name' => 'Large / Blue', 'price' => 3500.00, 'stock_quantity' => 20, 'attributes' => ['size' => 'L', 'color' => 'Blue']],
                    ['sku' => 'MJT-001-XL-BL', 'name' => 'XL / Blue', 'price' => 3500.00, 'stock_quantity' => 12, 'attributes' => ['size' => 'XL', 'color' => 'Blue']],
                    ['sku' => 'MJT-001-M-BK', 'name' => 'Medium / Black', 'price' => 3500.00, 'stock_quantity' => 18, 'attributes' => ['size' => 'M', 'color' => 'Black']],
                    ['sku' => 'MJT-001-L-BK', 'name' => 'Large / Black', 'price' => 3500.00, 'stock_quantity' => 22, 'attributes' => ['size' => 'L', 'color' => 'Black']],
                    ['sku' => 'MJT-001-XL-BK', 'name' => 'XL / Black', 'price' => 3500.00, 'stock_quantity' => 10, 'attributes' => ['size' => 'XL', 'color' => 'Black']],
                ],
            ],

            // WOMEN'S KURTI
            [
                'category_id' => $womenKurti->id, 'brand_id' => 5,
                'name' => 'Printed Cotton Kurti', 'sku' => 'WKU-001',
                'description' => 'Beautiful printed cotton kurti for daily wear',
                'base_price' => 1400.00, 'discount_price' => 1199.00,
                'stock_quantity' => 0, 'status' => 'active', 'is_featured' => true,
                'variants' => [
                    ['sku' => 'WKU-001-S-BL', 'name' => 'Small / Blue', 'price' => 1400.00, 'stock_quantity' => 25, 'attributes' => ['size' => 'S', 'color' => 'Blue']],
                    ['sku' => 'WKU-001-M-BL', 'name' => 'Medium / Blue', 'price' => 1400.00, 'stock_quantity' => 40, 'attributes' => ['size' => 'M', 'color' => 'Blue']],
                    ['sku' => 'WKU-001-L-BL', 'name' => 'Large / Blue', 'price' => 1400.00, 'stock_quantity' => 30, 'attributes' => ['size' => 'L', 'color' => 'Blue']],
                    ['sku' => 'WKU-001-XL-BL', 'name' => 'XL / Blue', 'price' => 1400.00, 'stock_quantity' => 15, 'attributes' => ['size' => 'XL', 'color' => 'Blue']],
                    ['sku' => 'WKU-001-S-RD', 'name' => 'Small / Red', 'price' => 1400.00, 'stock_quantity' => 20, 'attributes' => ['size' => 'S', 'color' => 'Red']],
                    ['sku' => 'WKU-001-M-RD', 'name' => 'Medium / Red', 'price' => 1400.00, 'stock_quantity' => 35, 'attributes' => ['size' => 'M', 'color' => 'Red']],
                    ['sku' => 'WKU-001-L-RD', 'name' => 'Large / Red', 'price' => 1400.00, 'stock_quantity' => 28, 'attributes' => ['size' => 'L', 'color' => 'Red']],
                    ['sku' => 'WKU-001-XL-RD', 'name' => 'XL / Red', 'price' => 1400.00, 'stock_quantity' => 12, 'attributes' => ['size' => 'XL', 'color' => 'Red']],
                ],
            ],
            [
                'category_id' => $womenKurti->id, 'brand_id' => 5,
                'name' => 'Embroidered Kurti', 'sku' => 'WKU-002',
                'description' => 'Elegant embroidered kurti for special occasions',
                'base_price' => 2500.00, 'discount_price' => null,
                'stock_quantity' => 0, 'status' => 'active', 'is_featured' => false,
                'variants' => [
                    ['sku' => 'WKU-002-S-PK', 'name' => 'Small / Pink', 'price' => 2500.00, 'stock_quantity' => 15, 'attributes' => ['size' => 'S', 'color' => 'Pink']],
                    ['sku' => 'WKU-002-M-PK', 'name' => 'Medium / Pink', 'price' => 2500.00, 'stock_quantity' => 25, 'attributes' => ['size' => 'M', 'color' => 'Pink']],
                    ['sku' => 'WKU-002-L-PK', 'name' => 'Large / Pink', 'price' => 2500.00, 'stock_quantity' => 20, 'attributes' => ['size' => 'L', 'color' => 'Pink']],
                    ['sku' => 'WKU-002-S-MR', 'name' => 'Small / Maroon', 'price' => 2500.00, 'stock_quantity' => 12, 'attributes' => ['size' => 'S', 'color' => 'Maroon']],
                    ['sku' => 'WKU-002-M-MR', 'name' => 'Medium / Maroon', 'price' => 2500.00, 'stock_quantity' => 22, 'attributes' => ['size' => 'M', 'color' => 'Maroon']],
                    ['sku' => 'WKU-002-L-MR', 'name' => 'Large / Maroon', 'price' => 2500.00, 'stock_quantity' => 18, 'attributes' => ['size' => 'L', 'color' => 'Maroon']],
                ],
            ],

            // WOMEN'S SAREE
            [
                'category_id' => $womenSaree->id, 'brand_id' => 5,
                'name' => 'Silk Saree', 'sku' => 'WSR-001',
                'description' => 'Pure silk saree with beautiful border',
                'base_price' => 5500.00, 'discount_price' => 4999.00,
                'stock_quantity' => 0, 'status' => 'active', 'is_featured' => true,
                'variants' => [
                    ['sku' => 'WSR-001-RD', 'name' => 'Red', 'price' => 5500.00, 'stock_quantity' => 8, 'attributes' => ['size' => 'S', 'color' => 'Red']],
                    ['sku' => 'WSR-001-BL', 'name' => 'Blue', 'price' => 5500.00, 'stock_quantity' => 10, 'attributes' => ['size' => 'S', 'color' => 'Blue']],
                    ['sku' => 'WSR-001-GN', 'name' => 'Green', 'price' => 5500.00, 'stock_quantity' => 6, 'attributes' => ['size' => 'S', 'color' => 'Green']],
                    ['sku' => 'WSR-001-BK', 'name' => 'Black', 'price' => 5500.00, 'stock_quantity' => 5, 'attributes' => ['size' => 'S', 'color' => 'Black']],
                ],
            ],
            [
                'category_id' => $womenSaree->id, 'brand_id' => 5,
                'name' => 'Georgette Saree', 'sku' => 'WSR-002',
                'description' => 'Lightweight georgette saree for party wear',
                'base_price' => 3200.00, 'discount_price' => null,
                'stock_quantity' => 0, 'status' => 'active', 'is_featured' => false,
                'variants' => [
                    ['sku' => 'WSR-002-PK', 'name' => 'Pink', 'price' => 3200.00, 'stock_quantity' => 12, 'attributes' => ['size' => 'S', 'color' => 'Pink']],
                    ['sku' => 'WSR-002-YL', 'name' => 'Yellow', 'price' => 3200.00, 'stock_quantity' => 10, 'attributes' => ['size' => 'S', 'color' => 'Yellow']],
                    ['sku' => 'WSR-002-GR', 'name' => 'Green', 'price' => 3200.00, 'stock_quantity' => 8, 'attributes' => ['size' => 'S', 'color' => 'Green']],
                ],
            ],

            // WOMEN'S SALWAR KAMEEZ
            [
                'category_id' => $womenSalwar->id, 'brand_id' => 5,
                'name' => 'Cotton Salwar Kameez', 'sku' => 'WSK-001',
                'description' => 'Comfortable cotton salwar kameez set',
                'base_price' => 2200.00, 'discount_price' => 1899.00,
                'stock_quantity' => 0, 'status' => 'active', 'is_featured' => true,
                'variants' => [
                    ['sku' => 'WSK-001-S-BL', 'name' => 'Small / Blue', 'price' => 2200.00, 'stock_quantity' => 20, 'attributes' => ['size' => 'S', 'color' => 'Blue']],
                    ['sku' => 'WSK-001-M-BL', 'name' => 'Medium / Blue', 'price' => 2200.00, 'stock_quantity' => 35, 'attributes' => ['size' => 'M', 'color' => 'Blue']],
                    ['sku' => 'WSK-001-L-BL', 'name' => 'Large / Blue', 'price' => 2200.00, 'stock_quantity' => 25, 'attributes' => ['size' => 'L', 'color' => 'Blue']],
                    ['sku' => 'WSK-001-S-GN', 'name' => 'Small / Green', 'price' => 2200.00, 'stock_quantity' => 18, 'attributes' => ['size' => 'S', 'color' => 'Green']],
                    ['sku' => 'WSK-001-M-GN', 'name' => 'Medium / Green', 'price' => 2200.00, 'stock_quantity' => 30, 'attributes' => ['size' => 'M', 'color' => 'Green']],
                    ['sku' => 'WSK-001-L-GN', 'name' => 'Large / Green', 'price' => 2200.00, 'stock_quantity' => 22, 'attributes' => ['size' => 'L', 'color' => 'Green']],
                ],
            ],

            // WOMEN'S T-SHIRTS
            [
                'category_id' => $womenTshirt->id, 'brand_id' => 3,
                'name' => 'Crop Top Tee', 'sku' => 'WTS-001',
                'description' => 'Trendy crop top t-shirt for young women',
                'base_price' => 550.00, 'discount_price' => 449.00,
                'stock_quantity' => 0, 'status' => 'active', 'is_featured' => true,
                'variants' => [
                    ['sku' => 'WTS-001-S-WH', 'name' => 'Small / White', 'price' => 550.00, 'stock_quantity' => 40, 'attributes' => ['size' => 'S', 'color' => 'White']],
                    ['sku' => 'WTS-001-M-WH', 'name' => 'Medium / White', 'price' => 550.00, 'stock_quantity' => 50, 'attributes' => ['size' => 'M', 'color' => 'White']],
                    ['sku' => 'WTS-001-L-WH', 'name' => 'Large / White', 'price' => 550.00, 'stock_quantity' => 35, 'attributes' => ['size' => 'L', 'color' => 'White']],
                    ['sku' => 'WTS-001-S-PK', 'name' => 'Small / Pink', 'price' => 550.00, 'stock_quantity' => 35, 'attributes' => ['size' => 'S', 'color' => 'Pink']],
                    ['sku' => 'WTS-001-M-PK', 'name' => 'Medium / Pink', 'price' => 550.00, 'stock_quantity' => 45, 'attributes' => ['size' => 'M', 'color' => 'Pink']],
                    ['sku' => 'WTS-001-L-PK', 'name' => 'Large / Pink', 'price' => 550.00, 'stock_quantity' => 30, 'attributes' => ['size' => 'L', 'color' => 'Pink']],
                    ['sku' => 'WTS-001-S-YL', 'name' => 'Small / Yellow', 'price' => 550.00, 'stock_quantity' => 25, 'attributes' => ['size' => 'S', 'color' => 'Yellow']],
                    ['sku' => 'WTS-001-M-YL', 'name' => 'Medium / Yellow', 'price' => 550.00, 'stock_quantity' => 40, 'attributes' => ['size' => 'M', 'color' => 'Yellow']],
                ],
            ],

            // WOMEN'S FROCKS
            [
                'category_id' => $womenFrock->id, 'brand_id' => 1,
                'name' => 'Floral Print Frock', 'sku' => 'WFR-001',
                'description' => 'Beautiful floral print frock for casual outings',
                'base_price' => 1800.00, 'discount_price' => 1599.00,
                'stock_quantity' => 0, 'status' => 'active', 'is_featured' => true,
                'variants' => [
                    ['sku' => 'WFR-001-S-PK', 'name' => 'Small / Pink', 'price' => 1800.00, 'stock_quantity' => 20, 'attributes' => ['size' => 'S', 'color' => 'Pink']],
                    ['sku' => 'WFR-001-M-PK', 'name' => 'Medium / Pink', 'price' => 1800.00, 'stock_quantity' => 30, 'attributes' => ['size' => 'M', 'color' => 'Pink']],
                    ['sku' => 'WFR-001-L-PK', 'name' => 'Large / Pink', 'price' => 1800.00, 'stock_quantity' => 25, 'attributes' => ['size' => 'L', 'color' => 'Pink']],
                    ['sku' => 'WFR-001-S-BL', 'name' => 'Small / Blue', 'price' => 1800.00, 'stock_quantity' => 18, 'attributes' => ['size' => 'S', 'color' => 'Blue']],
                    ['sku' => 'WFR-001-M-BL', 'name' => 'Medium / Blue', 'price' => 1800.00, 'stock_quantity' => 28, 'attributes' => ['size' => 'M', 'color' => 'Blue']],
                    ['sku' => 'WFR-001-L-BL', 'name' => 'Large / Blue', 'price' => 1800.00, 'stock_quantity' => 22, 'attributes' => ['size' => 'L', 'color' => 'Blue']],
                ],
            ],

            // KIDS T-SHIRTS
            [
                'category_id' => $kidsTshirt->id, 'brand_id' => 4,
                'name' => 'Kids Cartoon Tee', 'sku' => 'KTS-001',
                'description' => 'Fun cartoon printed t-shirt for kids',
                'base_price' => 350.00, 'discount_price' => 299.00,
                'stock_quantity' => 0, 'status' => 'active', 'is_featured' => true,
                'variants' => [
                    ['sku' => 'KTS-001-4-5-WH', 'name' => '4-5 Years / White', 'price' => 350.00, 'stock_quantity' => 30, 'attributes' => ['size' => '4-5 Years', 'color' => 'White']],
                    ['sku' => 'KTS-001-6-7-WH', 'name' => '6-7 Years / White', 'price' => 350.00, 'stock_quantity' => 35, 'attributes' => ['size' => '6-7 Years', 'color' => 'White']],
                    ['sku' => 'KTS-001-8-9-WH', 'name' => '8-9 Years / White', 'price' => 350.00, 'stock_quantity' => 28, 'attributes' => ['size' => '8-9 Years', 'color' => 'White']],
                    ['sku' => 'KTS-001-4-5-BL', 'name' => '4-5 Years / Blue', 'price' => 350.00, 'stock_quantity' => 25, 'attributes' => ['size' => '4-5 Years', 'color' => 'Blue']],
                    ['sku' => 'KTS-001-6-7-BL', 'name' => '6-7 Years / Blue', 'price' => 350.00, 'stock_quantity' => 32, 'attributes' => ['size' => '6-7 Years', 'color' => 'Blue']],
                    ['sku' => 'KTS-001-8-9-BL', 'name' => '8-9 Years / Blue', 'price' => 350.00, 'stock_quantity' => 25, 'attributes' => ['size' => '8-9 Years', 'color' => 'Blue']],
                    ['sku' => 'KTS-001-10-11-BL', 'name' => '10-11 Years / Blue', 'price' => 350.00, 'stock_quantity' => 20, 'attributes' => ['size' => '10-11 Years', 'color' => 'Blue']],
                ],
            ],
            [
                'category_id' => $kidsTshirt->id, 'brand_id' => 4,
                'name' => 'Kids Solid Tee', 'sku' => 'KTS-002',
                'description' => 'Simple solid color t-shirt for kids',
                'base_price' => 280.00, 'discount_price' => null,
                'stock_quantity' => 0, 'status' => 'active', 'is_featured' => false,
                'variants' => [
                    ['sku' => 'KTS-002-4-5-RD', 'name' => '4-5 Years / Red', 'price' => 280.00, 'stock_quantity' => 20, 'attributes' => ['size' => '4-5 Years', 'color' => 'Red']],
                    ['sku' => 'KTS-002-6-7-RD', 'name' => '6-7 Years / Red', 'price' => 280.00, 'stock_quantity' => 25, 'attributes' => ['size' => '6-7 Years', 'color' => 'Red']],
                    ['sku' => 'KTS-002-8-9-RD', 'name' => '8-9 Years / Red', 'price' => 280.00, 'stock_quantity' => 22, 'attributes' => ['size' => '8-9 Years', 'color' => 'Red']],
                    ['sku' => 'KTS-002-4-5-GR', 'name' => '4-5 Years / Green', 'price' => 280.00, 'stock_quantity' => 18, 'attributes' => ['size' => '4-5 Years', 'color' => 'Green']],
                    ['sku' => 'KTS-002-6-7-GR', 'name' => '6-7 Years / Green', 'price' => 280.00, 'stock_quantity' => 22, 'attributes' => ['size' => '6-7 Years', 'color' => 'Green']],
                ],
            ],

            // KIDS PANTS
            [
                'category_id' => $kidsPant->id, 'brand_id' => 4,
                'name' => 'Kids Cargo Pants', 'sku' => 'KPT-001',
                'description' => 'Durable cargo pants for active kids',
                'base_price' => 500.00, 'discount_price' => 399.00,
                'stock_quantity' => 0, 'status' => 'active', 'is_featured' => false,
                'variants' => [
                    ['sku' => 'KPT-001-4-5-BK', 'name' => '4-5 Years / Black', 'price' => 500.00, 'stock_quantity' => 20, 'attributes' => ['size' => '4-5 Years', 'color' => 'Black']],
                    ['sku' => 'KPT-001-6-7-BK', 'name' => '6-7 Years / Black', 'price' => 500.00, 'stock_quantity' => 25, 'attributes' => ['size' => '6-7 Years', 'color' => 'Black']],
                    ['sku' => 'KPT-001-8-9-BK', 'name' => '8-9 Years / Black', 'price' => 500.00, 'stock_quantity' => 22, 'attributes' => ['size' => '8-9 Years', 'color' => 'Black']],
                    ['sku' => 'KPT-001-10-11-BK', 'name' => '10-11 Years / Black', 'price' => 500.00, 'stock_quantity' => 18, 'attributes' => ['size' => '10-11 Years', 'color' => 'Black']],
                ],
            ],

            // KIDS FROCKS
            [
                'category_id' => $kidsFrock->id, 'brand_id' => 1,
                'name' => 'Kids Party Frock', 'sku' => 'KFR-001',
                'description' => 'Beautiful party frock for little girls',
                'base_price' => 800.00, 'discount_price' => 699.00,
                'stock_quantity' => 0, 'status' => 'active', 'is_featured' => true,
                'variants' => [
                    ['sku' => 'KFR-001-4-5-PK', 'name' => '4-5 Years / Pink', 'price' => 800.00, 'stock_quantity' => 15, 'attributes' => ['size' => '4-5 Years', 'color' => 'Pink']],
                    ['sku' => 'KFR-001-6-7-PK', 'name' => '6-7 Years / Pink', 'price' => 800.00, 'stock_quantity' => 20, 'attributes' => ['size' => '6-7 Years', 'color' => 'Pink']],
                    ['sku' => 'KFR-001-8-9-PK', 'name' => '8-9 Years / Pink', 'price' => 800.00, 'stock_quantity' => 18, 'attributes' => ['size' => '8-9 Years', 'color' => 'Pink']],
                    ['sku' => 'KFR-001-4-5-WH', 'name' => '4-5 Years / White', 'price' => 800.00, 'stock_quantity' => 12, 'attributes' => ['size' => '4-5 Years', 'color' => 'White']],
                    ['sku' => 'KFR-001-6-7-WH', 'name' => '6-7 Years / White', 'price' => 800.00, 'stock_quantity' => 15, 'attributes' => ['size' => '6-7 Years', 'color' => 'White']],
                ],
            ],

            // ACCESSORIES - BAGS
            [
                'category_id' => $accBag->id, 'brand_id' => 3,
                'name' => 'Leather Backpack', 'sku' => 'ABG-001',
                'description' => 'Premium leather backpack for daily use',
                'base_price' => 3500.00, 'discount_price' => 2999.00,
                'stock_quantity' => 15, 'status' => 'active', 'is_featured' => true, 'unit' => 'pcs',
            ],
            [
                'category_id' => $accBag->id, 'brand_id' => 3,
                'name' => 'Canvas Tote Bag', 'sku' => 'ABG-002',
                'description' => 'Stylish canvas tote bag for casual use',
                'base_price' => 1200.00, 'discount_price' => null,
                'stock_quantity' => 25, 'status' => 'active', 'is_featured' => false, 'unit' => 'pcs',
            ],

            // ACCESSORIES - CAPS
            [
                'category_id' => $accCap->id, 'brand_id' => 3,
                'name' => 'Baseball Cap', 'sku' => 'ACP-001',
                'description' => 'Adjustable baseball cap',
                'base_price' => 450.00, 'discount_price' => 399.00,
                'stock_quantity' => 0, 'status' => 'active', 'is_featured' => false,
                'variants' => [
                    ['sku' => 'ACP-001-S-BK', 'name' => 'Black', 'price' => 450.00, 'stock_quantity' => 30, 'attributes' => ['size' => 'S', 'color' => 'Black']],
                    ['sku' => 'ACP-001-S-WH', 'name' => 'White', 'price' => 450.00, 'stock_quantity' => 25, 'attributes' => ['size' => 'S', 'color' => 'White']],
                    ['sku' => 'ACP-001-S-NA', 'name' => 'Navy', 'price' => 450.00, 'stock_quantity' => 20, 'attributes' => ['size' => 'S', 'color' => 'Navy']],
                    ['sku' => 'ACP-001-S-RD', 'name' => 'Red', 'price' => 450.00, 'stock_quantity' => 18, 'attributes' => ['size' => 'S', 'color' => 'Red']],
                ],
            ],

            // ACCESSORIES - SCARVES
            [
                'category_id' => $accScarf->id, 'brand_id' => 5,
                'name' => 'Silk Scarf', 'sku' => 'ASC-001',
                'description' => 'Elegant silk scarf for women',
                'base_price' => 800.00, 'discount_price' => null,
                'stock_quantity' => 20, 'status' => 'active', 'is_featured' => false, 'unit' => 'pcs',
            ],
        ];

        foreach ($products as $pData) {
            $variants = $pData['variants'] ?? [];
            unset($pData['variants']);

            if (empty($pData['slug'])) {
                $pData['slug'] = Str::slug($pData['name']);
            }

            $product = Product::create($pData);

            if (!empty($variants)) {
                $totalStock = 0;
                foreach ($variants as $vData) {
                    ProductVariant::create(array_merge($vData, [
                        'product_id' => $product->id,
                        'is_active' => true,
                    ]));
                    $totalStock += $vData['stock_quantity'];
                }
                $product->update(['stock_quantity' => $totalStock]);
            }

            InventoryAlert::create([
                'product_id' => $product->id,
                'threshold' => 10,
                'is_active' => true,
            ]);

            StockMovement::create([
                'product_id' => $product->id,
                'variant_id' => null,
                'warehouse_id' => $wh1->id,
                'type' => 'in',
                'quantity' => $product->stock_quantity,
                'reference' => 'INV-INIT-' . strtoupper($product->sku),
                'notes' => 'Initial inventory stock',
            ]);
        }

        $this->command->info("Clothing Inventory seeded successfully!");
        $this->command->info("Products: " . Product::count());
        $this->command->info("Variants: " . ProductVariant::count());
        $this->command->info("Categories: " . Category::count());
        $this->command->info("Brands: " . Brand::count());
        $this->command->info("Warehouses: " . Warehouse::count());
    }
}
