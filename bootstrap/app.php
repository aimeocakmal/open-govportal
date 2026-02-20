<?php

use App\Http\Middleware\ApplyTheme;
use App\Http\Middleware\SetLocale;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        // Web routes: root redirect + public + admin (loaded via require inside web.php)
        web: __DIR__.'/../routes/web.php',

        // API routes: served under /api/v1/ with the `api` middleware group
        // (stateless, no session/CSRF, throttle:api applied automatically)
        api: __DIR__.'/../routes/api.php',
        apiPrefix: 'api/v1',

        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        // Appended to the `web` middleware group (runs on all web + admin routes)
        $middleware->web(append: [
            ApplyTheme::class,
        ]);

        // Route-level middleware aliases
        $middleware->alias([
            'setlocale' => SetLocale::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
