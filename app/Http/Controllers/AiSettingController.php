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
        $aiSettings = AiSetting::where('user_id', Auth::id())
            ->orderBy('priority', 'desc')
            ->get();

        return view('dashboard.ai-setup', compact('aiSettings'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'api_key' => 'required|string|max:500',
        ]);

        $maxPriority = AiSetting::where('user_id', Auth::id())->max('priority') ?? 0;

        AiSetting::create([
            'user_id' => Auth::id(),
            'api_key' => $validated['api_key'],
            'is_active' => true,
            'priority' => $maxPriority + 1,
        ]);

        return redirect()->route('ai.setup')
            ->with('success', 'AI API Key সফলভাবে যোগ করা হয়েছে!');
    }

    public function destroy(AiSetting $aiSetting)
    {
        if ($aiSetting->user_id !== Auth::id()) {
            abort(403);
        }

        $aiSetting->delete();

        return redirect()->route('ai.setup')
            ->with('success', 'API Key মুছে ফেলা হয়েছে।');
    }

    public function toggle(AiSetting $aiSetting)
    {
        if ($aiSetting->user_id !== Auth::id()) {
            abort(403);
        }

        $aiSetting->update(['is_active' => ! $aiSetting->is_active]);

        $status = $aiSetting->is_active ? 'সক্রিয়' : 'নিষ্ক্রিয়';

        return redirect()->route('ai.setup')
            ->with('success', "API Key {$status} করা হয়েছে।");
    }

    public function updatePriority(Request $request, AiSetting $aiSetting)
    {
        if ($aiSetting->user_id !== Auth::id()) {
            abort(403);
        }

        $validated = $request->validate([
            'priority' => 'required|integer|min:0',
        ]);

        $aiSetting->update(['priority' => $validated['priority']]);

        return redirect()->route('ai.setup')
            ->with('success', 'Key এর অগ্রাধিকার আপডেট করা হয়েছে।');
    }

    public function test(AiSetting $aiSetting)
    {
        if ($aiSetting->user_id !== Auth::id()) {
            abort(403);
        }

        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer '.$aiSetting->api_key,
                'Content-Type' => 'application/json',
            ])->timeout(15)->post('https://api.groq.com/openai/v1/chat/completions', [
                'model' => config('services.groq.model', 'llama-3.3-70b-versatile'),
                'messages' => [
                    ['role' => 'user', 'content' => 'Hello, just testing connection. Reply with one word.'],
                ],
            ]);

            if ($response->successful()) {
                return back()->with('success', 'API Key সফলভাবে কাজ করছে!');
            }

            return back()->with('error', 'AI API ত্রুটি: '.$response->body());
        } catch (\Exception $e) {
            return back()->with('error', 'সংযোগ ব্যর্থ: '.$e->getMessage());
        }
    }
}
