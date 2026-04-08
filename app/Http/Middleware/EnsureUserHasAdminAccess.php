<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class EnsureUserHasAdminAccess
{
    /*
    |--------------------------------------------------------------------------
    | handle() — Block users who don't have admin panel access
    |--------------------------------------------------------------------------
    | We check the 'access admin panel' permission which is assigned to
    | admin, editor, and author in your seeder — but NOT to reader.
    |
    | If the user does not have this permission, we redirect them to the
    | public frontend home page with an error message.
    |
    | This middleware sits on top of the entire admin route group so it
    | runs before any specific permission checks deeper in the routes.
    */
    public function handle(Request $request, Closure $next)
    {
        if (!auth()->user()->can('access admin panel')) {
            return redirect()
                ->route('home')
                ->with('error', 'You do not have access to the admin panel.');
        }

        return $next($request);
    }
}
