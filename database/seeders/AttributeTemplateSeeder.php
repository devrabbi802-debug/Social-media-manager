<?php

namespace Database\Seeders;

use App\Models\Attribute;
use App\Models\AttributeValue;
use App\Models\BusinessCategory;
use App\Models\Category;
use App\Models\CategoryAttribute;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class AttributeTemplateSeeder extends Seeder
{
    public function run(): void
    {
        $this->createGlobalVariantOptions();
        $this->createCategoryExtraFieldTemplates();
    }

    private function createGlobalVariantOptions(): void
    {
        $variantOptions = [
            ['name' => 'Color',      'options' => ['Red', 'Blue', 'Green', 'Black', 'White', 'Navy', 'Grey', 'Pink', 'Yellow', 'Purple']],
            ['name' => 'Size',       'options' => ['XS', 'S', 'M', 'L', 'XL', 'XXL', '3XL']],
            ['name' => 'Material',   'options' => ['Cotton', 'Polyester', 'Silk', 'Leather', 'Wood', 'Metal', 'Plastic', 'Rubber']],
            ['name' => 'Storage',    'options' => ['64GB', '128GB', '255GB', '512GB', '1TB', '2TB']],
            ['name' => 'Flavor',     'options' => ['Original', 'Chocolate', 'Vanilla', 'Strawberry', 'Mango', 'Mixed']],
            ['name' => 'Shade',      'options' => ['Light', 'Medium', 'Dark', 'Natural', 'Fair']],
            ['name' => 'Strength',   'options' => ['250mg', '500mg', '1g', '2g', '5g']],
            ['name' => 'Pack Size',  'options' => ['10s', '30s', '60s', '120s', '500g', '1kg']],
        ];

        foreach ($variantOptions as $opt) {
            $attribute = Attribute::firstOrCreate(
                ['slug' => Str::slug($opt['name']), 'is_global' => true],
                [
                    'category_id' => null,
                    'is_variant' => true,
                    'is_filterable' => true,
                    'name' => $opt['name'],
                    'data_type' => 'select',
                    'is_active' => true,
                    'sort_order' => 0,
                ]
            );

            // Create attribute values (options)
            foreach ($opt['options'] as $idx => $value) {
                AttributeValue::firstOrCreate(
                    ['attribute_id' => $attribute->id, 'value' => $value],
                    [
                        'sort_order' => $idx,
                    ]
                );
            }
        }
    }

    private function createCategoryExtraFieldTemplates(): void
    {
        // Load all business categories from LANDLORD DB
        $businessCategories = BusinessCategory::on('mysql')->where('is_active', true)->get();

        foreach ($businessCategories as $bc) {
            // Find or create matching Category in TENANT DB
            $tenantCategory = Category::firstOrCreate(
                ['slug' => $bc->slug],
                [
                    'name' => $bc->name,
                    'description' => $bc->name.' category products',
                    'is_active' => true,
                    'sort_order' => $bc->sort_order,
                ]
            );

            $extraFields = $bc->extra_fields ?? [];
            foreach ($extraFields as $index => $field) {
                $attr = Attribute::firstOrCreate(
                    [
                        'category_id' => $tenantCategory->id,
                        'slug' => Str::slug($field['name']),
                        'is_global' => false,
                    ],
                    [
                        'name' => $field['label'] ?? $field['name'],
                        'data_type' => $field['type'],
                        'is_variant' => false,
                        'is_filterable' => false,
                        'placeholder' => $field['placeholder'] ?? null,
                        'default' => $field['default'] ?? null,
                        'is_active' => true,
                        'sort_order' => $index,
                    ]
                );

                CategoryAttribute::firstOrCreate(
                    [
                        'category_id' => $tenantCategory->id,
                        'attribute_id' => $attr->id,
                    ],
                    [
                        'required' => $field['required'] ?? false,
                        'sort_order' => $index,
                    ]
                );
            }
        }
    }
}
