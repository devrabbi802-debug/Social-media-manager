<?php

namespace App\Jobs;

use App\Models\AiImagePrompt;
use App\Models\VariantImage;
use App\Services\GeminiApiService;
use App\Services\GeminiKeyManager;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class AnalyzeVariantImageJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;

    public int $timeout = 90;

    public int $backoff = 30;

    public function __construct(
        public VariantImage $variantImage,
        public int $userId,
    ) {
        $this->onQueue('facebook');
    }

    public function handle(): void
    {
        $image = $this->variantImage;

        if ($image->hasAnalysis()) {
            Log::info('Variant image already analyzed', ['id' => $image->id]);
            return;
        }

        $keyManager = new GeminiKeyManager((string) $this->userId);

        $apiKey = $keyManager->getAvailableKey();
        if (!$apiKey) {
            Log::error('No available Gemini API key for variant image analysis', [
                'variant_image_id' => $image->id,
            ]);
            throw new \Exception('No available Gemini API key');
        }

        $imagePath = $image->image_path;
        if (!Storage::disk('public')->exists($imagePath)) {
            Log::error('Variant image file not found', [
                'variant_image_id' => $image->id,
                'path' => $imagePath,
            ]);
            return;
        }

        $imageContents = Storage::disk('public')->get($imagePath);
        $imageBase64 = base64_encode($imageContents);

        $prompt = "এই প্রোডাক্ট ভ্যারিয়েন্ট ইমেজটি বিশ্লেষণ করো। নিচের তথ্যগুলো বাংলায় লিখো:\n";
        $prompt .= "1. প্রোডাক্টের ধরন (product type)\n";
        $prompt .= "2. রঙ (color)\n";
        $prompt .= "3. ম্যাটেরিয়াল/উপাদান (material)\n";
        $prompt .= "4. ডিজাইন/স্টাইল (design/style)\n";
        $prompt .= "5. আনুমানিক সাইজ/মাপ (approximate size)\n";
        $prompt .= "6. ব্যবহারের ক্ষেত্র (use case)\n";
        $prompt .= "7. অন্যান্য বিশেষ বৈশিষ্ট্য (other notable features)\n\n";
        $prompt .= "JSON formatে উত্তর দাও। প্রতিটি field এর key হবে: product_type, color, material, design, approximate_size, use_case, features";

        try {
            $service = new GeminiApiService($apiKey->api_key);
            $analysis = $service->analyzeImage($imageBase64, $prompt, AiImagePrompt::getActive()->prompt_text);

            if ($analysis) {
                $parsed = $this->parseAnalysis($analysis);
                $image->update(['image_analysis' => $parsed]);

                Log::info('Variant image analyzed successfully', [
                    'variant_image_id' => $image->id,
                    'variant_id' => $image->variant_id,
                ]);
            }
        } catch (\Exception $e) {
            $keyManager->markKeyRateLimited($apiKey->id);
            Log::error('Variant image analysis failed', [
                'variant_image_id' => $image->id,
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    private function parseAnalysis(string $analysis): array
    {
        $jsonMatch = preg_match('/\{[\s\S]*\}/', $analysis, $matches);

        if ($jsonMatch) {
            $decoded = json_decode($matches[0], true);
            if ($decoded && json_last_error() === JSON_ERROR_NONE) {
                return $decoded;
            }
        }

        return [
            'raw_analysis' => $analysis,
            'product_type' => null,
            'color' => null,
            'material' => null,
            'design' => null,
            'approximate_size' => null,
            'use_case' => null,
            'features' => null,
        ];
    }

    public function failed(\Throwable $exception): void
    {
        Log::error('AnalyzeVariantImageJob failed permanently', [
            'variant_image_id' => $this->variantImage->id,
            'error' => $exception->getMessage(),
        ]);
    }
}
