<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Tenant;
use App\Http\Controllers\FacebookSettingController;

// Landing Page
Route::get('/', function () {
    return view('welcome');
});

// Features Page
Route::get('/features', function () {
    return view('features');
});

// Pricing Page
Route::get('/pricing', function () {
    return view('pricing');
});

// About Page
Route::get('/about', function () {
    return view('about');
});

// Contact Page
Route::get('/contact', function () {
    return view('contact');
});

// Auth Routes - Login
Route::get('/login', function () {
    return view('auth.login');
})->name('login');

Route::post('/login', function (Request $request) {
    $credentials = $request->validate([
        'email' => 'required|email',
        'password' => 'required',
    ]);

    if (Auth::attempt($credentials)) {
        $request->session()->regenerate();
        return redirect()->intended('/dashboard');
    }

    return back()->withErrors([
        'email' => 'ইমেইল বা পাসওয়ার্ড ভুল।',
    ])->onlyInput('email');
});

// Auth Routes - Register
Route::get('/register', function () {
    return view('auth.register');
})->name('register');

Route::post('/register', function (Request $request) {
    $validated = $request->validate([
        'name' => 'required|string|max:255',
        'email' => 'required|string|email|max:255',
        'phone' => 'required|string|max:20',
        'company' => 'nullable|string|max:255',
        'subdomain' => 'required|string|min:3|max:50|regex:/^[a-z0-9-]+$/|unique:tenants,id',
        'password' => 'required|string|min:8|confirmed',
    ]);

    // Tenant create koro
    $tenant = Tenant::create([
        'id' => $validated['subdomain'],
        'name' => $validated['company'] ?? $validated['name'],
        'email' => $validated['email'],
        'phone' => $validated['phone'],
        'company' => $validated['company'],
        'plan' => 'trial',
        'status' => 'active',
        'trial_ends_at' => now()->addDays(14),
    ]);

    // Domain create koro
    $tenant->domains()->create([
        'domain' => $validated['subdomain'] . '.' . config('app.domain'),
    ]);

    // Tenant database e user create koro
    $tenant->run(function () use ($validated) {
        $user = \App\Models\User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'phone' => $validated['phone'],
            'company' => $validated['company'],
            'password' => \Illuminate\Support\Facades\Hash::make($validated['password']),
        ]);

        Auth::login($user);
    });

    // Tenant subdomain e redirect koro
    return redirect()->to(
        'http://' . $validated['subdomain'] . '.' . config('app.domain') . '/dashboard'
    );
});

// Logout
Route::post('/logout', function (Request $request) {
    Auth::logout();
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

    Route::get('/facebook/settings', [FacebookSettingController::class, 'index'])->name('facebook.settings');
    Route::post('/facebook/settings', [FacebookSettingController::class, 'store'])->name('facebook.settings.store');
    Route::delete('/facebook/settings', [FacebookSettingController::class, 'destroy'])->name('facebook.settings.destroy');
});
