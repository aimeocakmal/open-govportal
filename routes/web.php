<?php

use App\Http\Controllers\HomeController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Root redirect
|--------------------------------------------------------------------------
| Detect browser locale and redirect to the appropriate locale prefix.
| Falls back to the app default locale (ms).
*/
Route::get('/', function () {
    $browser = request()->getPreferredLanguage(['ms', 'en']);
    $locale  = $browser === 'en' ? 'en' : 'ms';
    return redirect("/{$locale}");
});

/*
|--------------------------------------------------------------------------
| Locale-prefixed public routes
|--------------------------------------------------------------------------
| All public-facing pages live under /{locale}/...
| The SetLocale middleware reads the {locale} segment and calls App::setLocale().
*/
Route::prefix('{locale}')
    ->where(['locale' => 'ms|en'])
    ->middleware('setlocale')
    ->group(function () {
        Route::get('/', [HomeController::class, 'index'])->name('home');

        // Phase 3 routes will be added here:
        // Route::get('/siaran', ...)
        // Route::get('/siaran/{slug}', ...)
        // etc.
    });
