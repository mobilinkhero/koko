<?php

use App\Http\Middleware\AdminMiddleware;
use App\Http\Middleware\EnsureTenantSecurity;
use App\Http\Middleware\SanitizeInputs;
use App\Http\Middleware\TenantMiddleware;
use Illuminate\Support\Facades\Route;
use Modules\LogViewer\Livewire\LogViewer;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your module. These
| routes are loaded by the RouteServiceProvider.
|
| Note: Controller routes should use fully qualified class names:
| [Controller::class, 'method'] or 'Modules\LogViewer\Http\Controllers\ControllerName@method'
|
*/

Route::middleware(['auth', AdminMiddleware::class, SanitizeInputs::class])
    ->prefix('admin') // Prefix the route with 'admin'
    ->name('admin.') // Name the route with 'admin.' prefix
    ->group(function () {
        // With the updated RouteServiceProvider, we can use Livewire components directly with ::class
        Route::get('/logs', LogViewer::class)->name('logs.index');

        // For controller routes, use the full namespace:
        // Route::get('/example', \Modules\LogViewer\Http\Controllers\ExampleController::class . '@index')->name('example.index');
    });

Route::middleware(['auth', 'web',  SanitizeInputs::class, TenantMiddleware::class, EnsureTenantSecurity::class])->group(function () {
    Route::prefix('/{subdomain}')->as('tenant.')->group(function () {
        Route::get('/logs', LogViewer::class)->name('logs.index');
    });
});
