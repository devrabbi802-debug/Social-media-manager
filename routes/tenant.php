<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;
use Stancl\Tenancy\Middleware\InitializeTenancyByDomain;
use Stancl\Tenancy\Middleware\PreventAccessFromCentralDomains;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\FacebookSettingController;
use App\Http\Controllers\FacebookOAuthController;
use App\Http\Controllers\AiSettingController;
use App\Http\Controllers\ConversationController;
use App\Http\Controllers\Dashboard\ProductController;
use App\Http\Controllers\Dashboard\CategoryController;
use App\Http\Controllers\Dashboard\BrandController;
use App\Http\Controllers\Dashboard\AttributeTemplateController;
use App\Http\Controllers\Dashboard\WarehouseController;
use App\Http\Controllers\Dashboard\InventoryController;
use App\Http\Controllers\Dashboard\ImageMatchController;

/*
|--------------------------------------------------------------------------
| Tenant Routes
|--------------------------------------------------------------------------
|
| Here you can register the tenant routes for your application.
| These routes are loaded by the TenantRouteServiceProvider.
|
| Feel free to customize them however you want. Good luck!
|
*/

Route::middleware([
    'web',
    InitializeTenancyByDomain::class,
    PreventAccessFromCentralDomains::class,
])->group(function () {

    // Auth Routes
    Route::get('/login', function () {
        if (auth()->check()) {
            return redirect('/dashboard');
        }
        return view('auth.login');
    })->name('login');

    Route::post('/login', function (\Illuminate\Http\Request $request) {
        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        if (auth()->attempt($credentials)) {
            $request->session()->regenerate();
            return redirect()->intended('/dashboard');
        }

        return back()->withErrors([
            'email' => 'ইমেইল বা পাসওয়ার্ড ভুল।',
        ])->onlyInput('email');
    });

    Route::post('/logout', function (\Illuminate\Http\Request $request) {
        auth()->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect('/');
    })->name('logout');

    // Dashboard Routes (authenticated users only)
    Route::middleware(['auth'])->group(function () {
        Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
        Route::get('/integration', [DashboardController::class, 'integration'])->name('integration');
        Route::get('/facebook/post', [DashboardController::class, 'facebookPost'])->name('facebook.post');
        Route::get('/settings', [DashboardController::class, 'settings'])->name('settings');
        Route::get('/leads', [DashboardController::class, 'leads'])->name('leads');
        Route::get('/reports', [DashboardController::class, 'reports'])->name('reports');
        Route::get('/whatsapp/send', [DashboardController::class, 'whatsapp'])->name('whatsapp.send');
        // Inventory Routes
        Route::prefix('inventory')->name('inventory.')->group(function () {

            // Products (static routes age, dynamic pore)
            Route::get('/products', [ProductController::class, 'index'])->name('products.index');
            Route::get('/products/create', [ProductController::class, 'create'])->name('products.create');
            Route::post('/products', [ProductController::class, 'store'])->name('products.store');
            Route::get('/products/attributes', [ProductController::class, 'getAttributes'])->name('products.attributes');
            Route::get('/products/variant-options', [ProductController::class, 'getVariantOptions'])->name('products.variant-options');
            Route::get('/products/{product}', [ProductController::class, 'show'])->name('products.show');
            Route::get('/products/{product}/edit', [ProductController::class, 'edit'])->name('products.edit');
            Route::put('/products/{product}', [ProductController::class, 'update'])->name('products.update');
            Route::delete('/products/{product}', [ProductController::class, 'destroy'])->name('products.destroy');

            // Product Variants
            Route::post('/products/{product}/variants', [ProductController::class, 'storeVariant'])->name('products.variants.store');
            Route::put('/products/{product}/variants/{variant}', [ProductController::class, 'updateVariant'])->name('products.variants.update');
            Route::delete('/products/{product}/variants/{variant}', [ProductController::class, 'destroyVariant'])->name('products.variants.destroy');

            // Product Embeddings
            Route::post('/products/{product}/generate-embeddings', [ProductController::class, 'generateEmbeddings'])->name('products.generate-embeddings');

            // Categories
            Route::get('/categories', [CategoryController::class, 'index'])->name('categories.index');
            Route::get('/categories/create', [CategoryController::class, 'create'])->name('categories.create');
            Route::post('/categories', [CategoryController::class, 'store'])->name('categories.store');
            Route::get('/categories/{category}/edit', [CategoryController::class, 'edit'])->name('categories.edit');
            Route::put('/categories/{category}', [CategoryController::class, 'update'])->name('categories.update');
            Route::delete('/categories/{category}', [CategoryController::class, 'destroy'])->name('categories.destroy');

            // Brands
            Route::get('/brands', [BrandController::class, 'index'])->name('brands.index');
            Route::get('/brands/create', [BrandController::class, 'create'])->name('brands.create');
            Route::post('/brands', [BrandController::class, 'store'])->name('brands.store');
            Route::get('/brands/{brand}/edit', [BrandController::class, 'edit'])->name('brands.edit');
            Route::put('/brands/{brand}', [BrandController::class, 'update'])->name('brands.update');
            Route::delete('/brands/{brand}', [BrandController::class, 'destroy'])->name('brands.destroy');

            // Attribute Templates
            Route::get('/attributes', [AttributeTemplateController::class, 'index'])->name('attributes.index');
            Route::get('/attributes/create', [AttributeTemplateController::class, 'create'])->name('attributes.create');
            Route::post('/attributes', [AttributeTemplateController::class, 'store'])->name('attributes.store');
            Route::get('/attributes/{attribute}/edit', [AttributeTemplateController::class, 'edit'])->name('attributes.edit');
            Route::put('/attributes/{attribute}', [AttributeTemplateController::class, 'update'])->name('attributes.update');
            Route::delete('/attributes/{attribute}', [AttributeTemplateController::class, 'destroy'])->name('attributes.destroy');

            // Warehouses
            Route::get('/warehouses', [WarehouseController::class, 'index'])->name('warehouses.index');
            Route::get('/warehouses/create', [WarehouseController::class, 'create'])->name('warehouses.create');
            Route::post('/warehouses', [WarehouseController::class, 'store'])->name('warehouses.store');
            Route::get('/warehouses/{warehouse}/edit', [WarehouseController::class, 'edit'])->name('warehouses.edit');
            Route::put('/warehouses/{warehouse}', [WarehouseController::class, 'update'])->name('warehouses.update');
            Route::delete('/warehouses/{warehouse}', [WarehouseController::class, 'destroy'])->name('warehouses.destroy');

            // Inventory Management
            Route::get('/', [InventoryController::class, 'index'])->name('index');
            Route::get('/movements', [InventoryController::class, 'movements'])->name('movements');
            Route::post('/stock-in', [InventoryController::class, 'stockIn'])->name('stock-in');
            Route::post('/stock-out', [InventoryController::class, 'stockOut'])->name('stock-out');
            Route::post('/adjust-stock', [InventoryController::class, 'adjustStock'])->name('adjust-stock');

            // Stock Transfers
            Route::get('/transfers', [\App\Http\Controllers\Dashboard\StockTransferController::class, 'index'])->name('transfers.index');
            Route::post('/transfers', [\App\Http\Controllers\Dashboard\StockTransferController::class, 'store'])->name('transfers.store');
            Route::post('/transfers/{transfer}/complete', [\App\Http\Controllers\Dashboard\StockTransferController::class, 'complete'])->name('transfers.complete');
            Route::post('/transfers/{transfer}/cancel', [\App\Http\Controllers\Dashboard\StockTransferController::class, 'cancel'])->name('transfers.cancel');
            Route::delete('/transfers/{transfer}', [\App\Http\Controllers\Dashboard\StockTransferController::class, 'destroy'])->name('transfers.destroy');

            // Alerts
            Route::get('/alerts', [InventoryController::class, 'alerts'])->name('alerts');
            Route::post('/alerts', [InventoryController::class, 'storeAlert'])->name('alerts.store');
            Route::put('/alerts/{alert}', [InventoryController::class, 'updateAlert'])->name('alerts.update');
            Route::delete('/alerts/{alert}', [InventoryController::class, 'destroyAlert'])->name('alerts.destroy');
        });

        // AI Setup
        Route::get('/ai-setup', [AiSettingController::class, 'index'])->name('ai.setup');
        Route::post('/ai-setup', [AiSettingController::class, 'store'])->name('ai.setup.store');
        Route::delete('/ai-setup/{aiSetting}', [AiSettingController::class, 'destroy'])->name('ai.setup.destroy');
        Route::post('/ai-setup/{aiSetting}/toggle', [AiSettingController::class, 'toggle'])->name('ai.setup.toggle');
        Route::put('/ai-setup/{aiSetting}/priority', [AiSettingController::class, 'updatePriority'])->name('ai.setup.priority');
        Route::get('/ai-setup/{aiSetting}/test', [AiSettingController::class, 'test'])->name('ai.setup.test');

        // Facebook Settings
        Route::get('/facebook/settings', [FacebookSettingController::class, 'index'])->name('facebook.settings');
        Route::post('/facebook/settings', [FacebookSettingController::class, 'store'])->name('facebook.settings.store');
        Route::delete('/facebook/settings', [FacebookSettingController::class, 'destroy'])->name('facebook.settings.destroy');
        Route::post('/facebook/settings/toggle-ai-reply', [FacebookSettingController::class, 'toggleAiReply'])->name('facebook.settings.toggle.ai.reply');

        // Conversations
        Route::get('/conversations', [ConversationController::class, 'index'])->name('conversations');
        Route::get('/conversations/{conversation}', [ConversationController::class, 'show'])->name('conversations.show');

        // Facebook OAuth
        Route::get('/facebook/connect', [FacebookOAuthController::class, 'redirect'])->name('facebook.redirect');
        Route::get('/facebook/callback', [FacebookOAuthController::class, 'callback'])->name('facebook.callback');
        Route::get('/facebook/select-page', [FacebookOAuthController::class, 'selectPage'])->name('facebook.select.page');
        Route::post('/facebook/connect-page', [FacebookOAuthController::class, 'connectSelectedPage'])->name('facebook.connect.page');

        // Image Matching (CLIP)
        Route::get('/image-match', [ImageMatchController::class, 'index'])->name('image-match.index');
        Route::post('/image-match', [ImageMatchController::class, 'match'])->name('image-match.match');
        Route::post('/image-match/url', [ImageMatchController::class, 'matchUrl'])->name('image-match.url');
    });
});
