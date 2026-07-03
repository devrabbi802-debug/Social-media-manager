<?php

namespace App\Http\Controllers;

use App\Models\AiSetting;
use App\Services\GeminiApiService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;

class AiSettingController extends Controller
{
    public function index()
    {
        $messageKeys = AiSetting::where('user_id', Auth::id())
            ->byType('message')
            ->byPriority()
            ->get();

        $imageKeys = AiSetting::where('user_id', Auth::id())
            ->byType('image')
            ->byPriority()
            ->get();

        $activeTab = request('tab', 'message');

        return view('dashboard.ai-setup', compact('messageKeys', 'imageKeys', 'activeTab'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'api_key' => 'required|string|max:500',
            'type' => 'required|in:message,image',
        ]);

        $maxPriority = AiSetting::where('user_id', Auth::id())
            ->where('type', $validated['type'])
            ->max('priority') ?? 0;

        AiSetting::create([
            'user_id' => Auth::id(),
            'api_key' => $validated['api_key'],
            'type' => $validated['type'],
            'is_active' => true,
            'priority' => $maxPriority + 1,
        ]);

        $tab = $validated['type'];
        $typeName = $tab === 'message' ? 'Message AI' : 'Image AI';

        return redirect()->route('ai.setup', ['tab' => $tab])
            ->with('success', "{$typeName} Key সফলভাবে যোগ করা হয়েছে!");
    }

    public function destroy(AiSetting $aiSetting)
    {
        if ($aiSetting->user_id !== Auth::id()) {
            abort(403);
        }

        $tab = $aiSetting->type;
        $aiSetting->delete();

        return redirect()->route('ai.setup', ['tab' => $tab])
            ->with('success', 'API Key মুছে ফেলা হয়েছে।');
    }

    public function toggle(AiSetting $aiSetting)
    {
        if ($aiSetting->user_id !== Auth::id()) {
            abort(403);
        }

        $aiSetting->update(['is_active' => ! $aiSetting->is_active]);

        $tab = $aiSetting->type;
        $status = $aiSetting->is_active ? 'সক্রিয়' : 'নিষ্ক্রিয়';

        return redirect()->route('ai.setup', ['tab' => $tab])
            ->with('success', "API Key {$status} করা হয়েছে।");
    }

    public function test(AiSetting $aiSetting)
    {
        if ($aiSetting->user_id !== Auth::id()) {
            abort(403);
        }

        $tab = $aiSetting->type;

        if ($tab === 'image') {
            $result = GeminiApiService::testConnection($aiSetting->api_key);
        } else {
            $result = $this->testGroqKey($aiSetting->api_key);
        }

        $status = $result['success'] ? 'success' : 'error';

        return redirect()->route('ai.setup', ['tab' => $tab])
            ->with($status, $result['message']);
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
