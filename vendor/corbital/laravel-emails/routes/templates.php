<?php

use Corbital\LaravelEmails\Http\Controllers\EmailLayoutController;
use Corbital\LaravelEmails\Http\Controllers\EmailTemplateController;
use Corbital\LaravelEmails\Http\Controllers\TemplatePreviewController;
use Illuminate\Support\Facades\Route;

// Admin routes for email templates management only
Route::group([
    'prefix' => config('laravel-emails.admin_route.prefix', 'admin/emails'),
    'middleware' => config('laravel-emails.admin_route.middleware', ['web', 'auth']),
    'as' => 'laravel-emails.',
], function () {
    // Template preview
    Route::get('templates/{slug}/preview', [TemplatePreviewController::class, 'preview'])->name('templates.preview');
    Route::post('templates/{slug}/test', [TemplatePreviewController::class, 'sendTest'])->name('templates.test');
    Route::get('templates/list', [TemplatePreviewController::class, 'index'])->name('templates.list');

    // Layouts
    Route::get('layouts', [EmailLayoutController::class, 'index'])->name('layouts.index');
    Route::get('layouts/create', [EmailLayoutController::class, 'create'])->name('layouts.create');
    Route::post('layouts', [EmailLayoutController::class, 'store'])->name('layouts.store');
    Route::get('layouts/{layout}', [EmailLayoutController::class, 'show'])->name('layouts.show');
    Route::get('layouts/{layout}/edit', [EmailLayoutController::class, 'edit'])->name('layouts.edit');
    Route::put('layouts/{layout}', [EmailLayoutController::class, 'update'])->name('layouts.update');
    Route::delete('layouts/{layout}', [EmailLayoutController::class, 'destroy'])->name('layouts.destroy');
    Route::get('layouts/{layout}/preview', [EmailLayoutController::class, 'preview'])->name('layouts.preview');

    // Templates
    Route::get('templates', [EmailTemplateController::class, 'index'])->name('templates.index');
    Route::get('templates/create', [EmailTemplateController::class, 'create'])->name('templates.create');
    Route::post('templates', [EmailTemplateController::class, 'store'])->name('templates.store');
    Route::get('templates/{template}', [EmailTemplateController::class, 'show'])->name('templates.show');
    Route::get('templates/{template}/edit', [EmailTemplateController::class, 'edit'])->name('templates.edit');
    Route::put('templates/{template}', [EmailTemplateController::class, 'update'])->name('templates.update');
    Route::delete('templates/{template}', [EmailTemplateController::class, 'destroy'])->name('templates.destroy');
    Route::post('templates/{template}/preview', [EmailTemplateController::class, 'preview'])->name('templates.preview');
});
