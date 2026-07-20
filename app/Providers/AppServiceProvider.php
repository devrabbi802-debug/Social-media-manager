<?php

namespace App\Providers;

use App\Models\BusinessCategory;
use App\Models\BusinessSetup;
use App\Observers\BusinessCategoryObserver;
use Illuminate\Support\Facades\View;
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

        // Share businessSetup with all Blade views (graceful fallback if DB unavailable)
        try {
            View::share('businessSetup', BusinessSetup::getActive());
        } catch (\Throwable) {
            View::share('businessSetup', new BusinessSetup());
        }
    }
}
