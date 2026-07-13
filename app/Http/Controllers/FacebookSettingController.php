<?php

namespace App\Http\Controllers;

use App\Models\FacebookSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class FacebookSettingController extends Controller
{
    public function index()
    {
        $facebookSetting = FacebookSetting::where('user_id', Auth::id())->first();

        return view('tenant.facebook-settings', compact('facebookSetting'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'app_id'            => 'required|string|max:255',
            'app_secret'        => 'required|string|max:255',
            'verify_token'      => 'nullable|string|max:255',
            'page_id'           => 'required|string|max:255',
            'page_access_token' => 'required|string',
        ]);

        $validated['user_id'] = Auth::id();
        $validated['connection_type'] = 'facebook_app';

        FacebookSetting::updateOrCreate(
            ['user_id' => Auth::id()],
            $validated
        );

        return redirect()->route('facebook.settings')
            ->with('success', 'ফেসবুক সেটিংস সফলভাবে সংরক্ষিত হয়েছে!');
    }

    public function destroy()
    {
        FacebookSetting::where('user_id', Auth::id())->delete();

        return redirect()->route('facebook.settings')
            ->with('success', 'ফেসবুক সেটিংস মুছে ফেলা হয়েছে।');
    }

    public function toggleAiReply()
    {
        $facebookSetting = FacebookSetting::where('user_id', Auth::id())->first();

        if (! $facebookSetting) {
            return redirect()->route('facebook.settings')
                ->with('error', 'ফেসবুক সেটিংস পাওয়া যায়নি।');
        }

        $facebookSetting->update([
            'ai_auto_reply_enabled' => ! $facebookSetting->ai_auto_reply_enabled,
        ]);

        $status = $facebookSetting->ai_auto_reply_enabled ? 'চালু' : 'বন্ধ';

        return redirect()->route('facebook.settings')
            ->with('success', "AI অটো রিপ্লাই {$status} করা হয়েছে।");
    }
}
