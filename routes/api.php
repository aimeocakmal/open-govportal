<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API routes — /api/v1/...
|--------------------------------------------------------------------------
| All routes here are under the `api` middleware group, which provides:
|   - throttle:api  (60 req/min default, configurable in RouteServiceProvider)
|   - SubstituteBindings
|   - No session / no CSRF (stateless)
|
| Authentication: Laravel Sanctum token (added in Phase 4).
| All responses: JSON.
|
| Versioning: routes are served from /api/v1/ (configured in bootstrap/app.php).
|
| Planned endpoints (Phase 4+):
|
|   GET  /broadcasts           BroadcastApiController@index  paginated, filterable by type/locale
|   GET  /broadcasts/{slug}    BroadcastApiController@show
|   GET  /achievements         AchievementApiController@index
|   GET  /achievements/{slug}  AchievementApiController@show
|   GET  /quick-links          QuickLinkApiController@index
|   GET  /hero-banners         HeroBannerApiController@index (active only)
|   GET  /policies             PolicyApiController@index
|   GET  /policies/{id}        PolicyApiController@show
|   GET  /staff-directory      StaffDirectoryApiController@index (filterable by department)
|   GET  /search               SearchApiController@index  ?q=&locale=ms
|   POST /feedback             FeedbackApiController@store (rate-limited: 5/hr)
|
| Example:
|   Route::middleware('auth:sanctum')->group(function () {
|       Route::apiResource('broadcasts', BroadcastApiController::class)->only(['index', 'show']);
|   });
*/

// Health check — no auth required
Route::get('/health', fn () => response()->json(['status' => 'ok']))->name('api.health');

// No data endpoints yet — added here in Phase 4.
