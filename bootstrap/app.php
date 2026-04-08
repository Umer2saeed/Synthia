<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {

        /*
       |----------------------------------------------------------------------
       | Register Spatie Permission Middleware Aliases
       |----------------------------------------------------------------------
       | 'role'       → checks if user has a given role
       | 'permission' → checks if user has a given permission
       | 'role_or_permission' → checks either
       |
       | Usage in routes:
       |   Route::middleware('role:admin')->group(...)
       |   Route::middleware('permission:manage users')->group(...)
       */
        $middleware->alias([
            'role'               => \Spatie\Permission\Middleware\RoleMiddleware::class,
            'permission'         => \Spatie\Permission\Middleware\PermissionMiddleware::class,
            'role_or_permission' => \Spatie\Permission\Middleware\RoleOrPermissionMiddleware::class,

            // Our custom middleware
            'admin.access' => \App\Http\Middleware\EnsureUserHasAdminAccess::class,
        ]);

    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
