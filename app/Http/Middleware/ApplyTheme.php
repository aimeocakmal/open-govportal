<?php

namespace App\Http\Middleware;

use App\Models\Setting;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ApplyTheme
{
    public function handle(Request $request, Closure $next): Response
    {
        $valid    = array_keys(config('themes.valid_themes', ['default' => 'Default']));
        $fallback = config('themes.fallback', 'default');

        $cookie = $request->cookie('govportal_theme');

        if ($cookie && in_array($cookie, $valid)) {
            $theme = $cookie;
        } else {
            // Read site default from settings table; falls back to config if table not ready
            try {
                $theme = Setting::get('site_default_theme', $fallback);
                $theme = in_array($theme, $valid) ? $theme : $fallback;
            } catch (\Throwable) {
                $theme = $fallback;
            }
        }

        view()->share('currentTheme', $theme);

        return $next($request);
    }
}
