<?php

namespace App\Providers;

use App\Services\SystemManagement;
use Illuminate\Support\ServiceProvider;

class SystemInfoServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        $this->app->singleton(SystemManagement::class);
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        //
    }
}
