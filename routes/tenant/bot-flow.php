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

use App\Http\Controllers\Tenant\BotFlowController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function () {
    Route::get('/get-bot-flow/{id}', [BotFlowController::class, 'get']);
    Route::post('/save-bot-flow', [BotFlowController::class, 'save']);
    Route::get('/whatsapp-templates', [BotFlowController::class, 'getTemplates']);
    Route::post('/upload-media', [BotFlowController::class, 'upload']);
});
