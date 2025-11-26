<?php

namespace App\Providers;

use App\Models\Tenant;
use App\Services\TenantCacheService;
use Illuminate\Support\ServiceProvider;

class TenantServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        // Simple facade-like binding
        $this->app->bind('tenant', function () {
            return new class
            {
                public function current()
                {
                    $tenant = Tenant::current();

                    return $tenant;
                }

                public function checkCurrent()
                {
                    return Tenant::checkCurrent();
                }
            };
        });

        // Current tenant singleton
        $this->app->singleton('currentTenant', function () {
            return null; // Will be set by middleware
        });
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        // Listen for tenant resolution events to cache the tenant
        if ($this->app->bound('events')) {
            $this->app['events']->listen('tenant.resolved', function ($tenantId) {
                if (! empty($tenantId)) {
                    // Cache tenant to avoid repeated queries
                    TenantCacheService::remember($tenantId);
                }
            });

            // Clear plan feature cache when any plan-related models are updated or deleted
            // Combine multiple listeners into one for better maintainability
            $planModelEvents = [
                'eloquent.saved: App\Models\Plan',
                'eloquent.deleted: App\Models\Plan',
                'eloquent.saved: App\Models\Feature',
                'eloquent.deleted: App\Models\Feature',
                'eloquent.saved: App\Models\PlanFeature',
                'eloquent.deleted: App\Models\PlanFeature',
            ];

            $this->app['events']->listen($planModelEvents, function () {
                if (function_exists('tenant_id') && tenant_id()) {
                    \App\Services\PlanFeatureCache::clearCache();
                }
            });

            // Listen for currency model events to clear currency cache
            $currencyModelEvents = [
                'eloquent.saved: App\Models\Currency',
                'eloquent.deleted: App\Models\Currency',
            ];

            $this->app['events']->listen($currencyModelEvents, function () {
                \App\Services\CurrencyCache::clearCache();
            });

            // Listen for subscription model events to clear subscription cache
            $subscriptionModelEvents = [
                'eloquent.saved: App\Models\Subscription',
                'eloquent.deleted: App\Models\Subscription',
            ];

            $this->app['events']->listen($subscriptionModelEvents, function () {
                if (function_exists('tenant_id') && tenant_id()) {
                    \App\Services\SubscriptionCache::clearCache(tenant_id());
                }
            });
        }
    }
}
