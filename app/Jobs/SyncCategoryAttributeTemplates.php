<?php

namespace App\Jobs;

use App\Models\AttributeTemplate;
use App\Models\BusinessCategory;
use App\Models\Category;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Stancl\Tenancy\Facades\Tenancy;

class SyncCategoryAttributeTemplates implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;
    public int $timeout = 120;

    public function __construct(
        public BusinessCategory $category
    ) {}

    public function handle(): void
    {
        $tenants = \Stancl\Tenancy\Models\Tenant::all();

        foreach ($tenants as $tenant) {
            try {
                Tenancy::initialize($tenant);
                $this->syncForTenant($this->category);
                Tenancy::end();
            } catch (\Exception $e) {
                Log::error("SyncCategoryAttributeTemplates failed for tenant {$tenant->id}: {$e->getMessage()}");
                Tenancy::end();
            }
        }
    }

    private function syncForTenant(BusinessCategory $category): void
    {
        $extraFields = $category->extra_fields ?? [];
        $fieldNames = collect($extraFields)->pluck('name')->map(fn($n) => Str::slug($n))->toArray();

        // Find or create matching tenant Category
        $tenantCategory = Category::firstOrCreate(
            ['slug' => $category->slug],
            [
                'name'        => $category->name,
                'description' => $category->name . ' category products',
                'is_active'   => true,
                'sort_order'  => $category->sort_order,
            ]
        );

        // UPSERT: extra_fields theke templates create/update
        foreach ($extraFields as $index => $field) {
            AttributeTemplate::updateOrCreate(
                [
                    'category_id'       => $tenantCategory->id,
                    'slug'              => Str::slug($field['name']),
                    'is_global'         => false,
                ],
                [
                    'name'              => $field['label'] ?? $field['name'],
                    'type'              => $field['type'],
                    'is_required'       => $field['required'] ?? false,
                    'placeholder'       => $field['placeholder'] ?? null,
                    'default'           => $field['default'] ?? null,
                    'options'           => $field['options'] ?? null,
                    'is_variant_option' => false,
                    'is_active'         => true,
                    'sort_order'        => $index,
                ]
            );
        }

        // SOFT DELETE: JSON theke removed fields → deactivate
        AttributeTemplate::where('category_id', $tenantCategory->id)
            ->where('is_global', false)
            ->where('is_variant_option', false)
            ->whereNotIn('slug', $fieldNames)
            ->update(['is_active' => false]);

        // HARD DELETE: if template has no product_attribute_values → safe to remove
        AttributeTemplate::where('category_id', $tenantCategory->id)
            ->where('is_global', false)
            ->where('is_variant_option', false)
            ->where('is_active', false)
            ->whereDoesntHave('attributeValues')
            ->delete();
    }
}
