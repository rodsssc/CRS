<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RedirectUnverifiedUsers
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (!$user) {
            return $next($request);
        }

        // Only check for client users
        if ($user->role !== 'client') {
            return $next($request);
        }

        // Skip check for specific routes
        $skipRoutes = [
            'client.verification.index',
            'client.verification.verification.store',
            // profiling endpoints live under the client.verification.* group
            'client.verification.profile.show',
            'client.verification.profile.store',
            'client.verification.profile.update',
            // (legacy names kept to avoid accidental lockouts if routes change)
            'client.profile.show',
            'client.profile.store',
            'client.profile.update',
            'client.home',
            'home',
            'logout',
        ];

        if (in_array($request->route()?->getName(), $skipRoutes)) {
            return $next($request);
        }

        // Check verification status
        if (!$user->hasVerifiedIdentity()) {
            // If not verified and trying to access protected routes
            if ($request->routeIs('client.*') && !in_array($request->route()?->getName(), $skipRoutes)) {
                return redirect()->route('client.profile.index')
                    ->with('warning', 'Please complete your identity verification to proceed.');
            }
        }

        return $next($request);
    }
}
