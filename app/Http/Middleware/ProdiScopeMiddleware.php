<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ProdiScopeMiddleware
{
    /**
     * Handle an incoming request.
     * 
     * This middleware ensures that prodi-level admins can only access
     * data within their assigned prodi.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (!$user) {
            return $next($request);
        }

        // Superadmin bypasses all restrictions
        if ($user->isSuperAdmin()) {
            return $next($request);
        }

        // For admin_prodi, check if they're trying to access resources
        // outside their prodi
        if ($user->role === 'admin_prodi' && $user->prodi_id) {
            // Store the prodi constraint in the request for controllers to use
            $request->merge([
                'prodi_scope' => $user->prodi_id,
                'prodi_scoped' => true,
            ]);
        }

        return $next($request);
    }
}
