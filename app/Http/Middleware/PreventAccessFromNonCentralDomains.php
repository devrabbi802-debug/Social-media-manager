<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PreventAccessFromNonCentralDomains
{
    public function handle(Request $request, Closure $next)
    {
        $host = $request->getHost();

        // Only central domains are allowed
        if (in_array($host, config('tenancy.central_domains'))) {
            return $next($request);
        }

        // Block all non-central domains (tenant domains use their own routes)
        abort(404);
    }
}
