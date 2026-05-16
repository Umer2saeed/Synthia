<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RequireTwoFactor
{
    /*
    | Force admin users to set up 2FA if they have not done so.
    | Redirects to the 2FA setup page until they complete it.
    | Non-admin users are not affected.
    */
    public function handle(Request $request, Closure $next): Response
    {
        // DEVELOPMENT ONLY — remove before production
        if (app()->environment('local')) {
            return $next($request);
        }

        $user = auth()->user();

        if (!$user) {
            return $next($request);
        }

        // Only enforce for admin role
        if (!$user->hasRole('admin')) {
            return $next($request);
        }

        // Admin has 2FA — allow through
        if ($user->hasTwoFactorEnabled()) {
            return $next($request);
        }

        // Admin is currently setting up 2FA — allow the setup routes through
        if ($request->routeIs('two-factor.*')) {
            return $next($request);
        }

        // Admin has no 2FA — force them to set it up
        return redirect()->route('two-factor.setup')
            ->with('warning', 'Admin accounts require Two-Factor Authentication. Please set it up to continue.');
    }
}
