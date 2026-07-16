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

        // Central domains — always allow
        if (in_array($host, config('tenancy.central_domains'))) {
            return $next($request);
        }

        // Check if domain exists in tenant domains table (landlord DB)
        $domainExists = DB::table('domains')->where('domain', $host)->exists();

        if (!$domainExists) {
            abort(404);
        }

        return $next($request);
    }
}
