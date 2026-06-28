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
        Route::post('/users/{admin}/login-as', [UserController::class, 'loginAs'])->name('admin.users.login-as');

    });

});
