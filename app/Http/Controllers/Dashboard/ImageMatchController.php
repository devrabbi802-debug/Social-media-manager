<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Services\ClipService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class ImageMatchController extends Controller
{
    /**
     * Show the image matching test page.
     */
    public function index()
    {
        $clipService = new ClipService();
        $clipStatus = $clipService->healthCheck();
        
        // Get catalog statistics
        $catalogEmbeddings = $clipService->getCatalogEmbeddings();
        $productCount = count(array_filter($catalogEmbeddings, fn($e) => $e['type'] === 'product'));
        $variantCount = count(array_filter($catalogEmbeddings, fn($e) => $e['type'] === 'variant'));

        return view('tenant.image-match', compact('clipStatus', 'productCount', 'variantCount'));
    }

    /**
     * Match an uploaded image against the catalog.
     */
    public function match(Request $request)
    {
        $request->validate([
            'image' => 'required|image|max:5120',
        ]);

        try {
            $clipService = new ClipService();
            
            // Check CLIP server health
            $health = $clipService->healthCheck();
            if ($health['status'] !== 'healthy') {
                return back()->with('error', 'CLIP Server সংযোগ করা যায়নি।');
            }

            // Get the uploaded image
            $imageFile = $request->file('image');
            $imageContents = file_get_contents($imageFile->getRealPath());
            $imageBase64 = base64_encode($imageContents);

            // Get catalog embeddings
            $catalogEmbeddings = $clipService->getCatalogEmbeddings();
            
            if (empty($catalogEmbeddings)) {
                return back()->with('error', 'কোনো প্রোডাক্ট ক্যাটালগ পাওয়া যায়নি। প্রথমে প্রোডাক্ট যোগ করুন।');
            }

            // Match image
            $matchResult = $clipService->matchImage(
                $imageBase64,
                $catalogEmbeddings,
                5,
                config('services.clip.threshold', 0.7)
            );

            if (!$matchResult) {
                return back()->with('error', 'ছবি ম্যাচ করতে সমস্যা হয়েছে।');
            }

            $uploadedPath = $imageFile->store('temp', 'public');

            return view('tenant.image-match-result', [
                'matches' => $matchResult['matches'] ?? [],
                'bestMatch' => $matchResult['best_match'] ?? null,
                'totalCatalog' => $matchResult['total_catalog_items'] ?? 0,
                'uploadedImage' => asset('storage/' . $uploadedPath),
            ]);

        } catch (\Exception $e) {
            Log::error('Image match failed', ['error' => $e->getMessage()]);
            return back()->with('error', 'ছবি ম্যাচ করতে সমস্যা হয়েছে: ' . $e->getMessage());
        }
    }

    /**
     * Match an image from URL (API endpoint for Facebook webhook).
     */
    public function matchUrl(Request $request)
    {
        $request->validate([
            'image_url' => 'required|url',
        ]);

        try {
            $clipService = new ClipService();
            
            // Check CLIP server health
            $health = $clipService->healthCheck();
            if ($health['status'] !== 'healthy') {
                return response()->json([
                    'success' => false,
                    'error' => 'CLIP Server is not healthy',
                ], 503);
            }

            // Get catalog embeddings
            $catalogEmbeddings = $clipService->getCatalogEmbeddings();
            
            if (empty($catalogEmbeddings)) {
                return response()->json([
                    'success' => false,
                    'error' => 'No catalog embeddings found',
                ], 404);
            }

            // Match image
            $matchResult = $clipService->matchImageFromUrl(
                $request->input('image_url'),
                $catalogEmbeddings,
                5,
                config('services.clip.threshold', 0.7)
            );

            if (!$matchResult) {
                return response()->json([
                    'success' => false,
                    'error' => 'Image matching failed',
                ], 500);
            }

            return response()->json([
                'success' => true,
                'matches' => $matchResult['matches'] ?? [],
                'best_match' => $matchResult['best_match'] ?? null,
                'total_catalog_items' => $matchResult['total_catalog_items'] ?? 0,
            ]);

        } catch (\Exception $e) {
            Log::error('Image match URL failed', ['error' => $e->getMessage()]);
            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
