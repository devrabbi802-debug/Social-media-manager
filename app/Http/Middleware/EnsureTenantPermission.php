<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureTenantPermission
{
    /**
     * Route middleware `permission:user_management,list`.
     */
    public function handle(Request $request, Closure $next, string $module, string $action = 'list'): Response
    {
        $user = $request->user();

        if ($user && $user->hasPermission($module, $action)) {
            return $next($request);
        }

        abort(403, 'You do not have permission to access this area.');
    }
}
