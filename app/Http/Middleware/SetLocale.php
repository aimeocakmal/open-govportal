<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Symfony\Component\HttpFoundation\Response;

class SetLocale
{
    private const SUPPORTED = ['ms', 'en'];

    public function handle(Request $request, Closure $next): Response
    {
        $locale = $request->route('locale', config('app.locale', 'ms'));

        if (!in_array($locale, self::SUPPORTED, true)) {
            abort(404);
        }

        App::setLocale($locale);

        return $next($request);
    }
}
