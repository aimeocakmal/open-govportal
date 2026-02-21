<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Symfony\Component\HttpFoundation\Response;

class SetAdminLocale
{
    private const SUPPORTED = ['ms', 'en'];

    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user && in_array($user->preferred_locale, self::SUPPORTED, true)) {
            App::setLocale($user->preferred_locale);
        }

        return $next($request);
    }
}
