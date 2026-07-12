<?php

namespace App\Http\Controllers;

use App\Models\AiSetting;
use App\Services\ClipService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;

class AiSettingController extends Controller
{
    public function index()
    {
        $groqKey = AiSetting::where('user_id', Auth::id())
            ->byType('message')
            ->first();

        $geminiKey = AiSetting::where('user_id', Auth::id())
            ->byType('image')
            ->first();

        $clipService = new ClipService();
        $clipStatus = $clipService->healthCheck();

        return view('dashboard.ai-setup', compact('groqKey', 'geminiKey', 'clipStatus'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'api_key' => 'required|string|max:500',
            'type' => 'required|in:message,image',
        ]);

        $existing = AiSetting::where('user_id', Auth::id())
            ->where('type', $validated['type'])
            ->first();

        if ($existing) {
            $existing->update([
                'api_key' => $validated['api_key'],
                'is_active' => true,
            ]);
            $message = $validated['type'] === 'message'
                ? 'Groq API Key আপডেট করা হয়েছে!'
                : 'Gemini API Key আপডেট করা হয়েছে!';
        } else {
            AiSetting::create([
                'user_id' => Auth::id(),
                'api_key' => $validated['api_key'],
                'type' => $validated['type'],
                'is_active' => true,
                'priority' => 1,
            ]);
            $message = $validated['type'] === 'message'
                ? 'Groq API Key সফলভাবে যোগ করা হয়েছে!'
                : 'Gemini API Key সফলভাবে যোগ করা হয়েছে!';
        }

        return redirect()->route('ai.setup')
            ->with('success', $message);
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

    public function test(AiSetting $aiSetting)
    {
        if ($aiSetting->user_id !== Auth::id()) {
            abort(403);
        }

        if ($aiSetting->type === 'image') {
            $result = \App\Services\AiChatService::testGeminiConnection($aiSetting->api_key);
        } else {
            $result = $this->testGroqKey($aiSetting->api_key);
        }

        $status = $result['success'] ? 'success' : 'error';
        $message = $result['message'];

        return redirect()->route('ai.setup')
            ->with($status, $message);
    }

    private function testGroqKey(string $apiKey): array
    {
        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer '.$apiKey,
                'Content-Type' => 'application/json',
            ])->timeout(15)->post('https://api.groq.com/openai/v1/chat/completions', [
                'model' => config('services.groq.model', 'llama-3.3-70b-versatile'),
                'messages' => [
                    ['role' => 'user', 'content' => 'Hello, just testing connection. Reply with one word.'],
                ],
            ]);

            if ($response->status() === 401) {
                return ['success' => false, 'message' => 'API key invalid'];
            }

            if ($response->status() === 429) {
                return ['success' => true, 'message' => 'Connected! (Rate limited but key is valid)'];
            }

            if ($response->failed()) {
                return ['success' => false, 'message' => 'API error: '.$response->status()];
            }

            $body = $response->json();
            $reply = $body['choices'][0]['message']['content'] ?? null;

            return ['success' => true, 'message' => 'Connected! AI replied: '.substr($reply ?? '', 0, 50)];
        } catch (\Exception $e) {
            return ['success' => false, 'message' => 'Error: '.$e->getMessage()];
        }
    }
}
