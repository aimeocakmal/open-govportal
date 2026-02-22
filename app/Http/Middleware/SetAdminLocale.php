<?php

namespace App\Http\Middleware;

use Closure;
use Filament\Support\Colors\Color;
use Filament\Support\Facades\FilamentColor;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Symfony\Component\HttpFoundation\Response;

class SetAdminLocale
{
    private const SUPPORTED = ['ms', 'en'];

    private const THEME_COLORS = [
        'orange' => Color::Orange,
        'yellow' => Color::Yellow,
        'lime' => Color::Lime,
        'green' => Color::Green,
        'sky' => Color::Sky,
        'blue' => Color::Blue,
        'indigo' => Color::Indigo,
        'purple' => Color::Purple,
        'pink' => Color::Pink,
        'slate' => Color::Slate,
    ];

    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (! $user) {
            return $next($request);
        }

        if (in_array($user->preferred_locale, self::SUPPORTED, true)) {
            App::setLocale($user->preferred_locale);
        }

        $themeColor = $user->theme_color ?? 'blue';

        if (isset(self::THEME_COLORS[$themeColor])) {
            FilamentColor::register([
                'primary' => self::THEME_COLORS[$themeColor],
            ]);
        }

        return $next($request);
    }
}
