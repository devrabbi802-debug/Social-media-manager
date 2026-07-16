<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Models\Tenant;
use App\Http\Controllers\FacebookSettingController;
use App\Http\Controllers\FacebookWebhookController;
use App\Http\Controllers\OnboardingController;
use App\Http\Controllers\SubdomainController;
use App\Http\Controllers\ZernioOAuthController;
use App\Http\Middleware\PreventAccessFromNonCentralDomains;

// Webhook routes — accessible from any domain (Facebook/Zernio calls via tunnel)
Route::get('/webhook/facebook', [FacebookWebhookController::class, 'verify']);
Route::post('/webhook/facebook', [FacebookWebhookController::class, 'handle']);
Route::post('/webhook/zernio', [FacebookWebhookController::class, 'handleZernio']);

// Central routes — only accessible on central domains
Route::middleware(PreventAccessFromNonCentralDomains::class)->group(function () {

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

    // Auth Routes - Login (redirect to onboarding — tenant users login on their subdomain)
    Route::get('/login', function () {
        return redirect()->route('onboarding');
    })->name('login');

    // Auth Routes - Register (redirect to onboarding)
    Route::get('/register', function () {
        return redirect()->route('onboarding');
    })->name('register');

    // Onboarding Wizard
    Route::get('/onboarding', [OnboardingController::class, 'index'])->name('onboarding');
    Route::post('/onboarding', [OnboardingController::class, 'store'])->name('onboarding.store');

    // Check subdomain availability
    Route::post('/check-subdomain', [SubdomainController::class, 'check'])->name('check-subdomain');

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
            return view('tenant.index');
        })->name('dashboard');

        Route::get('/settings', function () {
            return view('tenant.settings');
        })->name('settings');

        Route::put('/settings/profile', function (Request $request) {
            $validated = $request->validate([
                'name' => 'required|string|max:255',
                'email' => 'required|email|max:255',
                'phone' => 'nullable|string|max:50',
                'company' => 'nullable|string|max:255',
            ]);

            auth()->user()->update($validated);

            return redirect()->route('settings')->with('success', 'প্রোফাইল আপডেট হয়েছে!');
        });

        Route::put('/settings/password', function (Request $request) {
            $validated = $request->validate([
                'current_password' => 'required',
                'password' => 'required|min:6|confirmed',
            ]);

            if (!Hash::check($validated['current_password'], auth()->user()->password)) {
                return back()->withErrors(['current_password' => 'বর্তমান পাসওয়ার্ড সঠিক নয়।']);
            }

            auth()->user()->update(['password' => $validated['password']]);

            return redirect()->route('settings')->with('success', 'পাসওয়ার্ড আপডেট হয়েছে!');
        });

        Route::get('/leads', function () {
            return view('tenant.leads');
        })->name('leads');

        Route::get('/inventory', function () {
            return view('tenant.inventory');
        })->name('inventory');

        Route::get('/reports', function () {
            return view('tenant.reports');
        })->name('reports');

        Route::get('/whatsapp/send', function () {
            return view('tenant.whatsapp');
        })->name('whatsapp.send');

        Route::get('/facebook/post', function () {
            return view('tenant.facebook');
        })->name('facebook.post');

        Route::get('/inventory/add', function () {
            return view('tenant.inventory-add');
        })->name('inventory.add');

        Route::get('/integration', function () {
            return view('tenant.integration');
        })->name('integration');

        Route::get('/facebook/settings', [FacebookSettingController::class, 'index'])->name('facebook.settings');
        Route::post('/facebook/settings', [FacebookSettingController::class, 'store'])->name('facebook.settings.store');
        Route::delete('/facebook/settings', [FacebookSettingController::class, 'destroy'])->name('facebook.settings.destroy');
        Route::post('/facebook/settings/toggle-ai-reply', [FacebookSettingController::class, 'toggleAiReply'])->name('facebook.settings.toggle.ai.reply');
    });
});

Route::post('/facebook/zernio/test-webhook', [ZernioOAuthController::class, 'testWebhook'])->name('zernio.test.webhook')
    ->middleware(['web', Stancl\Tenancy\Middleware\InitializeTenancyByDomain::class, 'auth']);
