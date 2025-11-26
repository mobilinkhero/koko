<?php

use Illuminate\Support\Facades\Route;

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

Route::middleware('api')->prefix('api')->group(function () {
    Route::prefix('LogViewer')->group(function () {
        // API routes here
    });
});
