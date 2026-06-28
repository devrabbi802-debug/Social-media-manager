<?php

use Illuminate\Support\Facades\Route;

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

// Auth Routes
Route::get('/login', function () {
    return view('auth.login');
})->name('login');

Route::get('/register', function () {
    return view('auth.register');
})->name('register');

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
});
