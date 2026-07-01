<?php

declare(strict_types=1);

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
    });
});
