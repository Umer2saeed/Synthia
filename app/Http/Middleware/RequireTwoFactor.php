<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RequireTwoFactor
{
    /*
    | Forces admins to set up 2FA, but ONLY when TWO_FACTOR_ENFORCE_ADMIN=true in .env.
    | With the flag off (the default) this does nothing, so a 2FA bug can never
    | lock you out of your own admin panel.
    */
    public function handle(Request $request, Closure $next): Response
    {
        if (! config('twofactor.enforce_admin')) {
            return $next($request);
        }

        $user = $request->user();

        if (! $user || ! $user->hasRole('admin')) {
            return $next($request);
        }

        if ($user->hasTwoFactorEnabled() || $request->routeIs('two-factor.*')) {
            return $next($request);
        }

        return redirect()->route('two-factor.setup')
            ->with('warning', 'Admin accounts require two-factor authentication. Please set it up to continue.');
    }
}
