<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\StorefrontApiController;
use App\Http\Controllers\ThemeController;
use App\Http\Controllers\ThemeEditorController;
use Stancl\Tenancy\Middleware\InitializeTenancyByDomain;
use Stancl\Tenancy\Middleware\PreventAccessFromCentralDomains;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Storefront API routes with tenant middleware for multi-tenant support.
| These routes handle all storefront data fetching for the React SPA.
|
*/

Route::middleware([
    'api',
    InitializeTenancyByDomain::class,
    PreventAccessFromCentralDomains::class,
])->prefix('storefront')->group(function () {
    // Public storefront endpoints
    Route::get('/config', [StorefrontApiController::class, 'config']);
    Route::get('/home', [StorefrontApiController::class, 'home']);
    Route::get('/products', [StorefrontApiController::class, 'products']);
    Route::get('/products/{slug}', [StorefrontApiController::class, 'product']);
    Route::get('/products/{slug}/related', [StorefrontApiController::class, 'relatedProducts']);
    Route::get('/categories', [StorefrontApiController::class, 'categories']);
    Route::get('/brands', [StorefrontApiController::class, 'brands']);
    Route::get('/featured', [StorefrontApiController::class, 'featured']);
});

// Theme endpoints (public)
Route::middleware([
    'api',
    InitializeTenancyByDomain::class,
    PreventAccessFromCentralDomains::class,
])->group(function () {
    Route::get('/themes', [ThemeController::class, 'index']);
    Route::get('/themes/{slug}', [ThemeController::class, 'show']);
});

// Theme editor endpoints (authenticated)
Route::middleware([
    'api',
    InitializeTenancyByDomain::class,
    PreventAccessFromCentralDomains::class,
])->prefix('editor')->group(function () {
    Route::get('/sections', [ThemeEditorController::class, 'sections']);
    Route::put('/sections/banners', [ThemeEditorController::class, 'updateBanners']);
    Route::put('/sections/notices', [ThemeEditorController::class, 'updateNotices']);
    Route::put('/sections/categories', [ThemeEditorController::class, 'updateCategories']);
    Route::put('/sections/all-categories', [ThemeEditorController::class, 'updateAllCategories']);
    Route::put('/sections/title', [ThemeEditorController::class, 'updateSectionTitle']);
    Route::put('/sections/category-banner', [ThemeEditorController::class, 'updateCategoryBanner']);
    Route::put('/sections/category-products', [ThemeEditorController::class, 'updateCategoryProducts']);
    Route::post('/upload', [ThemeEditorController::class, 'uploadImage']);
});