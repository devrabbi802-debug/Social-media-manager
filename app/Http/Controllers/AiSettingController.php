<?php

namespace App\Http\Controllers;

use App\Models\AiSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;

class AiSettingController extends Controller
{
    public function index()
    {
        $aiSetting = AiSetting::where('user_id', Auth::id())->first();

        return view('dashboard.ai-setup', compact('aiSetting'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'api_key' => 'required|string|max:500',
        ]);

        AiSetting::updateOrCreate(
            ['user_id' => Auth::id()],
            ['api_key' => $validated['api_key']]
        );

        return redirect()->route('ai.setup')
            ->with('success', 'AI সেক্রেট কী সফলভাবে সংরক্ষিত হয়েছে!');
    }

    public function test()
    {
        $aiSetting = AiSetting::where('user_id', Auth::id())->first();

        if (!$aiSetting) {
            return back()->with('error', 'আগে API Key সেট করুন।');
        }

        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $aiSetting->api_key,
                'Content-Type' => 'application/json',
            ])->timeout(30)->post('https://api.kilo.ai/api/gateway/chat/completions', [
                'model' => config('services.kilo.model', 'kilo-auto/free'),
                'messages' => [
                    ['role' => 'user', 'content' => 'Hello, just testing connection. Reply with one word.'],
                ],
                'stream' => false,
            ]);

            if ($response->successful()) {
                return back()->with('success', 'AI সংযোগ সফল! API Key কাজ করছে।');
            }

            return back()->with('error', 'AI API ত্রুটি: ' . $response->body());
        } catch (\Exception $e) {
            return back()->with('error', 'সংযোগ ব্যর্থ: ' . $e->getMessage());
        }
    }
}
