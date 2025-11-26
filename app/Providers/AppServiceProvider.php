<?php

namespace App\Providers;

use App\Models\Tenant;
use App\Services\LanguageService;
use App\Services\MailService;
use App\Services\pusher\PusherService;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;
use Livewire\Component;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton(PusherService::class);
        $this->app->singleton(LanguageService::class);

        // Register tenant cache service
        $this->app->singleton('tenant.cache', function ($app) {
            $tenantId = $this->getCurrentTenantId();

            return new \App\Services\Cache\TenantCacheManager($tenantId);
        });
    }

    /**
     * Get current tenant ID for cache service
     */
    private function getCurrentTenantId(): string
    {
        try {
            if (function_exists('tenant_id')) {
                return tenant_id() ?? 'default';
            }

            // Fallback methods
            if (session()->has('current_tenant_id')) {
                return session('current_tenant_id');
            }

            return 'default';
        } catch (\Exception $e) {
            return 'default';
        }
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Model::shouldBeStrict();
        DB::prohibitDestructiveCommands($this->app->environment('production'));

        $theme = 'default'; // Fallback name

        if (File::exists(base_path('theme.json'))) {
            $json = File::get(base_path('theme.json'));
            $data = json_decode($json, true);
            $theme = $data['name'] ?? $theme;
        }

        View::share('themeName', $theme);

        $this->configureMailService();

        $this->registerLivewireMacros();
        $this->configureTimezoneAndDateFormats();

        do_action('globally_registration');
    }

    /**
     * Configure timezone and date formats
     */
    private function configureTimezoneAndDateFormats(): void
    {
        if (Tenant::checkCurrent()) {
            $systemSettings = tenant_settings_by_group('system');

            $timezone = $systemSettings['timezone'] ?? config('app.timezone');
            $dateFormat = $systemSettings['date_format'] ?? config('app.date_format');
            $timeFormatSetting = $systemSettings['time_format'] ?? '24';
        } else {
            $systemSettings = get_batch_settings([
                'system.timezone',
                'system.date_format',
                'system.time_format',
            ]);

            $timezone = $systemSettings['system.timezone'] ?? config('app.timezone');
            $dateFormat = $systemSettings['system.date_format'] ?? config('app.date_format');
            $timeFormatSetting = $systemSettings['system.time_format'] ?? '24';
        }

        $timeFormat = $timeFormatSetting === '12' ? 'h:i A' : 'H:i';

        // Share with all views
        View::share('dateTimeSettings', [
            'timezone' => $timezone,
            'dateFormat' => $dateFormat,
            'timeFormat' => $timeFormat,
            'is24Hour' => $timeFormatSetting === '24',
        ]);
    }

    /**
     * Configure mail service settings
     */
    private function configureMailService(): void
    {
        app(MailService::class)->setMailConfig();
    }

    /**
     * Register Livewire component macros
     */
    private function registerLivewireMacros(): void
    {
        Component::macro('notify', function ($message, $isAfterRedirect = false) {
            $payload = [
                'message' => $message['message'] ?? '',
                'type' => $message['type'] ?? 'info',
            ];

            if ($isAfterRedirect) {
                session()->flash('notification', $payload);
            } else {
                $this->dispatch('notify', $payload);
            }
        });
    }
}
