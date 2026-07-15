<?php

namespace Database\Seeders;

use App\Models\BusinessCategory;
use Illuminate\Database\Seeder;

class BusinessCategorySeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            [
                'name' => 'Fashion / Clothing',
                'slug' => 'fashion-clothing',
                'icon' => '👗',
                'extra_fields' => [
                    ['name' => 'size_chart', 'label' => 'Size Chart', 'type' => 'textarea', 'required' => true, 'placeholder' => 'S=36, M=38, L=40, XL=42'],
                    ['name' => 'color_variants', 'label' => 'Color Variants Available', 'type' => 'boolean', 'required' => false],
                    ['name' => 'fitting_guide', 'label' => 'Fitting Guide', 'type' => 'textarea', 'required' => false, 'placeholder' => 'সাইজ চার্ট অনুযায়ী মাপ নিন'],
                    ['name' => 'return_policy_days', 'label' => 'Return Policy (দিন)', 'type' => 'number', 'required' => true, 'default' => 3, 'placeholder' => '৩'],
                ],
                'sort_order' => 1,
            ],
            [
                'name' => 'Electronics',
                'slug' => 'electronics',
                'icon' => '📱',
                'extra_fields' => [
                    ['name' => 'warranty_period', 'label' => 'Warranty Period', 'type' => 'text', 'required' => true, 'placeholder' => '১ বছর'],
                    ['name' => 'model_serial_required', 'label' => 'Model/Serial Number Required', 'type' => 'boolean', 'required' => false],
                    ['name' => 'product_condition', 'label' => 'Product Condition', 'type' => 'select', 'required' => true, 'options' => ['Genuine', 'Refurbished', 'Both'], 'default' => 'Genuine'],
                    ['name' => 'after_sales_service', 'label' => 'After-Sales Service', 'type' => 'textarea', 'required' => false, 'placeholder' => 'সার্ভিস সেন্টারে জমা দিন'],
                ],
                'sort_order' => 2,
            ],
            [
                'name' => 'Food & Grocery',
                'slug' => 'food-grocery',
                'icon' => '🍕',
                'extra_fields' => [
                    ['name' => 'expiry_date_info', 'label' => 'Expiry Date Info', 'type' => 'boolean', 'required' => false],
                    ['name' => 'halal_certification', 'label' => 'Halal Certification', 'type' => 'boolean', 'required' => false],
                    ['name' => 'perishable_delivery', 'label' => 'Perishable Item Delivery', 'type' => 'boolean', 'required' => false],
                    ['name' => 'ingredients_allergens', 'label' => 'Ingredients / Allergens', 'type' => 'textarea', 'required' => false, 'placeholder' => 'উপাদান: চাল, ডাল, তেল...'],
                ],
                'sort_order' => 3,
            ],
            [
                'name' => 'Cosmetics & Beauty',
                'slug' => 'cosmetics-beauty',
                'icon' => '💄',
                'extra_fields' => [
                    ['name' => 'expiry_info', 'label' => 'Expiry Date Info', 'type' => 'boolean', 'required' => false],
                    ['name' => 'skin_type_suitability', 'label' => 'Skin Type Suitability', 'type' => 'text', 'required' => false, 'placeholder' => 'সব ধরনের ত্বক / শুষ্ক ত্বক / তৈলাক্ত ত্বক'],
                    ['name' => 'authentic_guarantee', 'label' => 'Original/Authentic Guarantee', 'type' => 'boolean', 'required' => false],
                    ['name' => 'usage_instructions', 'label' => 'Usage Instructions', 'type' => 'textarea', 'required' => false, 'placeholder' => 'দিনে ২ বার মুখে প্রয়োগ করুন'],
                ],
                'sort_order' => 4,
            ],
            [
                'name' => 'Furniture / Home',
                'slug' => 'furniture-home',
                'icon' => '🏠',
                'extra_fields' => [
                    ['name' => 'assembly_required', 'label' => 'Assembly Required', 'type' => 'boolean', 'required' => false],
                    ['name' => 'dimensions_info', 'label' => 'Dimensions Info', 'type' => 'text', 'required' => false, 'placeholder' => 'লম্বা: ৬ফুট, চওড়া: ৩ফুট'],
                    ['name' => 'delivery_method', 'label' => 'Delivery Method', 'type' => 'select', 'required' => true, 'options' => ['Truck', 'Courier', 'Both'], 'default' => 'Courier'],
                    ['name' => 'installation_charge', 'label' => 'Installation Charge (৳)', 'type' => 'number', 'required' => false, 'placeholder' => '৫০০'],
                ],
                'sort_order' => 5,
            ],
            [
                'name' => 'Digital Product / Service',
                'slug' => 'digital-product',
                'icon' => '💻',
                'extra_fields' => [
                    ['name' => 'delivery_method', 'label' => 'Delivery Method', 'type' => 'select', 'required' => true, 'options' => ['Email', 'Link', 'Both'], 'default' => 'Email'],
                    ['name' => 'license_validity', 'label' => 'License Validity', 'type' => 'text', 'required' => false, 'placeholder' => 'আজীবন / ১ বছর'],
                    ['name' => 'refund_policy', 'label' => 'Refund Policy', 'type' => 'textarea', 'required' => false, 'placeholder' => '৭ দিনের মধ্যে রিফান্ড'],
                    ['name' => 'subscription_type', 'label' => 'Subscription Type', 'type' => 'boolean', 'required' => false],
                ],
                'sort_order' => 6,
            ],
            [
                'name' => 'Handicraft / Customized',
                'slug' => 'handicraft-custom',
                'icon' => '🎨',
                'extra_fields' => [
                    ['name' => 'made_to_order_days', 'label' => 'Made-to-Order Time (দিন)', 'type' => 'number', 'required' => true, 'placeholder' => '৭'],
                    ['name' => 'customization_options', 'label' => 'Customization Options', 'type' => 'textarea', 'required' => false, 'placeholder' => 'রং, সাইজ, ডিজাইন পরিবর্তন করা যায়'],
                    ['name' => 'no_return_policy', 'label' => 'No Return Policy (Personalized)', 'type' => 'boolean', 'required' => false],
                    ['name' => 'artisan_info', 'label' => 'Artisan/Maker Info', 'type' => 'textarea', 'required' => false, 'placeholder' => 'হাতে তৈরি, গ্রামীণ কারিগরদের দ্বারা'],
                ],
                'sort_order' => 7,
            ],
            [
                'name' => 'Pharmacy / Health',
                'slug' => 'pharmacy-health',
                'icon' => '💊',
                'extra_fields' => [
                    ['name' => 'prescription_required', 'label' => 'Prescription Required', 'type' => 'boolean', 'required' => false],
                    ['name' => 'storage_condition', 'label' => 'Storage Condition', 'type' => 'text', 'required' => false, 'placeholder' => 'শুষ্ক স্থানে, রোদ থেকে দূরে'],
                    ['name' => 'regulatory_disclaimer', 'label' => 'Regulatory Disclaimer', 'type' => 'textarea', 'required' => false, 'placeholder' => 'ডাক্তারের পরামর্শ ছাড়া সেবন করবেন না'],
                    ['name' => 'dosage_info', 'label' => 'Dosage Info Available', 'type' => 'boolean', 'required' => false],
                ],
                'sort_order' => 8,
            ],
        ];

        foreach ($categories as $index => $category) {
            BusinessCategory::updateOrCreate(
                ['slug' => $category['slug']],
                array_merge($category, ['is_active' => true])
            );
        }
    }
}
