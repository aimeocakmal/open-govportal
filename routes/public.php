<?php

use App\Http\Controllers\AchievementController;
use App\Http\Controllers\BroadcastController;
use App\Http\Controllers\DasarController;
use App\Http\Controllers\DirectoriController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\HubungiKamiController;
use App\Http\Controllers\PreviewController;
use App\Http\Controllers\ProfilKementerianController;
use App\Http\Controllers\SearchController;
use App\Http\Controllers\SitemapController;
use App\Http\Controllers\StaticPageController;
use App\Http\Controllers\StatistikController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Content preview with signed URL (not locale-prefixed)
|--------------------------------------------------------------------------
*/
Route::get('/preview/{model}/{id}', [PreviewController::class, 'show'])
    ->name('preview.show')
    ->middleware('signed');

/*
|--------------------------------------------------------------------------
| XML Sitemap (not locale-prefixed)
|--------------------------------------------------------------------------
*/
Route::get('/sitemap.xml', [SitemapController::class, 'index'])->name('sitemap');

/*
|--------------------------------------------------------------------------
| Public routes — locale-prefixed
|--------------------------------------------------------------------------
| All public-facing pages live under /{locale}/...
|
| Middleware stack (applied to the group):
|   - setlocale  → reads {locale} segment, calls App::setLocale()
|   - ApplyTheme → reads govportal_theme cookie, shares $currentTheme (via web group)
*/
Route::prefix('{locale}')
    ->where(['locale' => 'ms|en'])
    ->middleware('setlocale')
    ->group(function () {

        // Homepage
        Route::get('/', [HomeController::class, 'index'])->name('home');

        // Siaran (Broadcasts)
        Route::get('/siaran', [BroadcastController::class, 'index'])->name('siaran.index');
        Route::get('/siaran/{slug}', [BroadcastController::class, 'show'])->name('siaran.show');

        // Pencapaian (Achievements)
        Route::get('/pencapaian', [AchievementController::class, 'index'])->name('pencapaian.index');
        Route::get('/pencapaian/{slug}', [AchievementController::class, 'show'])->name('pencapaian.show');

        // Statistik (Statistics)
        Route::get('/statistik', [StatistikController::class, 'index'])->name('statistik.index');

        // Direktori (Staff Directory)
        Route::get('/direktori', [DirectoriController::class, 'index'])->name('direktori.index');

        // Dasar (Policies)
        Route::get('/dasar', [DasarController::class, 'index'])->name('dasar.index');
        Route::get('/dasar/{id}/muat-turun', [DasarController::class, 'download'])->name('dasar.download');

        // Profil Kementerian (Ministry Profile)
        Route::get('/profil-kementerian', [ProfilKementerianController::class, 'index'])->name('profil-kementerian.index');

        // Hubungi Kami (Contact Us)
        Route::get('/hubungi-kami', [HubungiKamiController::class, 'index'])->name('hubungi-kami.index');

        // Carian (Search)
        Route::get('/carian', [SearchController::class, 'index'])->name('carian.index');

        // Static pages — named routes for known pages
        Route::get('/penafian', [StaticPageController::class, 'show'])->name('penafian.index')->defaults('slug', 'penafian');
        Route::get('/dasar-privasi', [StaticPageController::class, 'show'])->name('dasar-privasi.index')->defaults('slug', 'dasar-privasi');

        // Static pages — catch-all for any CMS-managed page (must be LAST in the group)
        Route::get('/{slug}', [StaticPageController::class, 'show'])->name('static-page.show');
    });
