<?php

namespace App\Http\Middleware;

use App\Models\Setting;
use App\Services\ThemeService;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ApplyTheme
{
    public function __construct(private ThemeService $themeService) {}

    public function handle(Request $request, Closure $next): Response
    {
        // Admin routes are never themed — use default and skip view path manipulation
        if ($request->is('admin/*')) {
            view()->share('currentTheme', 'default');
            view()->share('themeViteEntries', $this->themeService->getViteEntries('default'));

            return $next($request);
        }

        $validThemes = array_keys($this->themeService->discover());
        $fallback = config('themes.fallback', 'default');

        $cookie = $request->cookie('govportal_theme');

        if ($cookie && in_array($cookie, $validThemes)) {
            $theme = $cookie;
        } else {
            try {
                $theme = Setting::get('site_default_theme', $fallback);
                $theme = in_array($theme, $validThemes) ? $theme : $fallback;
            } catch (\Throwable) {
                $theme = $fallback;
            }
        }

        $this->themeService->setActive($theme);

        // Reset and rebuild view paths (Octane-safe — prevents path accumulation)
        $finder = view()->getFinder();
        $finder->flush();
        $finder->setPaths([]);

        // Active theme views first (if not default)
        if ($theme !== 'default') {
            $finder->addLocation($this->themeService->getViewsPath($theme));
        }

        // Default theme views as fallback
        $finder->addLocation($this->themeService->getViewsPath('default'));

        // Share theme data with all views
        view()->share('currentTheme', $theme);
        view()->share('themeViteEntries', $this->themeService->getViteEntries($theme));

        return $next($request);
    }
}
