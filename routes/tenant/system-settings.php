<?php

/*
Project         :   WhatsApp Marketing & Automation Platform with Bots, Chats, Bulk Sender & AI
@package        :   Laravel
Laravel Version :   11.41.3
PHP Version     :   8.2.18
Created Date    :   14-01-2025
Copyright       :   Corbital Technologies LLP
Author          :   CORBITALTECH™
Author URL      :   https://codecanyon.net/user/corbitaltech
Support         :   contact@corbitaltech.dev
License         :   Licensed under Codecanyon Licence
*/

use App\Http\Middleware\SanitizeInputs;
use App\Http\Middleware\TenantMiddleware;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', SanitizeInputs::class, TenantMiddleware::class])->group(function () {
    Route::prefix('/{subdomain}')->as('tenant.')->group(function () {
        $settings = [
            'general' => App\Livewire\Tenant\Settings\System\GeneralSettings::class,
            'pusher' => App\Livewire\Tenant\Settings\System\PusherSettings::class,
            'miscellaneous' => App\Livewire\Tenant\Settings\System\MiscellaneousSettings::class,
        ];

        $settings = apply_filters('tenant_settings_navigation_route', $settings);

        foreach ($settings as $prefix => $component) {
            Route::get("/{$prefix}", $component)->name("settings.{$prefix}");
        }
    });
});
