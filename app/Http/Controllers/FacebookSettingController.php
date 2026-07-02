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

        return view('dashboard.facebook-settings', compact('facebookSetting'));
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
}
