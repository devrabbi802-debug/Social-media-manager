<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Admin\UserController;

// Admin routes with web middleware (for session, errors, csrf)
Route::middleware(['web'])->group(function () {

    // Admin Login (guest only)
    Route::middleware('guest:admin')->group(function () {
        Route::get('/rootadmin/login', function () {
            if (Auth::guard('admin')->check()) {
                return redirect('/rootadmin/dashboard');
            }
            return view('admin.auth.login');
        })->name('admin.login');

        Route::post('/rootadmin/login', function (Request $request) {
            $credentials = $request->validate([
                'email' => 'required|email',
                'password' => 'required',
            ]);

            if (Auth::guard('admin')->attempt($credentials, $request->boolean('remember'))) {
                $request->session()->regenerate();
                return redirect('/rootadmin/dashboard');
            }

            return back()->withErrors([
                'email' => 'ইমেইল বা পাসওয়ার্ড ভুল।',
            ])->onlyInput('email');
        })->name('admin.login.submit');
    });

    // Admin Logout
    Route::post('/rootadmin/logout', function (Request $request) {
        Auth::guard('admin')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect('/rootadmin/login');
    })->name('admin.logout');

    // Admin Protected Routes
    Route::middleware(['admin'])->prefix('rootadmin')->group(function () {

        // Dashboard
        Route::get('/dashboard', function () {
            return view('admin.dashboard');
        })->name('admin.dashboard');

        Route::get('/', function () {
            return redirect('/rootadmin/dashboard');
        });

        // User Management
        Route::get('/users', [UserController::class, 'index'])->name('admin.users.index');
        Route::get('/users/create', [UserController::class, 'create'])->name('admin.users.create');
        Route::post('/users', [UserController::class, 'store'])->name('admin.users.store');
        Route::get('/users/{user}/edit', [UserController::class, 'edit'])->name('admin.users.edit');
        Route::put('/users/{user}', [UserController::class, 'update'])->name('admin.users.update');
        Route::delete('/users/{user}', [UserController::class, 'destroy'])->name('admin.users.destroy');

        // Lead Management (placeholder)
        Route::get('/leads', fn() => view('admin.placeholder', ['title' => 'লিড ম্যানেজমেন্ট']))->name('admin.leads.index');

        // Inventory (placeholder)
        Route::get('/inventory', fn() => view('admin.placeholder', ['title' => 'ইনভেন্টরি']))->name('admin.inventory.index');

        // WhatsApp (placeholder)
        Route::get('/whatsapp', fn() => view('admin.placeholder', ['title' => 'হোয়াটসঅ্যাপ ম্যানেজমেন্ট']))->name('admin.whatsapp.index');

        // Facebook (placeholder)
        Route::get('/facebook', fn() => view('admin.placeholder', ['title' => 'ফেসবুক ম্যানেজমেন্ট']))->name('admin.facebook.index');

        // Settings (placeholder)
        Route::get('/settings', fn() => view('admin.placeholder', ['title' => 'সেটিংস']))->name('admin.settings.index');

    });

});
