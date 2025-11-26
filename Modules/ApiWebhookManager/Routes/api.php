<?php

use Illuminate\Support\Facades\Route;
use Modules\ApiWebhookManager\Http\Controllers\Api\Admin\PlanApiController;
use Modules\ApiWebhookManager\Http\Controllers\Api\ContactController;
use Modules\ApiWebhookManager\Http\Controllers\Api\GroupController;
use Modules\ApiWebhookManager\Http\Controllers\Api\MessageBotController;
use Modules\ApiWebhookManager\Http\Controllers\Api\MessageController;
use Modules\ApiWebhookManager\Http\Controllers\Api\SourceController;
use Modules\ApiWebhookManager\Http\Controllers\Api\StatusController;
use Modules\ApiWebhookManager\Http\Controllers\Api\TemplateBotController;
use Modules\ApiWebhookManager\Http\Controllers\Api\TemplateController;
use Modules\ApiWebhookManager\Http\Middleware\TenantValidationMiddleware;
use Modules\ApiWebhookManager\Http\Middleware\ValidateAdminApiToken;

Route::middleware(TenantValidationMiddleware::class)->group(function () {
    Route::prefix('v1/{subdomain}')->as('tenant.')->group(function () {
        $resources = [
            'contacts' => ContactController::class,
            'statuses' => StatusController::class,
            'sources' => SourceController::class,
        ];

        foreach ($resources as $resource => $controller) {

            Route::middleware("api.token:{$resource}.create")->post("/{$resource}", [$controller, 'store']);
            Route::middleware("api.token:{$resource}.read")->get("/{$resource}", [$controller, 'index']);
            Route::middleware("api.token:{$resource}.read")->get("/{$resource}/{id}", [$controller, 'show']);
            Route::middleware("api.token:{$resource}.update")->put("/{$resource}/{id}", [$controller, 'update']);
            Route::middleware("api.token:{$resource}.delete")->delete("/{$resource}/{id}", [$controller, 'destroy']);
        }

        Route::middleware('api.token:templates.read')->get('/templates', [TemplateController::class, 'index']);
        Route::middleware('api.token:templates.read')->get('/templates/{id}', [TemplateController::class, 'show']);

        Route::middleware('api.token:templatebots.read')->get('/templatebots', [TemplateBotController::class, 'index']);
        Route::middleware('api.token:templatebots.read')->get('/templatebots/{id}', [TemplateBotController::class, 'show']);

        Route::middleware('api.token:messagebots.read')->get('/messagebots', [MessageBotController::class, 'index']);
        Route::middleware('api.token:messagebots.read')->get('/messagebots/{id}', [MessageBotController::class, 'show']);

        Route::middleware('api.token:groups.read')->get('/groups', [GroupController::class, 'index']);
        Route::middleware('api.token:groups.read')->get('/groups/{id}', [GroupController::class, 'show']);
        Route::middleware('api.token:groups.create')->post('/groups', [GroupController::class, 'store']);
        Route::middleware('api.token:groups.update')->put('/groups/{id}', [GroupController::class, 'update']);
        Route::middleware('api.token:groups.delete')->delete('/groups/{id}', [GroupController::class, 'destroy']);

        // Messages - Send simple message to contact
        Route::middleware('api.token:messages.send')->post('/messages/send', [MessageController::class, 'sendMessage'])->name('messages.send');

        // Messages - Send template message to contact
        Route::middleware('api.token:messages.send')->post('/messages/template', [MessageController::class, 'sendTemplateMessage'])->name('messages.template');

        // Messages - Send media message to contact
        Route::middleware('api.token:messages.send')->post('/messages/media', [MessageController::class, 'sendMediaMessage'])->name('messages.media');
    });
});

Route::middleware(ValidateAdminApiToken::class)->group(function () {
    Route::prefix('admin/v1/')->as('admin.')->group(function () {

        Route::middleware('admin.api.token:plans.read')->get('/plans', [PlanApiController::class, 'index']);
        Route::middleware('admin.api.token:plans.read')->get('/plans/{id}', [PlanApiController::class, 'show']);

    });
});
