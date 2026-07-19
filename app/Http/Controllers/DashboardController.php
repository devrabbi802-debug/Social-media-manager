<?php

namespace App\Http\Controllers;

use App\Models\BusinessCategory;
use App\Models\BusinessSetting;
use App\Models\Conversation;
use App\Models\FacebookSetting;
use App\Models\Message;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class DashboardController extends Controller
{
    public function index()
    {
        $user = auth()->user();
        $facebookSetting = FacebookSetting::where('user_id', $user->id)->first();

        $todayMessages = Message::whereDate('created_at', today())->count();
        $totalConversations = Conversation::count();
        $totalMessages = Message::count();
        $aiReplies = Message::where('direction', 'outgoing')->count();

        $recentConversations = Conversation::with('latestMessage')
            ->orderBy('last_message_at', 'desc')
            ->limit(5)
            ->get();

        return view('tenant.index', compact(
            'facebookSetting',
            'todayMessages',
            'totalConversations',
            'totalMessages',
            'aiReplies',
            'recentConversations'
        ));
    }

    public function integration()
    {
        return view('tenant.integration');
    }

    public function facebookPost()
    {
        return view('tenant.facebook');
    }

    public function settings()
    {
        $businessSetting = BusinessSetting::firstOrCreate(['user_id' => auth()->id()]);
        $categories = DB::connection('mysql')->table('business_categories')
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();
        return view('tenant.settings', compact('businessSetting', 'categories'));
    }

    public function updateBusinessSettings(Request $request)
    {
        // Decode JSON string from hidden input to array before validation
        if (is_string($request->input('accepted_payment_methods'))) {
            $decoded = json_decode($request->input('accepted_payment_methods'), true);
            $request->merge(['accepted_payment_methods' => $decoded]);
        }
        if (is_string($request->input('delivery_areas'))) {
            $decoded = json_decode($request->input('delivery_areas'), true);
            $request->merge(['delivery_areas' => $decoded]);
        }

        $validated = $request->validate([
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
            'logo' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
        ]);

        $businessSetting = BusinessSetting::where('user_id', auth()->id())->first();

        if (!$businessSetting) {
            return back()->withErrors(['error' => 'বিজনেস সেটিংস পাওয়া যায়নি।']);
        }

        $paymentMethods = $request->input('accepted_payment_methods');
        if (is_array($paymentMethods)) {
            $paymentMethods = array_filter($paymentMethods, fn($m) => !empty($m['name']));
            $paymentMethods = array_values($paymentMethods);
            $paymentMethods = !empty($paymentMethods) ? $paymentMethods : null;
        } else {
            $paymentMethods = null;
        }

        // Handle logo upload
        $logoPath = $businessSetting->logo_path;
        if ($request->hasFile('logo')) {
            // Delete old logo if exists
            if ($logoPath && \Illuminate\Support\Facades\Storage::disk('public')->exists($logoPath)) {
                \Illuminate\Support\Facades\Storage::disk('public')->delete($logoPath);
            }
            $logoPath = $request->file('logo')->store('logos', 'public');
        }

        $deliveryAreas = $validated['delivery_areas'] ?? null;
        if (is_array($deliveryAreas)) {
            $deliveryAreas = array_filter($deliveryAreas, fn($a) => !empty($a['name']));
            $deliveryAreas = array_values($deliveryAreas);
            $deliveryAreas = !empty($deliveryAreas) ? $deliveryAreas : null;
        } else {
            $deliveryAreas = null;
        }

        $businessSetting->update([
            'delivery_areas' => $deliveryAreas,
            'delivery_time' => $validated['delivery_time'] ?? null,
            'delivery_partner' => $validated['delivery_partner'] ?? null,
            'cod_available' => $request->boolean('cod_available', true),
            'accepted_payment_methods' => $paymentMethods,
            'advance_payment_required' => $request->boolean('advance_payment_required'),
            'advance_payment_percent' => $validated['advance_payment_percent'] ?? 0,
            'advance_for_outside_dhaka' => $request->boolean('advance_for_outside_dhaka'),
            'refund_policy' => $validated['refund_policy'] ?? null,
            'exchange_policy' => $validated['exchange_policy'] ?? null,
            'order_process_message' => $validated['order_process_message'] ?? null,
            'logo_path' => $logoPath,
        ]);

        return back()->with(['success' => 'বিজনেস সেটিংস আপডেট হয়েছে!', 'active_tab' => 'delivery']);
    }

    public function updateBusinessInfo(Request $request)
    {
        $validated = $request->validate([
            'business_name' => 'required|string|max:255',
            'persona_name' => 'required|string|max:255',
            'category_id' => 'nullable|integer',
            'sub_category' => 'nullable|string|max:255',
            'business_hours' => 'required|string|max:255',
            'off_hours_message' => 'nullable|string|max:500',
            'business_description' => 'required|string|max:1000',
        ]);

        if (!empty($validated['category_id'])) {
            $exists = DB::connection('mysql')->table('business_categories')
                ->where('id', $validated['category_id'])
                ->exists();
            if (!$exists) {
                return back()->withErrors(['category_id' => 'ক্যাটাগরি পাওয়া যায়নি।'])->withInput();
            }
        }

        $businessSetting = BusinessSetting::where('user_id', auth()->id())->first();
        if (!$businessSetting) {
            return back()->withErrors(['error' => 'বিজনেস সেটিংস পাওয়া যায়নি।']);
        }

        $businessSetting->update($validated);

        return back()->with(['success' => 'বিজনেস তথ্য আপডেট হয়েছে!', 'active_tab' => 'business']);
    }

    public function updateTone(Request $request)
    {
        $validated = $request->validate([
            'formality_level' => 'required|in:formal,casual',
            'emoji_usage' => 'required|in:never,sometimes,often',
            'language_style' => 'required|in:shuddho_bangla,anjonio,banglish',
            'greeting_style' => 'required|string|max:255',
        ]);

        $businessSetting = BusinessSetting::where('user_id', auth()->id())->first();
        if (!$businessSetting) {
            return back()->withErrors(['error' => 'বিজনেস সেটিংস পাওয়া যায়নি।']);
        }

        $businessSetting->update($validated);

        return back()->with(['success' => 'টোন ও যোগাযোগ সেটিংস আপডেট হয়েছে!', 'active_tab' => 'tone']);
    }

    public function updatePricing(Request $request)
    {
        $validated = $request->validate([
            'price_negotiation' => 'nullable|boolean',
            'negotiation_limit' => 'nullable|integer|min:0|max:100',
            'bulk_discount_rule' => 'nullable|string|max:500',
            'current_promo' => 'nullable|string|max:500',
        ]);

        $businessSetting = BusinessSetting::where('user_id', auth()->id())->first();
        if (!$businessSetting) {
            return back()->withErrors(['error' => 'বিজনেস সেটিংস পাওয়া যায়নি।']);
        }

        $businessSetting->update([
            'price_negotiation' => $request->boolean('price_negotiation'),
            'negotiation_limit' => $validated['negotiation_limit'] ?? 0,
            'bulk_discount_rule' => $validated['bulk_discount_rule'] ?? null,
            'current_promo' => $validated['current_promo'] ?? null,
        ]);

        return back()->with(['success' => 'মূল্য নির্ধারণ সেটিংস আপডেট হয়েছে!', 'active_tab' => 'pricing']);
    }

    public function updateFaq(Request $request)
    {
        $faq = $request->input('faq', []);
        if (is_string($faq)) {
            $faq = json_decode($faq, true) ?? [];
        }

        $faq = collect($faq)
            ->filter(fn($item) => !empty($item['question']) && !empty($item['answer']))
            ->values()
            ->toArray();

        $businessSetting = BusinessSetting::where('user_id', auth()->id())->first();
        if (!$businessSetting) {
            return back()->withErrors(['error' => 'বিজনেস সেটিংস পাওয়া যায়নি।']);
        }

        $businessSetting->update([
            'faq' => !empty($faq) ? $faq : null,
        ]);

        return back()->with(['success' => 'FAQ আপডেট হয়েছে!', 'active_tab' => 'faq']);
    }

    public function updateEscalation(Request $request)
    {
        $validated = $request->validate([
            'custom_escalation_keywords' => 'nullable|string|max:500',
            'escalation_contact' => 'nullable|string|max:255',
        ]);

        $businessSetting = BusinessSetting::where('user_id', auth()->id())->first();
        if (!$businessSetting) {
            return back()->withErrors(['error' => 'বিজনেস সেটিংস পাওয়া যায়নি।']);
        }

        $businessSetting->update($validated);

        return back()->with(['success' => 'এসকালেশন রুলস আপডেট হয়েছে!', 'active_tab' => 'escalation']);
    }

    public function updateProfile(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'phone' => 'nullable|string|max:50',
            'company' => 'nullable|string|max:255',
        ]);

        auth()->user()->update($validated);

        return back()->with(['success' => 'প্রোফাইল আপডেট হয়েছে!', 'active_tab' => 'profile']);
    }

    public function updatePassword(Request $request)
    {
        $validated = $request->validate([
            'current_password' => 'required',
            'password' => 'required|min:6|confirmed',
        ]);

        if (!Hash::check($validated['current_password'], auth()->user()->password)) {
            return back()->withErrors(['current_password' => 'বর্তমান পাসওয়ার্ড সঠিক নয়।']);
        }

        auth()->user()->update(['password' => $validated['password']]);

        return back()->with(['success' => 'পাসওয়ার্ড আপডেট হয়েছে!', 'active_tab' => 'password']);
    }

    public function leads()
    {
        return view('tenant.leads');
    }

    public function reports()
    {
        return view('tenant.reports');
    }

    public function whatsapp()
    {
        return view('tenant.whatsapp');
    }

    public function inventory()
    {
        return view('tenant.inventory');
    }

    public function inventoryAdd()
    {
        return view('tenant.inventory-add');
    }
}
