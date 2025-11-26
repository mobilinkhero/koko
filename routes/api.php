<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\BotFlowController;
use App\Http\Controllers\Api\CampaignController;
use App\Http\Controllers\Api\ContactController;
use App\Http\Controllers\Api\TenantController;
use App\Http\Controllers\Api\WebhookController;
use App\Http\Controllers\Api\WhatsAppController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

// Public authentication routes
Route::prefix('auth')->group(function () {
    Route::post('/login', [AuthController::class, 'login']);
    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/forgot-password', [AuthController::class, 'forgotPassword']);
    Route::post('/reset-password', [AuthController::class, 'resetPassword']);
    Route::post('/verify-email', [AuthController::class, 'verifyEmail']);
});

// Webhook routes (unauthenticated)
Route::prefix('webhooks')->group(function () {
    Route::post('/whatsapp/{provider}', [WebhookController::class, 'whatsapp']);
    Route::get('/whatsapp/{provider}/verify', [WebhookController::class, 'verifyWebhook']);
});

// Protected API routes
Route::middleware(['auth:sanctum'])->group(function () {

    // Authentication management
    Route::prefix('auth')->group(function () {
        Route::post('/logout', [AuthController::class, 'logout']);
        Route::get('/user', [AuthController::class, 'user']);
        Route::post('/refresh', [AuthController::class, 'refresh']);
    });

    // Tenant management
    Route::prefix('tenant')->group(function () {
        Route::get('/', [TenantController::class, 'current']);
        Route::put('/', [TenantController::class, 'update']);
        Route::get('/settings', [TenantController::class, 'settings']);
        Route::put('/settings', [TenantController::class, 'updateSettings']);
        Route::get('/subscription', [TenantController::class, 'subscription']);
    });

    // Contact management
    Route::prefix('contacts')->group(function () {
        Route::get('/', [ContactController::class, 'index']);
        Route::post('/', [ContactController::class, 'store']);
        Route::get('/{id}', [ContactController::class, 'show']);
        Route::put('/{id}', [ContactController::class, 'update']);
        Route::delete('/{id}', [ContactController::class, 'destroy']);
        Route::post('/import', [ContactController::class, 'import']);
        Route::get('/export', [ContactController::class, 'export']);
        Route::post('/bulk-action', [ContactController::class, 'bulkAction']);
    });

    // Campaign management
    Route::prefix('campaigns')->group(function () {
        Route::get('/', [CampaignController::class, 'index']);
        Route::post('/', [CampaignController::class, 'store']);
        Route::get('/{id}', [CampaignController::class, 'show']);
        Route::put('/{id}', [CampaignController::class, 'update']);
        Route::delete('/{id}', [CampaignController::class, 'destroy']);
        Route::post('/{id}/start', [CampaignController::class, 'start']);
        Route::post('/{id}/pause', [CampaignController::class, 'pause']);
        Route::post('/{id}/stop', [CampaignController::class, 'stop']);
        Route::get('/{id}/analytics', [CampaignController::class, 'analytics']);
    });

    // Bot flow management
    Route::prefix('bot-flows')->group(function () {
        Route::get('/', [BotFlowController::class, 'index']);
        Route::post('/', [BotFlowController::class, 'store']);
        Route::get('/{id}', [BotFlowController::class, 'show']);
        Route::put('/{id}', [BotFlowController::class, 'update']);
        Route::delete('/{id}', [BotFlowController::class, 'destroy']);
        Route::post('/{id}/publish', [BotFlowController::class, 'publish']);
        Route::post('/{id}/test', [BotFlowController::class, 'test']);
    });

    // WhatsApp integration
    Route::prefix('whatsapp')->group(function () {
        Route::get('/templates', [WhatsAppController::class, 'templates']);
        Route::post('/send-message', [WhatsAppController::class, 'sendMessage']);
        Route::post('/upload-media', [WhatsAppController::class, 'uploadMedia']);
        Route::get('/phone-numbers', [WhatsAppController::class, 'phoneNumbers']);
        Route::post('/verify-phone', [WhatsAppController::class, 'verifyPhone']);
        Route::get('/webhooks', [WhatsAppController::class, 'webhooks']);
        Route::post('/webhooks', [WhatsAppController::class, 'createWebhook']);
    });
});

// Admin API routes
Route::prefix('admin')->middleware(['auth:sanctum', 'admin'])->group(function () {
    Route::get('/tenants', [TenantController::class, 'adminIndex']);
    Route::post('/tenants', [TenantController::class, 'adminStore']);
    Route::get('/tenants/{id}', [TenantController::class, 'adminShow']);
    Route::put('/tenants/{id}', [TenantController::class, 'adminUpdate']);
    Route::delete('/tenants/{id}', [TenantController::class, 'adminDestroy']);
    Route::get('/analytics', [TenantController::class, 'adminAnalytics']);
});
