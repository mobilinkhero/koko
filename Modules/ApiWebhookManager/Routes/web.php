<?php

use App\Http\Middleware\EnsureTenantSecurity;
use App\Http\Middleware\SanitizeInputs;
use App\Http\Middleware\TenantMiddleware;
use Illuminate\Auth\Middleware\EnsureEmailIsVerified;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your module. These
| routes are loaded by the ServiceProvider.
|
*/

Route::middleware(['web', 'auth', SanitizeInputs::class, TenantMiddleware::class, EnsureEmailIsVerified::class, EnsureTenantSecurity::class])->group(function () {
    Route::prefix('/{subdomain}')->as('tenant.')->group(function () {
        Route::get('/webhook', '\\Modules\\ApiWebhookManager\\Livewire\\Tenant\\Settings\\System\\WebhookSettingsManager')->name('webhook.settings.view');
        Route::get('/api-management', '\\Modules\\ApiWebhookManager\\Livewire\\Tenant\\Settings\\System\\ManageApiTokens')->name('api-management.settings.view');
    });

});
