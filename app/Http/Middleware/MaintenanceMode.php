<?php

namespace App\Http\Middleware;

use App\Services\SettingsService;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class MaintenanceMode
{
    public function handle(Request $request, Closure $next): Response
    {
        $settings = app(SettingsService::class);

        // Only intercept if maintenance mode is on
        if (!$settings->bool('maintenance_mode', false)) {
            return $next($request);
        }

        // Admins bypass maintenance mode
        if ($request->user() && $request->user()->hasRole('admin')) {
            return $next($request);
        }

        // Admin login page must stay accessible
        if ($request->routeIs('login') || $request->routeIs('password.*')) {
            return $next($request);
        }

        $message = $settings->get(
            'maintenance_message',
            'We are performing scheduled maintenance. Back soon!'
        );

        return response()->view('errors.maintenance', compact('message'), 503);
    }
}
