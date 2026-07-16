<?php

namespace App\Providers;

use App\Models\BusinessCategory;
use App\Observers\BusinessCategoryObserver;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        BusinessCategory::observe(BusinessCategoryObserver::class);
    }
}
