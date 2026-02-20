<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Root redirect
|--------------------------------------------------------------------------
| Detect browser locale and redirect to the locale-prefixed homepage.
| Falls back to the app default (ms) if the browser preference is unrecognised.
*/
Route::get('/', function () {
    $browser = request()->getPreferredLanguage(['ms', 'en']);
    $locale  = $browser === 'en' ? 'en' : 'ms';
    return redirect("/{$locale}");
});

/*
|--------------------------------------------------------------------------
| Sub-route files
|--------------------------------------------------------------------------
| Split by concern so each file stays focused and easy to navigate.
| All files below run inside the `web` middleware group (session, CSRF, etc.).
*/
require __DIR__.'/public.php';
require __DIR__.'/admin.php';
