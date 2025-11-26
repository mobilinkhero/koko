<?php

use App\Http\Middleware\SanitizeInputs;
use App\Http\Middleware\TenantMiddleware;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', SanitizeInputs::class, TenantMiddleware::class])->group(function () {
    Route::prefix('/{subdomain}')->as('tenant.')->group(function () {
        $settings = [
            'whatsapp-auto-lead' => App\Livewire\Tenant\Settings\WhatsMark\WhatsappAutoLeadSettings::class,
            'stop-bot' => App\Livewire\Tenant\Settings\WhatsMark\StopBotSettings::class,
            'whatsapp-web-hooks' => App\Livewire\Tenant\Settings\WhatsMark\WebHooksSettings::class,
            'support-agent' => App\Livewire\Tenant\Settings\WhatsMark\SupportAgentSettings::class,
            'notification-sound' => App\Livewire\Tenant\Settings\WhatsMark\NotificationSoundSettings::class,
            'ai-integration' => App\Livewire\Tenant\Settings\WhatsMark\AiIntegrationSettings::class,
            'auto-clear-chat-history' => App\Livewire\Tenant\Settings\WhatsMark\AutoClearChatHistorySettings::class,
        ];

        foreach ($settings as $prefix => $component) {
            Route::get("/settings/{$prefix}", $component)->name("settings.{$prefix}");
        }
    });
});
