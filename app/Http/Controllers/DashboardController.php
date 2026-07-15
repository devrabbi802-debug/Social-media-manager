<?php

namespace App\Http\Controllers;

use App\Models\BusinessSetting;
use App\Models\Conversation;
use App\Models\FacebookSetting;
use App\Models\Message;
use Illuminate\Http\Request;

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
        $businessSetting = BusinessSetting::where('user_id', auth()->id())->first();
        return view('tenant.settings', compact('businessSetting'));
    }

    public function updateBusinessSettings(Request $request)
    {
        // Decode JSON string from hidden input to array before validation
        if (is_string($request->input('accepted_payment_methods'))) {
            $decoded = json_decode($request->input('accepted_payment_methods'), true);
            $request->merge(['accepted_payment_methods' => $decoded]);
        }

        $validated = $request->validate([
            'delivery_areas' => 'nullable|string|max:1000',
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

        $businessSetting->update([
            'delivery_areas' => $validated['delivery_areas'] ?? null,
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

        return back()->with('success', 'বিজনেস সেটিংস আপডেট হয়েছে!');
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
