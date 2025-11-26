<?php

namespace App\Providers;

use App\Multitenancy\PathTenantFinder;
use Illuminate\Support\ServiceProvider;

class MultitenancyServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        // Register the tenant finder
        $this->app->singleton('tenant.finder', PathTenantFinder::class);

    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        //
    }
}
