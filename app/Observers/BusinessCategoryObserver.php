<?php

namespace App\Observers;

use App\Jobs\SyncCategoryAttributeTemplates;
use App\Models\BusinessCategory;

class BusinessCategoryObserver
{
    public function created(BusinessCategory $category): void
    {
        SyncCategoryAttributeTemplates::dispatch($category);
    }

    public function updated(BusinessCategory $category): void
    {
        if ($category->isDirty('extra_fields')) {
            SyncCategoryAttributeTemplates::dispatch($category);
        }
    }
}
