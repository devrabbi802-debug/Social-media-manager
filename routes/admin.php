<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\TenantController;
use App\Http\Controllers\Admin\AiSystemPromptController;
use App\Http\Controllers\Admin\BusinessCategoryController;

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

        // Tenant Management
        Route::get('/tenants', [TenantController::class, 'index'])->name('admin.tenants.index');
        Route::get('/tenants/create', [TenantController::class, 'create'])->name('admin.tenants.create');
        Route::post('/tenants', [TenantController::class, 'store'])->name('admin.tenants.store');
        Route::get('/tenants/{tenant}/edit', [TenantController::class, 'edit'])->name('admin.tenants.edit');
        Route::put('/tenants/{tenant}', [TenantController::class, 'update'])->name('admin.tenants.update');
        Route::delete('/tenants/{tenant}', [TenantController::class, 'destroy'])->name('admin.tenants.destroy');

        // AI System Prompt
        Route::get('/ai-system-prompt', [AiSystemPromptController::class, 'index'])->name('admin.ai-prompt.index');
        Route::put('/ai-system-prompt', [AiSystemPromptController::class, 'update'])->name('admin.ai-prompt.update');

        // Business Categories
        Route::get('/business-categories', [BusinessCategoryController::class, 'index'])->name('admin.business-categories.index');
        Route::get('/business-categories/create', [BusinessCategoryController::class, 'create'])->name('admin.business-categories.create');
        Route::post('/business-categories', [BusinessCategoryController::class, 'store'])->name('admin.business-categories.store');
        Route::get('/business-categories/{businessCategory}/edit', [BusinessCategoryController::class, 'edit'])->name('admin.business-categories.edit');
        Route::put('/business-categories/{businessCategory}', [BusinessCategoryController::class, 'update'])->name('admin.business-categories.update');
        Route::delete('/business-categories/{businessCategory}', [BusinessCategoryController::class, 'destroy'])->name('admin.business-categories.destroy');

    });

});
