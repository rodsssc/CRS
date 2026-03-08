<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureClientIsVerified
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
        public function handle(Request $request, Closure $next): Response
        {
            $user = $request->user();
            
            // Check if user is a client first
            if (!$user->isClient()) {
                return redirect()->route('client.verification.verification')
                    ->with('error', 'Access denied.');
            }

           
            
            // Check if user has verified identity using the User model method
            if (!$user->hasVerifiedIdentity()) {
                return redirect()->route('client.home')
                    ->with('warning', 'Please complete your identity verification to access this feature.');

            }

            if($user-> hasPendingVerification()){
                return redirect()->route('client.home')
                    ->with('secondary', 'Waiting for.');
            }

         return $next($request);
     }
}
