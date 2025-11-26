<?php

use Illuminate\Support\Facades\Route;
use Modules\CacheManager\Http\Controllers\Api\CacheApiController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your module. These
| routes are loaded by the ServiceProvider within a group which
| is assigned the "api" middleware group.
|
*/

Route::middleware('api')->group(function () {
    Route::prefix('cache-manager')->group(function () {
        // Cache-related POST endpoints with validation
        Route::post('cache-status', [CacheApiController::class, 'getCacheStatus']);
    });
});
