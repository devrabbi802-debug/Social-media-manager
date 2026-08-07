<?php

declare(strict_types=1);

use App\Http\Controllers\AiSettingController;
use App\Http\Controllers\ConversationController;
use App\Http\Controllers\Dashboard\AccountingController;
use App\Http\Controllers\Dashboard\AccountingReportController;
use App\Http\Controllers\Dashboard\AccountingSettingController;
use App\Http\Controllers\Dashboard\AttributeController;
use App\Http\Controllers\Dashboard\BrandController;
use App\Http\Controllers\Dashboard\CategoryController;
use App\Http\Controllers\Dashboard\ChartOfAccountController;
use App\Http\Controllers\Dashboard\ImageMatchController;
use App\Http\Controllers\Dashboard\InventoryController;
use App\Http\Controllers\Dashboard\JournalEntryController;
use App\Http\Controllers\Dashboard\MoneyController;
use App\Http\Controllers\Dashboard\OrderController;
use App\Http\Controllers\Dashboard\PosController;
use App\Http\Controllers\Dashboard\PosReportController;
use App\Http\Controllers\Dashboard\PosSaleController;
use App\Http\Controllers\Dashboard\PosSessionController;
use App\Http\Controllers\Dashboard\PosSettingsController;
use App\Http\Controllers\Dashboard\ProductController;
use App\Http\Controllers\Dashboard\PurchaseController;
use App\Http\Controllers\Dashboard\PurchaseDirectController;
use App\Http\Controllers\Dashboard\PurchaseInvoiceController;
use App\Http\Controllers\Dashboard\PurchaseOrderController;
use App\Http\Controllers\Dashboard\PurchaseReceiptController;
use App\Http\Controllers\Dashboard\PurchaseReportController;
use App\Http\Controllers\Dashboard\PurchaseReturnController;
use App\Http\Controllers\Dashboard\PurchaseSettingController;
use App\Http\Controllers\Dashboard\RoleController;
use App\Http\Controllers\Dashboard\StockTransferController;
use App\Http\Controllers\Dashboard\SupplierController;
use App\Http\Controllers\Dashboard\SupplierPaymentController;
use App\Http\Controllers\Dashboard\UserController as TenantUserController;
use App\Http\Controllers\Dashboard\WarehouseController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\FacebookOAuthController;
use App\Http\Controllers\FacebookSettingController;
use App\Http\Controllers\StorefrontController;
use App\Http\Controllers\StorefrontSettingsController;
use App\Http\Controllers\Tenant\LanguageController;
use App\Http\Controllers\ZernioOAuthController;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Route;
use Stancl\Tenancy\Middleware\InitializeTenancyByDomain;
use Stancl\Tenancy\Middleware\PreventAccessFromCentralDomains;

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

$adminPrefix = config('app.admin_panel_prefix', 'ax7k9m');

