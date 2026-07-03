<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AiSystemPrompt;
use Illuminate\Http\Request;

class AiSystemPromptController extends Controller
{
    public function index()
    {
        $prompt = AiSystemPrompt::getActive();

        return view('admin.ai-system-prompt.index', compact('prompt'));
    }

    public function update(Request $request)
    {
        $validated = $request->validate([
            'prompt_text' => 'required|string|max:5000',
        ]);

        $prompt = AiSystemPrompt::firstOrCreate([]);
        $prompt->update(['prompt_text' => $validated['prompt_text']]);

        return back()->with('success', 'AI সিস্টেম প্রম্পট আপডেট হয়েছে!');
    }
}
