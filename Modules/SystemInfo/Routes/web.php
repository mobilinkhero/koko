<?php

use App\Http\Middleware\AdminMiddleware;
use App\Http\Middleware\SanitizeInputs;
use Illuminate\Support\Facades\Route;

Route::middleware([AdminMiddleware::class, SanitizeInputs::class])->group(function () {
    Route::get('admin/system-information', '\\Modules\\SystemInfo\\Livewire\\Admin\\Settings\\System\\SystemInformationSettings')->name('admin.system-information.settings.view');
});
