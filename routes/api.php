<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\StorefrontApiController;
use App\Http\Controllers\Storefront\AuthController;
use App\Http\Controllers\Storefront\CheckoutController;
use App\Http\Controllers\Storefront\CustomerController;
use App\Http\Controllers\Storefront\ForgotPasswordController;
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

// Storefront Auth endpoints (public)
Route::middleware([
    'api',
    InitializeTenancyByDomain::class,
    PreventAccessFromCentralDomains::class,
])->prefix('auth')->group(function () {
    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/login', [AuthController::class, 'login']);
    Route::post('/forgot-password', [ForgotPasswordController::class, 'sendResetLink']);
    Route::post('/reset-password', [ForgotPasswordController::class, 'reset']);
});

// Storefront Auth endpoints (authenticated)
Route::middleware([
    'api',
    InitializeTenancyByDomain::class,
    PreventAccessFromCentralDomains::class,
    'auth:sanctum',
])->prefix('auth')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/user', [AuthController::class, 'user']);
    Route::put('/profile', [AuthController::class, 'updateProfile']);
    Route::put('/password', [AuthController::class, 'updatePassword']);
});

// Checkout endpoints (public - works with or without auth)
Route::middleware([
    'api',
    InitializeTenancyByDomain::class,
    PreventAccessFromCentralDomains::class,
])->prefix('checkout')->group(function () {
    Route::post('/place', [CheckoutController::class, 'placeOrder']);
    Route::post('/track', [CheckoutController::class, 'trackOrder']);
});

// Customer endpoints (authenticated)
Route::middleware([
    'api',
    InitializeTenancyByDomain::class,
    PreventAccessFromCentralDomains::class,
    'auth:sanctum',
])->prefix('customer')->name('customer.')->group(function () {
    Route::get('/dashboard-stats', [CustomerController::class, 'dashboardStats']);
    Route::get('/orders', [CustomerController::class, 'orders']);
    Route::get('/orders/{id}', [CustomerController::class, 'orderDetail']);
    Route::get('/orders/{id}/tracking', [CustomerController::class, 'orderTracking']);
    Route::get('/addresses', [CustomerController::class, 'addresses']);
    Route::post('/addresses', [CustomerController::class, 'storeAddress']);
    Route::put('/addresses/{id}', [CustomerController::class, 'updateAddress']);
    Route::delete('/addresses/{id}', [CustomerController::class, 'deleteAddress']);
    Route::get('/wishlist', [CustomerController::class, 'wishlist']);
    Route::post('/wishlist', [CustomerController::class, 'addToWishlist']);
    Route::delete('/wishlist/{id}', [CustomerController::class, 'removeFromWishlist']);
    Route::get('/reviews', [CustomerController::class, 'reviews']);
    Route::post('/reviews', [CustomerController::class, 'storeReview']);
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