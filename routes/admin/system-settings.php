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

use App\Http\Middleware\AdminMiddleware;
use App\Http\Middleware\SanitizeInputs;
use Illuminate\Support\Facades\Route;

Route::middleware([AdminMiddleware::class, SanitizeInputs::class])->group(function () {
    $settings = [
        'email' => App\Livewire\Admin\Settings\System\EmailSettings::class,
        'system' => App\Livewire\Admin\Settings\System\SystemSettings::class,
        're-captcha' => App\Livewire\Admin\Settings\System\ReCaptchaSettings::class,
        'cron-job' => App\Livewire\Admin\Settings\System\CronJobSettings::class,
        'system-update' => App\Livewire\Admin\Settings\System\SystemUpdateSettings::class,
        'announcement' => App\Livewire\Admin\Settings\System\AnnouncementSettings::class,
        'tenant-settings' => App\Livewire\Admin\Settings\System\TenantSettings::class,
        'invoice-settings' => App\Livewire\Admin\Settings\System\InvoiceSettings::class,
        'privacy-policy' => App\Livewire\Admin\Settings\System\PrivacyPolicySettings::class,
        'terms-conditions' => App\Livewire\Admin\Settings\System\TermsConditionsSettings::class,
        'miscellaneous' => App\Livewire\Admin\Settings\System\MiscellaneousSettings::class,

    ];

    $settings = apply_filters('admin_settings_navigation_route', $settings);

    // Add theme style routes directly
    Route::get('/theme-style', [App\Http\Controllers\Admin\ThemeStyleController::class, 'index'])
        ->name('theme-style.index');

    Route::post('/theme-style/save', [App\Http\Controllers\Admin\ThemeStyleController::class, 'save'])
        ->name('theme-style.save');

    foreach ($settings as $prefix => $component) {
        Route::get("/{$prefix}", $component)->name("{$prefix}.settings.view");
    }
});
