<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;
use Stancl\Tenancy\Middleware\InitializeTenancyByDomain;
use Stancl\Tenancy\Middleware\PreventAccessFromCentralDomains;
use App\Http\Controllers\FacebookSettingController;
use App\Http\Controllers\FacebookOAuthController;
use App\Http\Controllers\AiSettingController;
use App\Http\Controllers\ConversationController;

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
        Route::get('/dashboard', function () {
            return view('dashboard.index');
        })->name('dashboard');

        Route::get('/settings', function () {
            return view('dashboard.settings');
        })->name('settings');

        Route::get('/leads', function () {
            return view('dashboard.leads');
        })->name('leads');

        Route::get('/inventory', function () {
            return view('dashboard.inventory');
        })->name('inventory');

        Route::get('/reports', function () {
            return view('dashboard.reports');
        })->name('reports');

        Route::get('/whatsapp/send', function () {
            return view('dashboard.whatsapp');
        })->name('whatsapp.send');

        Route::get('/facebook/post', function () {
            return view('dashboard.facebook');
        })->name('facebook.post');

        Route::get('/inventory/add', function () {
            return view('dashboard.inventory-add');
        })->name('inventory.add');
        
        Route::get('/integration', function () {
            return view('dashboard.integration');
        })->name('integration');

        // AI Setup
        Route::get('/ai-setup', [AiSettingController::class, 'index'])->name('ai.setup');
        Route::post('/ai-setup', [AiSettingController::class, 'store'])->name('ai.setup.store');
        Route::delete('/ai-setup/{aiSetting}', [AiSettingController::class, 'destroy'])->name('ai.setup.destroy');
        Route::post('/ai-setup/{aiSetting}/toggle', [AiSettingController::class, 'toggle'])->name('ai.setup.toggle');
        Route::put('/ai-setup/{aiSetting}/priority', [AiSettingController::class, 'updatePriority'])->name('ai.setup.priority');
        Route::get('/ai-setup/{aiSetting}/test', [AiSettingController::class, 'test'])->name('ai.setup.test');

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
    });
});
