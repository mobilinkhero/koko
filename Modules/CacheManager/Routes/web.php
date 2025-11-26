<?php

use App\Http\Middleware\AdminMiddleware;
use App\Http\Middleware\EnsureTenantSecurity;
use App\Http\Middleware\SanitizeInputs;
use App\Http\Middleware\TenantMiddleware;
use Illuminate\Support\Facades\Route;
use Modules\CacheManager\Http\Controllers\Admin\AdminCacheController;
use Modules\CacheManager\Http\Controllers\Tenant\TenantCacheController;

Route::middleware(['auth', AdminMiddleware::class, SanitizeInputs::class])->prefix('admin')->name('admin.')->group(function () {
    Route::get('cache-management', '\\Modules\\CacheManager\\Livewire\\Admin\\Settings\\System\\CacheManagementSettings')->name('cache-management.settings.view');

    // Admin Cache Management API Routes
    Route::prefix('cache')->name('cache.')->controller(AdminCacheController::class)->group(function () {
        Route::post('clear', 'clearCache')->name('clear');
        Route::post('clear-config', 'clearConfig')->name('clear-config');
        Route::post('clear-routes', 'clearRoutes')->name('clear-routes');
        Route::post('clear-views', 'clearViews')->name('clear-views');
        Route::post('clear-logs', 'clearLogs')->name('clear-logs');
        Route::post('optimize', 'optimizeApplication')->name('optimize');
        Route::get('status', 'getCacheStatus')->name('status');
    });
});

Route::middleware(['auth', 'web', SanitizeInputs::class, TenantMiddleware::class, EnsureTenantSecurity::class])->group(function () {
    Route::prefix('/{subdomain}')->as('tenant.')->group(function () {
        Route::get('/cache-management', '\\Modules\\CacheManager\\Livewire\\Tenant\\Settings\\System\\CacheManagementSettings')->name('settings.cache-management');

        // Tenant Cache Management API Routes
        Route::prefix('cache')->name('cache.')->controller(TenantCacheController::class)->group(function () {
            Route::post('clear', 'clearCache')->name('clear');
            Route::post('clear-views', 'clearViews')->name('clear-views');
            Route::post('clear-logs', 'clearLogs')->name('clear-logs');
            Route::post('clear-sessions', 'clearSessions')->name('clear-sessions');
            Route::post('optimize', 'optimizeApplication')->name('optimize');
            Route::get('status', 'getCacheStatus')->name('status');
        });
    });
});
