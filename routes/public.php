<?php

use App\Http\Controllers\HomeController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Public routes — locale-prefixed
|--------------------------------------------------------------------------
| All public-facing pages live under /{locale}/...
|
| Middleware stack (applied to the group):
|   - setlocale  → reads {locale} segment, calls App::setLocale()
|   - ApplyTheme → reads govportal_theme cookie, shares $currentTheme (via web group)
|
| Pages to add (Phase 3, Weeks 6–9):
|   /siaran            BroadcastController@index   SiaranList (Livewire)
|   /siaran/{slug}     BroadcastController@show    (pure Blade)
|   /pencapaian        AchievementController@index PencapaianList (Livewire)
|   /pencapaian/{slug} AchievementController@show  (pure Blade)
|   /statistik         StatistikController@index   (pure Blade + Chart.js)
|   /direktori         DirectoriController@index   DirectoriSearch (Livewire)
|   /dasar             DasarController@index       (pure Blade)
|   /dasar/{id}/muat-turun DasarController@download
|   /profil-kementerian    ProfilKementerianController@index
|   /hubungi-kami      HubungiKamiController@index ContactForm (Livewire)
|   /penafian          StaticPageController@penafian
|   /dasar-privasi     StaticPageController@dasarPrivasi
|   /carian            SearchController@index      SearchResults (Livewire)
*/
Route::prefix('{locale}')
    ->where(['locale' => 'ms|en'])
    ->middleware('setlocale')
    ->group(function () {

        // Homepage
        Route::get('/', [HomeController::class, 'index'])->name('home');

        // --- Phase 3 routes (uncomment as each is implemented) ---
        //
        // Route::get('/siaran',             [BroadcastController::class, 'index'])->name('siaran.index');
        // Route::get('/siaran/{slug}',       [BroadcastController::class, 'show'])->name('siaran.show');
        //
        // Route::get('/pencapaian',          [AchievementController::class, 'index'])->name('pencapaian.index');
        // Route::get('/pencapaian/{slug}',   [AchievementController::class, 'show'])->name('pencapaian.show');
        //
        // Route::get('/statistik',           [StatistikController::class, 'index'])->name('statistik.index');
        //
        // Route::get('/direktori',           [DirectoriController::class, 'index'])->name('direktori.index');
        //
        // Route::get('/dasar',               [DasarController::class, 'index'])->name('dasar.index');
        // Route::get('/dasar/{id}/muat-turun', [DasarController::class, 'download'])->name('dasar.download');
        //
        // Route::get('/profil-kementerian',  [ProfilKementerianController::class, 'index'])->name('profil-kementerian.index');
        //
        // Route::get('/hubungi-kami',        [HubungiKamiController::class, 'index'])->name('hubungi-kami.index');
        //
        // Route::get('/penafian',            [StaticPageController::class, 'penafian'])->name('penafian.index');
        // Route::get('/dasar-privasi',       [StaticPageController::class, 'dasarPrivasi'])->name('dasar-privasi.index');
        //
        // Route::get('/carian',              [SearchController::class, 'index'])->name('carian.index');
    });
