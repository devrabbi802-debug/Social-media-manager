<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

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
        Route::get('/dashboard', function () {
            return view('admin.dashboard');
        })->name('admin.dashboard');

        Route::get('/', function () {
            return redirect('/rootadmin/dashboard');
        });
    });

});