Route::middleware([
    'web',
    InitializeTenancyByDomain::class,
    PreventAccessFromCentralDomains::class,
    'locale',
])->group(function () use ($adminPrefix) {

    // Language Switch (accessible from anywhere)
    Route::post('/language/switch', [LanguageController::class, 'switch'])->name('language.switch');

    // Admin Panel Routes — prefixed with unique slug
    Route::prefix($adminPrefix)->group(function () {

        // Auto-login from onboarding (one-time token)
        Route::get('/auto-login', function (Request $request) {
            $email = $request->query('email');
            $token = $request->query('token');

            if (! $email || ! $token) {
                return redirect(route('login'))->withErrors(['email' => 'লিংক অবৈধ।']);
            }

            $user = User::where('email', $email)->first();

            if (! $user || ! Hash::check($token, $user->remember_token)) {
                return redirect(route('login'))->withErrors(['email' => 'লিংক অবৈধ বা মেয়াদ শেষ।']);
            }

            // One-time token clear koro
            $user->forceFill(['remember_token' => null])->save();

            Auth::login($user);
            $request->session()->regenerate();

            return redirect(route('dashboard'))->with('success', 'স্বাগতম! আপনার সেটআপ সম্পন্ন হয়েছে।');
        })->name('tenant.auto.login');

        // Auth Routes
        Route::get('/login', function () {
            if (auth()->check()) {
                return redirect(route('dashboard'));
            }

            return view('auth.login');
        })->name('login');

        Route::post('/login', function (Request $request) {
            $credentials = $request->validate([
                'email' => 'required|email',
                'password' => 'required',
            ]);

            if (auth()->attempt($credentials)) {
                $request->session()->regenerate();

                return redirect()->intended(route('dashboard'));
            }

            return back()->withErrors([
                'email' => __('auth.failed'),
            ])->onlyInput('email');
        });

        Route::post('/logout', function (Request $request) {
            auth()->logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect('/');
        })->name('logout');

        // Dashboard Routes (authenticated users only)
        Route::middleware(['auth'])->group(function () {
            Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
            Route::middleware('permission:integration,list')->group(function () {
                Route::get('/integration', [DashboardController::class, 'integration'])->name('integration');
            });
            Route::get('/facebook/post', [DashboardController::class, 'facebookPost'])->name('facebook.post');
            Route::get('/settings', [DashboardController::class, 'settings'])->name('settings');
            Route::put('/settings/profile', [DashboardController::class, 'updateProfile'])->name('settings.profile.update');
            Route::put('/settings/password', [DashboardController::class, 'updatePassword'])->name('settings.password.update');
            Route::middleware('permission:settings,edit')->group(function () {
                Route::put('/settings/business', [DashboardController::class, 'updateBusinessSettings'])->name('settings.business.update');
                Route::put('/settings/business-info', [DashboardController::class, 'updateBusinessInfo'])->name('settings.business-info.update');
                Route::put('/settings/tone', [DashboardController::class, 'updateTone'])->name('settings.tone.update');
                Route::put('/settings/pricing', [DashboardController::class, 'updatePricing'])->name('settings.pricing.update');
                Route::put('/settings/faq', [DashboardController::class, 'updateFaq'])->name('settings.faq.update');
                Route::put('/settings/escalation', [DashboardController::class, 'updateEscalation'])->name('settings.escalation.update');
            });
            Route::get('/leads', [DashboardController::class, 'leads'])->name('leads');
            Route::get('/reports', [DashboardController::class, 'reports'])->name('reports');
            Route::get('/whatsapp/send', [DashboardController::class, 'whatsapp'])->name('whatsapp.send');
            // Inventory Routes
            Route::prefix('inventory')->name('inventory.')->group(function () {

                // Products (static routes age, dynamic pore)
                Route::middleware('permission:products,list')->group(function () {
                    Route::get('/products', [ProductController::class, 'index'])->name('products.index');
                    Route::get('/products/attributes', [ProductController::class, 'getAttributes'])->name('products.attributes');
                    Route::get('/products/variant-options', [ProductController::class, 'getVariantOptions'])->name('products.variant-options');
                    Route::get('/products/extra-fields', [ProductController::class, 'getExtraFields'])->name('products.extra-fields');
                });
                Route::middleware('permission:products,create')->group(function () {
                    Route::get('/products/create', [ProductController::class, 'create'])->name('products.create');
                    Route::post('/products', [ProductController::class, 'store'])->name('products.store');
                });
                Route::middleware('permission:products,list')->group(function () {
                    Route::get('/products/{product}', [ProductController::class, 'show'])->name('products.show');
                });
                Route::middleware('permission:products,edit')->group(function () {
                    Route::get('/products/{product}/edit', [ProductController::class, 'edit'])->name('products.edit');
                    Route::put('/products/{product}', [ProductController::class, 'update'])->name('products.update');
                    Route::post('/products/{product}/variants', [ProductController::class, 'storeVariant'])->name('products.variants.store');
                    Route::put('/products/{product}/variants/{variant}', [ProductController::class, 'updateVariant'])->name('products.variants.update');
                    Route::post('/products/{product}/generate-embeddings', [ProductController::class, 'generateEmbeddings'])->name('products.generate-embeddings');
                    Route::post('/products/{product}/generate-variant-embeddings', [ProductController::class, 'generateVariantEmbeddings'])->name('products.generate-variant-embeddings');
                    Route::post('/products/{product}/generate-text-embeddings', [ProductController::class, 'generateTextEmbeddings'])->name('products.generate-text-embeddings');
                });
                Route::middleware('permission:products,delete')->group(function () {
                    Route::delete('/products/{product}', [ProductController::class, 'destroy'])->name('products.destroy');
                    Route::delete('/products/{product}/variants/{variant}', [ProductController::class, 'destroyVariant'])->name('products.variants.destroy');
                });

                // Categories
                Route::middleware('permission:categories,list')->group(function () {
                    Route::get('/categories', [CategoryController::class, 'index'])->name('categories.index');
                    Route::get('/categories/{category}/edit', [CategoryController::class, 'edit'])->name('categories.edit');
                });
                Route::middleware('permission:categories,create')->group(function () {
                    Route::get('/categories/create', [CategoryController::class, 'create'])->name('categories.create');
                    Route::post('/categories', [CategoryController::class, 'store'])->name('categories.store');
                });
                Route::middleware('permission:categories,edit')->group(function () {
                    Route::put('/categories/{category}', [CategoryController::class, 'update'])->name('categories.update');
                });
                Route::middleware('permission:categories,delete')->group(function () {
                    Route::delete('/categories/{category}', [CategoryController::class, 'destroy'])->name('categories.destroy');
                });

                // Brands
                Route::middleware('permission:brands,list')->group(function () {
                    Route::get('/brands', [BrandController::class, 'index'])->name('brands.index');
                    Route::get('/brands/{brand}/edit', [BrandController::class, 'edit'])->name('brands.edit');
                });
                Route::middleware('permission:brands,create')->group(function () {
                    Route::get('/brands/create', [BrandController::class, 'create'])->name('brands.create');
                    Route::post('/brands', [BrandController::class, 'store'])->name('brands.store');
                });
                Route::middleware('permission:brands,edit')->group(function () {
                    Route::put('/brands/{brand}', [BrandController::class, 'update'])->name('brands.update');
                });
                Route::middleware('permission:brands,delete')->group(function () {
                    Route::delete('/brands/{brand}', [BrandController::class, 'destroy'])->name('brands.destroy');
                });

                // Attribute Templates
                Route::middleware('permission:attributes,list')->group(function () {
                    Route::get('/attributes', [AttributeController::class, 'index'])->name('attributes.index');
                    Route::get('/attributes/{attribute}/edit', [AttributeController::class, 'edit'])->name('attributes.edit');
                });
                Route::middleware('permission:attributes,create')->group(function () {
                    Route::get('/attributes/create', [AttributeController::class, 'create'])->name('attributes.create');
                    Route::post('/attributes', [AttributeController::class, 'store'])->name('attributes.store');
                });
                Route::middleware('permission:attributes,edit')->group(function () {
                    Route::put('/attributes/{attribute}', [AttributeController::class, 'update'])->name('attributes.update');
                });
                Route::middleware('permission:attributes,delete')->group(function () {
                    Route::delete('/attributes/{attribute}', [AttributeController::class, 'destroy'])->name('attributes.destroy');
                });

                // Warehouses
                Route::middleware('permission:warehouses,list')->group(function () {
                    Route::get('/warehouses', [WarehouseController::class, 'index'])->name('warehouses.index');
                    Route::get('/warehouses/{warehouse}/edit', [WarehouseController::class, 'edit'])->name('warehouses.edit');
                });
                Route::middleware('permission:warehouses,create')->group(function () {
                    Route::get('/warehouses/create', [WarehouseController::class, 'create'])->name('warehouses.create');
                    Route::post('/warehouses', [WarehouseController::class, 'store'])->name('warehouses.store');
                });
                Route::middleware('permission:warehouses,edit')->group(function () {
                    Route::put('/warehouses/{warehouse}', [WarehouseController::class, 'update'])->name('warehouses.update');
                });
                Route::middleware('permission:warehouses,delete')->group(function () {
                    Route::delete('/warehouses/{warehouse}', [WarehouseController::class, 'destroy'])->name('warehouses.destroy');
                });

                // Inventory Management
                Route::middleware('permission:inventory_dashboard,list')->group(function () {
                    Route::get('/', [InventoryController::class, 'index'])->name('index');
                });
                Route::middleware('permission:stock_movements,list')->group(function () {
                    Route::get('/movements', [InventoryController::class, 'movements'])->name('movements');
                });
                Route::middleware('permission:stock_movements,create')->group(function () {
                    Route::post('/stock-in', [InventoryController::class, 'stockIn'])->name('stock-in');
                    Route::post('/stock-out', [InventoryController::class, 'stockOut'])->name('stock-out');
                    Route::post('/adjust-stock', [InventoryController::class, 'adjustStock'])->name('adjust-stock');
                });

                // Stock Transfers
                Route::middleware('permission:stock_transfers,list')->group(function () {
                    Route::get('/transfers', [StockTransferController::class, 'index'])->name('transfers.index');
                });
                Route::middleware('permission:stock_transfers,create')->group(function () {
                    Route::post('/transfers', [StockTransferController::class, 'store'])->name('transfers.store');
                    Route::post('/transfers/{transfer}/complete', [StockTransferController::class, 'complete'])->name('transfers.complete');
                    Route::post('/transfers/{transfer}/cancel', [StockTransferController::class, 'cancel'])->name('transfers.cancel');
                });
                Route::middleware('permission:stock_transfers,delete')->group(function () {
                    Route::delete('/transfers/{transfer}', [StockTransferController::class, 'destroy'])->name('transfers.destroy');
                });

                // Alerts
                Route::middleware('permission:stock_alerts,list')->group(function () {
                    Route::get('/alerts', [InventoryController::class, 'alerts'])->name('alerts');
                });
                Route::middleware('permission:stock_alerts,create')->group(function () {
                    Route::post('/alerts', [InventoryController::class, 'storeAlert'])->name('alerts.store');
                });
                Route::middleware('permission:stock_alerts,edit')->group(function () {
                    Route::put('/alerts/{alert}', [InventoryController::class, 'updateAlert'])->name('alerts.update');
                });
                Route::middleware('permission:stock_alerts,delete')->group(function () {
                    Route::delete('/alerts/{alert}', [InventoryController::class, 'destroyAlert'])->name('alerts.destroy');
                });
            });

            // User Management (Roles & Staff)
            Route::middleware(['permission:user_management,list'])->group(function () {
                Route::prefix('roles')->name('roles.')->group(function () {
                    Route::get('/', [RoleController::class, 'index'])->name('index');
                    Route::middleware('permission:user_management,create')->group(function () {
                        Route::get('/create', [RoleController::class, 'create'])->name('create');
                        Route::post('/', [RoleController::class, 'store'])->name('store');
                    });
                    Route::middleware('permission:user_management,edit')->group(function () {
                        Route::get('/{role}/edit', [RoleController::class, 'edit'])->name('edit');
                        Route::put('/{role}', [RoleController::class, 'update'])->name('update');
                    });
                    Route::middleware('permission:user_management,delete')->group(function () {
                        Route::delete('/{role}', [RoleController::class, 'destroy'])->name('destroy');
                    });
                });

                Route::prefix('users')->name('users.')->group(function () {
                    Route::get('/', [TenantUserController::class, 'index'])->name('index');
                    Route::middleware('permission:user_management,create')->group(function () {
                        Route::get('/create', [TenantUserController::class, 'create'])->name('create');
                        Route::post('/', [TenantUserController::class, 'store'])->name('store');
                    });
                    Route::middleware('permission:user_management,edit')->group(function () {
                        Route::get('/{user}/edit', [TenantUserController::class, 'edit'])->name('edit');
                        Route::put('/{user}', [TenantUserController::class, 'update'])->name('update');
                    });
                    Route::middleware('permission:user_management,delete')->group(function () {
                        Route::delete('/{user}', [TenantUserController::class, 'destroy'])->name('destroy');
                    });
                });
            });

            // Order Management
            Route::middleware('permission:orders,list')->prefix('orders')->name('orders.')->group(function () {
                Route::get('/', [OrderController::class, 'index'])->name('index');
                Route::middleware('permission:orders,export')->group(function () {
                    Route::get('/export', [OrderController::class, 'export'])->name('export');
                });
                Route::middleware('permission:orders,view')->group(function () {
                    Route::get('/{order}', [OrderController::class, 'show'])->name('show');
                    Route::get('/{order}/print', [OrderController::class, 'print'])->name('print');
                });
                Route::middleware('permission:orders,edit')->group(function () {
                    Route::post('/bulk-update', [OrderController::class, 'bulkUpdate'])->name('bulk-update');
                    Route::post('/{order}/receive-payment', [OrderController::class, 'receivePayment'])->name('receive-payment');
                    Route::get('/{order}/edit', [OrderController::class, 'edit'])->name('edit');
                    Route::put('/{order}', [OrderController::class, 'update'])->name('update');
                });
            });

            // AI Setup
            Route::middleware('permission:ai_setup,list')->group(function () {
                Route::get('/ai-setup', [AiSettingController::class, 'index'])->name('ai.setup');
                Route::post('/ai-setup', [AiSettingController::class, 'store'])->name('ai.setup.store');
                Route::delete('/ai-setup/{aiSetting}', [AiSettingController::class, 'destroy'])->name('ai.setup.destroy');
                Route::post('/ai-setup/{aiSetting}/toggle', [AiSettingController::class, 'toggle'])->name('ai.setup.toggle');
                Route::get('/ai-setup/{aiSetting}/test', [AiSettingController::class, 'test'])->name('ai.setup.test');
            });

            // Facebook Settings (Integration)
            Route::middleware('permission:integration,list')->group(function () {
                Route::get('/facebook/settings', [FacebookSettingController::class, 'index'])->name('facebook.settings');
                Route::post('/facebook/settings', [FacebookSettingController::class, 'store'])->name('facebook.settings.store');
                Route::delete('/facebook/settings', [FacebookSettingController::class, 'destroy'])->name('facebook.settings.destroy');
                Route::post('/facebook/settings/toggle-ai-reply', [FacebookSettingController::class, 'toggleAiReply'])->name('facebook.settings.toggle.ai.reply');

                // Facebook OAuth
                Route::get('/facebook/connect', [FacebookOAuthController::class, 'redirect'])->name('facebook.redirect');
                Route::get('/facebook/callback', [FacebookOAuthController::class, 'callback'])->name('facebook.callback');
                Route::get('/facebook/select-page', [FacebookOAuthController::class, 'selectPage'])->name('facebook.select.page');
                Route::post('/facebook/connect-page', [FacebookOAuthController::class, 'connectSelectedPage'])->name('facebook.connect.page');

                // Zernio Integration
                Route::post('/facebook/settings/zernio', [ZernioOAuthController::class, 'storeApiKey'])->name('zernio.store.apikey');
                Route::get('/facebook/connect-zernio', [ZernioOAuthController::class, 'connectFacebook'])->name('zernio.connect.facebook');
                Route::get('/facebook/zernio/callback', [ZernioOAuthController::class, 'facebookCallback'])->name('zernio.facebook.callback');
                Route::get('/facebook/zernio/select-page', [ZernioOAuthController::class, 'selectPage'])->name('zernio.select.page');
                Route::post('/facebook/zernio/connect-page', [ZernioOAuthController::class, 'connectSelectedPage'])->name('zernio.connect.page');
                Route::post('/facebook/zernio/disconnect', [ZernioOAuthController::class, 'disconnect'])->name('zernio.disconnect');
            });

            // Conversations
            Route::middleware('permission:conversations,list')->group(function () {
                Route::get('/conversations', [ConversationController::class, 'index'])->name('conversations');
                Route::get('/conversations/{conversation}', [ConversationController::class, 'show'])->name('conversations.show');
            });

            // Image Matching (CLIP)
            Route::middleware('permission:image_match,list')->group(function () {
                Route::get('/image-match', [ImageMatchController::class, 'index'])->name('image-match.index');
                Route::post('/image-match', [ImageMatchController::class, 'match'])->name('image-match.match');
                Route::post('/image-match/url', [ImageMatchController::class, 'matchUrl'])->name('image-match.url');
            });

            // Accounting System
            Route::middleware('permission:accounting,list')->prefix('accounting')->name('accounting.')->group(function () {
                Route::get('/', [AccountingController::class, 'index'])->name('index');

                // Chart of Accounts
                Route::middleware('permission:chart_of_accounts,list')->prefix('chart-of-accounts')->name('chart-of-accounts.')->group(function () {
                    Route::get('/', [ChartOfAccountController::class, 'index'])->name('index');
                    Route::middleware('permission:chart_of_accounts,create')->group(function () {
                        Route::get('/create', [ChartOfAccountController::class, 'create'])->name('create');
                        Route::post('/', [ChartOfAccountController::class, 'store'])->name('store');
                    });
                    Route::middleware('permission:chart_of_accounts,edit')->group(function () {
                        Route::get('/{account}/edit', [ChartOfAccountController::class, 'edit'])->name('edit');
                        Route::put('/{account}', [ChartOfAccountController::class, 'update'])->name('update');
                    });
                    Route::middleware('permission:chart_of_accounts,delete')->group(function () {
                        Route::delete('/{account}', [ChartOfAccountController::class, 'destroy'])->name('destroy');
                    });
                });

                // Income & Expense (money in/out)
                Route::middleware('permission:accounting_money,list')->prefix('money')->name('money.')->group(function () {
                    Route::get('/', [MoneyController::class, 'index'])->name('index');
                    Route::middleware('permission:accounting_money,create')->group(function () {
                        Route::get('/create', [MoneyController::class, 'create'])->name('create');
                        Route::post('/', [MoneyController::class, 'store'])->name('store');
                    });
                });

                // Journal Entries
                Route::middleware('permission:journal_entries,list')->prefix('journal')->name('journal.')->group(function () {
                    Route::get('/', [JournalEntryController::class, 'index'])->name('index');
                    Route::middleware('permission:journal_entries,create')->group(function () {
                        Route::get('/create', [JournalEntryController::class, 'create'])->name('create');
                        Route::post('/', [JournalEntryController::class, 'store'])->name('store');
                    });
                    Route::get('/{entry}', [JournalEntryController::class, 'show'])->name('show');
                    Route::middleware('permission:journal_entries,reverse')->group(function () {
                        Route::post('/{entry}/reverse', [JournalEntryController::class, 'reverse'])->name('reverse');
                    });
                });

                // Reports
                Route::middleware('permission:accounting_reports,list')->prefix('reports')->name('reports.')->group(function () {
                    Route::get('/trial-balance', [AccountingReportController::class, 'trialBalance'])->name('trial-balance');
                    Route::get('/income-statement', [AccountingReportController::class, 'incomeStatement'])->name('income-statement');
                    Route::get('/balance-sheet', [AccountingReportController::class, 'balanceSheet'])->name('balance-sheet');
                    Route::get('/ledger', [AccountingReportController::class, 'ledger'])->name('ledger');
                    Route::get('/transactions', [AccountingReportController::class, 'transactions'])->name('transactions');
                });

                // Settings
                Route::middleware('permission:accounting_settings,list')->prefix('settings')->name('settings.')->group(function () {
                    Route::get('/', [AccountingSettingController::class, 'index'])->name('index');
                    Route::middleware('permission:accounting_settings,edit')->group(function () {
                        Route::put('/', [AccountingSettingController::class, 'update'])->name('update');
                    });
                });
            });

            // Storefront Settings (Web Setup)
            Route::middleware('permission:storefront_settings,list')->prefix('storefront-settings')->name('storefront-settings.')->group(function () {
                Route::get('/', [StorefrontSettingsController::class, 'index'])->name('index');
                Route::middleware('permission:storefront_settings,edit')->group(function () {
                    Route::put('/', [StorefrontSettingsController::class, 'update'])->name('update');
                    Route::post('/apply-theme', [StorefrontSettingsController::class, 'applyTheme'])->name('apply-theme');
                    Route::post('/upload-logo', [StorefrontSettingsController::class, 'uploadLogo'])->name('upload-logo');
                    Route::post('/upload-favicon', [StorefrontSettingsController::class, 'uploadFavicon'])->name('upload-favicon');
                });
            });

            // POS System
            Route::middleware('permission:pos_terminal,list')->prefix('pos')->name('pos.')->group(function () {
                Route::get('/', [PosController::class, 'index'])->name('index');
                Route::get('/products', [PosController::class, 'products'])->name('products');
                Route::middleware('permission:pos_terminal,create')->group(function () {
                    Route::post('/checkout', [PosController::class, 'checkout'])->name('checkout');
                    Route::post('/hold', [PosController::class, 'hold'])->name('hold');
                    Route::get('/resume/{order}', [PosController::class, 'resume'])->name('resume');
                });
                Route::middleware('permission:pos_terminal,hold')->group(function () {
                    Route::delete('/hold/{order}', [PosController::class, 'cancelHold'])->name('hold.cancel');
                });

                // Sales
                Route::middleware('permission:pos_sales,list')->prefix('sales')->name('sales.')->group(function () {
                    Route::get('/', [PosSaleController::class, 'index'])->name('index');
                    Route::get('/{order}', [PosSaleController::class, 'show'])->name('show');
                    Route::get('/{order}/receipt', [PosSaleController::class, 'receipt'])->name('receipt');
                    Route::middleware('permission:pos_sales,refund')->group(function () {
                        Route::post('/{order}/refund', [PosSaleController::class, 'refund'])->name('refund');
                    });
                });

                // Register Sessions
                Route::middleware('permission:pos_sessions,list')->prefix('sessions')->name('sessions.')->group(function () {
                    Route::get('/', [PosSessionController::class, 'index'])->name('index');
                    Route::get('/{session}', [PosSessionController::class, 'show'])->name('show');
                    Route::middleware('permission:pos_sessions,create')->group(function () {
                        Route::post('/', [PosSessionController::class, 'store'])->name('store');
                    });
                    Route::middleware('permission:pos_sessions,close')->group(function () {
                        Route::post('/{session}/close', [PosSessionController::class, 'close'])->name('close');
                        Route::post('/{session}/cash', [PosSessionController::class, 'cashEvent'])->name('cash');
                    });
                });

                // Reports & Settings
                Route::middleware('permission:pos_reports,list')->group(function () {
                    Route::get('/reports', [PosReportController::class, 'index'])->name('reports');
                });
                Route::middleware('permission:pos_settings,list')->group(function () {
                    Route::get('/settings', [PosSettingsController::class, 'index'])->name('settings');
                    Route::middleware('permission:pos_settings,edit')->group(function () {
                        Route::put('/settings', [PosSettingsController::class, 'update'])->name('settings.update');
                    });
                });
            });

            // Purchase System
            Route::middleware('permission:purchase_dashboard,list')->prefix('purchase')->name('purchase.')->group(function () {
                Route::get('/', [PurchaseController::class, 'index'])->name('index');
                Route::get('/products', [PurchaseController::class, 'products'])->name('products');
                Route::get('/suppliers-search', [PurchaseController::class, 'suppliers'])->name('suppliers-search');

                // Direct Purchase (PO + GRN combined)
                Route::middleware('permission:purchase_orders,create')->prefix('direct')->name('direct.')->group(function () {
                    Route::get('/create', [PurchaseDirectController::class, 'create'])->name('create');
                    Route::post('/', [PurchaseDirectController::class, 'store'])->name('store');
                });

                // Suppliers
                Route::prefix('suppliers')->name('suppliers.')->group(function () {
                    Route::middleware('permission:suppliers,create')->group(function () {
                        Route::get('/create', [SupplierController::class, 'create'])->name('create');
                        Route::post('/', [SupplierController::class, 'store'])->name('store');
                    });
                    Route::middleware('permission:suppliers,list')->group(function () {
                        Route::get('/', [SupplierController::class, 'index'])->name('index');
                        Route::get('/{supplier}', [SupplierController::class, 'show'])->name('show');
                    });
                    Route::middleware('permission:suppliers,edit')->group(function () {
                        Route::get('/{supplier}/edit', [SupplierController::class, 'edit'])->name('edit');
                        Route::put('/{supplier}', [SupplierController::class, 'update'])->name('update');
                    });
                    Route::middleware('permission:suppliers,delete')->group(function () {
                        Route::delete('/{supplier}', [SupplierController::class, 'destroy'])->name('destroy');
                    });
                });

                // Purchase Orders
                Route::prefix('orders')->name('orders.')->group(function () {
                    Route::middleware('permission:purchase_orders,create')->group(function () {
                        Route::get('/create', [PurchaseOrderController::class, 'create'])->name('create');
                        Route::post('/', [PurchaseOrderController::class, 'store'])->name('store');
                    });
                    Route::middleware('permission:purchase_orders,list')->group(function () {
                        Route::get('/', [PurchaseOrderController::class, 'index'])->name('index');
                        Route::get('/{order}', [PurchaseOrderController::class, 'show'])->name('show');
                        Route::get('/{order}/print', [PurchaseOrderController::class, 'print'])->name('print');
                    });
                    Route::middleware('permission:purchase_orders,edit')->group(function () {
                        Route::get('/{order}/edit', [PurchaseOrderController::class, 'edit'])->name('edit');
                        Route::put('/{order}', [PurchaseOrderController::class, 'update'])->name('update');
                        Route::post('/{order}/mark-ordered', [PurchaseOrderController::class, 'markOrdered'])->name('mark-ordered');
                        Route::post('/{order}/pay-advance', [PurchaseOrderController::class, 'payAdvance'])->name('pay-advance');
                        Route::post('/{order}/cancel', [PurchaseOrderController::class, 'cancel'])->name('cancel');
                    });
                    Route::middleware('permission:purchase_orders,delete')->group(function () {
                        Route::delete('/{order}', [PurchaseOrderController::class, 'destroy'])->name('destroy');
                    });
                });

                // Purchase Receipts (GRN)
                Route::prefix('receipts')->name('receipts.')->group(function () {
                    Route::middleware('permission:purchase_receipts,create')->group(function () {
                        Route::get('/create', [PurchaseReceiptController::class, 'create'])->name('create');
                        Route::post('/', [PurchaseReceiptController::class, 'store'])->name('store');
                    });
                    Route::middleware('permission:purchase_receipts,list')->group(function () {
                        Route::get('/', [PurchaseReceiptController::class, 'index'])->name('index');
                        Route::get('/{receipt}', [PurchaseReceiptController::class, 'show'])->name('show');
                        Route::get('/{receipt}/print', [PurchaseReceiptController::class, 'print'])->name('print');
                    });
                    Route::middleware('permission:purchase_receipts,delete')->group(function () {
                        Route::delete('/{receipt}', [PurchaseReceiptController::class, 'destroy'])->name('destroy');
                    });
                });

                // Purchase Invoices (Bills)
                Route::prefix('invoices')->name('invoices.')->group(function () {
                    Route::middleware('permission:purchase_invoices,create')->group(function () {
                        Route::get('/create', [PurchaseInvoiceController::class, 'create'])->name('create');
                        Route::post('/', [PurchaseInvoiceController::class, 'store'])->name('store');
                    });
                    Route::middleware('permission:purchase_invoices,list')->group(function () {
                        Route::get('/', [PurchaseInvoiceController::class, 'index'])->name('index');
                        Route::get('/{invoice}', [PurchaseInvoiceController::class, 'show'])->name('show');
                        Route::get('/{invoice}/print', [PurchaseInvoiceController::class, 'print'])->name('print');
                    });
                    Route::middleware('permission:purchase_invoices,edit')->group(function () {
                        Route::get('/{invoice}/edit', [PurchaseInvoiceController::class, 'edit'])->name('edit');
                        Route::put('/{invoice}', [PurchaseInvoiceController::class, 'update'])->name('update');
                    });
                    Route::middleware('permission:purchase_invoices,pay')->group(function () {
                        Route::post('/{invoice}/pay', [PurchaseInvoiceController::class, 'pay'])->name('pay');
                        Route::post('/{invoice}/cancel', [PurchaseInvoiceController::class, 'cancel'])->name('cancel');
                    });
                    Route::middleware('permission:purchase_invoices,delete')->group(function () {
                        Route::delete('/{invoice}', [PurchaseInvoiceController::class, 'destroy'])->name('destroy');
                    });
                });

                // Supplier Payments
                Route::prefix('payments')->name('payments.')->group(function () {
                    Route::middleware('permission:supplier_payments,create')->group(function () {
                        Route::get('/create', [SupplierPaymentController::class, 'create'])->name('create');
                        Route::post('/', [SupplierPaymentController::class, 'store'])->name('store');
                        Route::get('/open-invoices', [SupplierPaymentController::class, 'openInvoices'])->name('open-invoices');
                    });
                    Route::middleware('permission:supplier_payments,list')->group(function () {
                        Route::get('/', [SupplierPaymentController::class, 'index'])->name('index');
                    });
                    Route::middleware('permission:supplier_payments,delete')->group(function () {
                        Route::delete('/{payment}', [SupplierPaymentController::class, 'destroy'])->name('destroy');
                    });
                });

                // Purchase Returns
                Route::prefix('returns')->name('returns.')->group(function () {
                    Route::middleware('permission:purchase_returns,create')->group(function () {
                        Route::get('/create', [PurchaseReturnController::class, 'create'])->name('create');
                        Route::post('/', [PurchaseReturnController::class, 'store'])->name('store');
                    });
                    Route::middleware('permission:purchase_returns,list')->group(function () {
                        Route::get('/', [PurchaseReturnController::class, 'index'])->name('index');
                        Route::get('/{return}', [PurchaseReturnController::class, 'show'])->name('show');
                    });
                    Route::middleware('permission:purchase_returns,edit')->group(function () {
                        Route::post('/{return}/cancel', [PurchaseReturnController::class, 'cancel'])->name('cancel');
                    });
                });

                // Reports & Settings
                Route::middleware('permission:purchase_reports,list')->group(function () {
                    Route::get('/reports', [PurchaseReportController::class, 'index'])->name('reports');
                });
                Route::middleware('permission:purchase_settings,list')->group(function () {
                    Route::get('/settings', [PurchaseSettingController::class, 'index'])->name('settings');
                    Route::middleware('permission:purchase_settings,edit')->group(function () {
                        Route::put('/settings', [PurchaseSettingController::class, 'update'])->name('settings.update');
                    });
                });
            });

        });
    });

    // Storefront catch-all route (LAST - no auth required)
    // This serves the React SPA for all non-dashboard, non-auth routes
    // Optional {path?} param ensures "/" matches (path=null) without overwriting central GET /
    Route::get('/{path?}', [StorefrontController::class, 'index'])
        ->where('path', '.*')->name('storefront.spa');
});
