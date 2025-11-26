<?php

use Corbital\ModuleManager\Http\Controllers\ModuleController;
use Corbital\ModuleManager\Http\Livewire\ModuleList;
use Illuminate\Support\Facades\Route;

Route::middleware(['web', 'auth'])->prefix('modules')->name('modules.')->group(function () {
    Route::get('/', ModuleList::class)->name('index');
    Route::get('/upload', [ModuleController::class, 'showUploadForm'])->name('upload');
    Route::post('/upload', [ModuleController::class, 'upload'])->name('upload.process');
    Route::get('/{name}', [ModuleController::class, 'show'])->name('show');
    Route::post('/{name}/activate', [ModuleController::class, 'activate'])->name('activate');
    Route::post('/{name}/deactivate', [ModuleController::class, 'deactivate'])->name('deactivate');
    Route::delete('/{name}', [ModuleController::class, 'remove'])->name('remove');
});
