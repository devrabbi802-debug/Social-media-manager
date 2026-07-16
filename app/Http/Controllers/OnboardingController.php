<?php

namespace App\Http\Controllers;

use App\Models\BusinessCategory;
use App\Models\BusinessSetting;
use App\Models\Tenant;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class OnboardingController extends Controller
{
    private function cleanPaymentMethods(?array $methods): ?array
    {
        if (empty($methods)) {
            return null;
        }

        $cleaned = array_filter($methods, fn($m) => !empty($m['name']));
        $cleaned = array_values($cleaned);

        return !empty($cleaned) ? $cleaned : null;
    }

    public function index()
    {
        $categories = BusinessCategory::active()->ordered()->get();
        return view('onboarding.index', compact('categories'));
    }

    public function store(Request $request)
    {
        if (is_string($request->input('accepted_payment_methods'))) {
            $decoded = json_decode($request->input('accepted_payment_methods'), true);
            $request->merge(['accepted_payment_methods' => $decoded]);
        }
        if (is_string($request->input('delivery_areas'))) {
            $decoded = json_decode($request->input('delivery_areas'), true);
            $request->merge(['delivery_areas' => $decoded]);
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255',
            'phone' => 'required|string|max:20',
            'subdomain' => 'required|string|min:3|max:50|regex:/^[a-z0-9-]+$/|unique:tenants,id',
            'password' => 'required|string|min:8|confirmed',
            'business_name' => 'required|string|max:255',
            'category_id' => 'nullable|exists:business_categories,id',
            'custom_category_name' => 'nullable|string|max:255',
            'sub_category' => 'nullable|string|max:255',
            'persona_name' => 'required|string|max:255',
            'business_hours' => 'required|string|max:255',
            'off_hours_message' => 'nullable|string|max:500',
            'business_description' => 'required|string|max:1000',
            'formality_level' => 'required|in:formal,casual',
            'emoji_usage' => 'required|in:never,sometimes,often',
            'language_style' => 'required|in:shuddho_bangla,anjonio,banglish',
            'greeting_style' => 'required|string|max:255',
            'price_negotiation' => 'nullable|boolean',
            'negotiation_limit' => 'nullable|integer|min:0|max:100',
            'bulk_discount_rule' => 'nullable|string|max:500',
            'current_promo' => 'nullable|string|max:500',
            'delivery_areas' => 'nullable|array',
            'delivery_areas.*.name' => 'required_with:delivery_areas|string|max:255',
            'delivery_areas.*.price' => 'nullable|string|max:255',
            'delivery_time' => 'nullable|string|max:255',
            'delivery_partner' => 'nullable|string|max:255',
            'cod_available' => 'nullable|boolean',
            'accepted_payment_methods' => 'nullable|array',
            'accepted_payment_methods.*.name' => 'required_with:accepted_payment_methods|string|max:255',
            'accepted_payment_methods.*.details' => 'nullable|string|max:500',
            'advance_payment_required' => 'nullable|boolean',
            'advance_payment_percent' => 'nullable|integer|min:0|max:100',
            'advance_for_outside_dhaka' => 'nullable|boolean',
            'refund_policy' => 'nullable|string|max:1000',
            'exchange_policy' => 'nullable|string|max:1000',
            'order_process_message' => 'nullable|string|max:2000',
            'faq' => 'nullable|array',
            'faq.*.question' => 'nullable|string|max:500',
            'faq.*.answer' => 'nullable|string|max:1000',
            'custom_escalation_keywords' => 'nullable|string|max:500',
            'escalation_contact' => 'nullable|string|max:255',
            'logo' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
        ]);

        $extraFieldsData = $request->only([
            'size_chart', 'color_variants', 'fitting_guide', 'return_policy_days',
            'warranty_period', 'model_serial_required', 'product_condition', 'after_sales_service',
            'expiry_date_info', 'halal_certification', 'perishable_delivery', 'ingredients_allergens',
            'expiry_info', 'skin_type_suitability', 'authentic_guarantee', 'usage_instructions',
            'furniture_assembly_required', 'dimensions_info', 'furniture_delivery_method', 'installation_charge',
            'digital_delivery_method', 'license_validity', 'refund_policy', 'subscription_type',
            'made_to_order_days', 'customization_options', 'no_return_policy', 'artisan_info',
            'prescription_required', 'storage_condition', 'regulatory_disclaimer', 'dosage_info',
        ]);

        if (empty($validated['category_id']) && empty($validated['custom_category_name'])) {
            return back()->withInput()->withErrors([
                'category_id' => 'ক্যাটাগরি বাছুন বা নতুন ক্যাটাগরির নাম লিখুন।',
            ]);
        }

        $extraFieldsData = array_filter($extraFieldsData, fn($v) => $v !== null && $v !== '');

        $categoryId = $validated['category_id'] ?? null;
        $tenant = null;

        try {
            if (empty($categoryId) && !empty($validated['custom_category_name'])) {
                $slug = \Illuminate\Support\Str::slug($validated['custom_category_name']);
                $category = BusinessCategory::firstOrCreate(
                    ['slug' => $slug],
                    [
                        'name' => $validated['custom_category_name'],
                        'icon' => '📦',
                        'is_active' => true,
                        'sort_order' => 99,
                    ]
                );
                $categoryId = $category->id;
            }

            $tenant = Tenant::create([
                'id' => $validated['subdomain'],
                'name' => $validated['business_name'] ?? $validated['name'],
                'email' => $validated['email'],
                'phone' => $validated['phone'],
                'company' => $validated['business_name'] ?? null,
                'plan' => 'trial',
                'status' => 'active',
                'trial_ends_at' => now()->addDays(14),
            ]);

            $tenant->domains()->create([
                'domain' => $validated['subdomain'] . '.' . config('app.domain'),
            ]);
        } catch (\Exception $e) {
            if ($tenant) {
                $tenant->delete();
            }
            return back()->withInput()->withErrors([
                'error' => 'সেটআপে সমস্যা হয়েছে: ' . $e->getMessage(),
            ]);
        }

        $createdUser = null;
        $loginToken = null;

        try {
            $loginToken = Str::random(64);

            $tenant->run(function () use ($validated, $extraFieldsData, $request, $categoryId, &$createdUser, $loginToken) {
                $user = \App\Models\User::create([
                    'name' => $validated['name'],
                    'email' => $validated['email'],
                    'phone' => $validated['phone'],
                    'company' => $validated['business_name'],
                    'password' => Hash::make($validated['password']),
                ]);

                // remember_token fillable e nai, tai alada set koro
                $user->forceFill(['remember_token' => Hash::make($loginToken)])->save();

                $createdUser = $user;

                $logoPath = null;
                if ($request->hasFile('logo')) {
                    $logoPath = $request->file('logo')->store('logos', 'public');
                }

                $faq = collect($request->input('faq', []))
                    ->filter(fn($item) => !empty($item['question']) && !empty($item['answer']))
                    ->values()
                    ->toArray();

                BusinessSetting::create([
                    'user_id' => $user->id,
                    'business_name' => $validated['business_name'],
                    'category_id' => $categoryId,
                    'sub_category' => $validated['sub_category'] ?? null,
                    'persona_name' => $validated['persona_name'],
                    'business_hours' => $validated['business_hours'],
                    'off_hours_message' => $validated['off_hours_message'] ?? null,
                    'business_description' => $validated['business_description'],
                    'formality_level' => $validated['formality_level'],
                    'emoji_usage' => $validated['emoji_usage'],
                    'language_style' => $validated['language_style'],
                    'greeting_style' => $validated['greeting_style'],
                    'price_negotiation' => $request->boolean('price_negotiation'),
                    'negotiation_limit' => $validated['negotiation_limit'] ?? 0,
                    'bulk_discount_rule' => $validated['bulk_discount_rule'] ?? null,
                    'current_promo' => $validated['current_promo'] ?? null,
                    'delivery_areas' => $this->cleanPaymentMethods($validated['delivery_areas'] ?? null),
                    'delivery_time' => $validated['delivery_time'] ?? null,
                    'delivery_partner' => $validated['delivery_partner'] ?? null,
                    'cod_available' => $request->boolean('cod_available', true),
                    'accepted_payment_methods' => $this->cleanPaymentMethods($request->input('accepted_payment_methods')),
                    'advance_payment_required' => $request->boolean('advance_payment_required'),
                    'advance_payment_percent' => $validated['advance_payment_percent'] ?? 0,
                    'advance_for_outside_dhaka' => $request->boolean('advance_for_outside_dhaka'),
                    'refund_policy' => $validated['refund_policy'] ?? null,
                    'exchange_policy' => $validated['exchange_policy'] ?? null,
                    'order_process_message' => $validated['order_process_message'] ?? null,
                    'custom_escalation_keywords' => $validated['custom_escalation_keywords'] ?? null,
                    'escalation_contact' => $validated['escalation_contact'] ?? null,
                    'extra_fields_data' => !empty($extraFieldsData) ? $extraFieldsData : null,
                    'faq' => !empty($faq) ? $faq : null,
                    'logo_path' => $logoPath,
                ]);
            });
        } catch (\Exception $e) {
            if ($tenant) {
                $tenant->delete();
            }

            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();
            return back()->withInput()->withErrors([
                'error' => 'সেটআপে সমস্যা হয়েছে: ' . $e->getMessage(),
            ]);
        }

        // Cross-domain auto-login via one-time token
        $tenantDomain = $validated['subdomain'] . '.' . config('app.domain');
        $email = $validated['email'];

        return redirect()->to(
            'http://' . $tenantDomain . '/auto-login?email=' . urlencode($email) . '&token=' . $loginToken
        );
    }
}
