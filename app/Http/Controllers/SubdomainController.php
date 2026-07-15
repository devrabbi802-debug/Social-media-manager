<?php

namespace App\Http\Controllers;

use App\Models\Tenant;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class SubdomainController extends Controller
{
    public function check(Request $request): JsonResponse
    {
        $subdomain = strtolower(trim($request->input('subdomain', '')));
        $subdomain = preg_replace('/[^a-z0-9-]/', '', $subdomain);

        $exists = Tenant::where('id', $subdomain)->exists();

        return response()->json([
            'available' => !$exists,
            'subdomain' => $subdomain,
        ]);
    }
}
