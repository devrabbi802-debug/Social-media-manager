<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AiImagePrompt;
use Illuminate\Http\Request;

class AiImagePromptController extends Controller
{
    public function index()
    {
        $prompt = AiImagePrompt::getActive();

        return view('admin.ai-image-prompt.index', compact('prompt'));
    }

    public function update(Request $request)
    {
        $validated = $request->validate([
            'prompt_text' => 'required|string|max:5000',
        ]);

        $prompt = AiImagePrompt::firstOrCreate([]);
        $prompt->update(['prompt_text' => $validated['prompt_text']]);

        return back()->with('success', 'AI Image Prompt আপডেট হয়েছে!');
    }
}
