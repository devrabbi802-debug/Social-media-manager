<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Admin\UserController;

Route::middleware(['web'])->group(function () {

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
                'email' => 'Invalid email or password.',
            ])->onlyInput('email');
        })->name('admin.login.submit');
    });

    Route::post('/rootadmin/logout', function (Request $request) {
        Auth::guard('admin')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect('/rootadmin/login');
    })->name('admin.logout');

    Route::middleware(['admin'])->prefix('rootadmin')->group(function () {

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

        // Placeholder routes
        Route::get('/leads', fn() => view('admin.placeholder', ['title' => 'Lead Management']))->name('admin.leads.index');
        Route::get('/inventory', fn() => view('admin.placeholder', ['title' => 'Inventory']))->name('admin.inventory.index');
        Route::get('/whatsapp', fn() => view('admin.placeholder', ['title' => 'WhatsApp']))->name('admin.whatsapp.index');
        Route::get('/facebook', fn() => view('admin.placeholder', ['title' => 'Facebook']))->name('admin.facebook.index');
        Route::get('/settings', fn() => view('admin.placeholder', ['title' => 'Settings']))->name('admin.settings.index');

    });

});
