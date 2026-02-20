<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Admin routes (custom — beyond Filament)
|--------------------------------------------------------------------------
| Filament registers its own routes under /admin via AdminPanelProvider.
| This file is for any custom admin-side actions that live outside Filament:
|   - Custom download/export endpoints
|   - Bulk-action handlers called by Filament table actions
|   - Webhook receivers that need admin auth context
|
| Middleware:
|   auth          → must be logged in
|   verified      → email must be verified (remove if not using verification)
|   role:super_admin|publisher|content_editor → Spatie RBAC guard
|
| Example:
|   Route::middleware(['auth', 'role:super_admin|publisher'])
|       ->prefix('admin')
|       ->name('admin.')
|       ->group(function () {
|           Route::get('/exports/broadcasts', [ExportController::class, 'broadcasts'])->name('exports.broadcasts');
|       });
*/

// No custom admin routes yet — added here as needed in Phase 2+.
