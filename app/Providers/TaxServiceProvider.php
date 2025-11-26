<?php

namespace App\Providers;

use App\Services\TaxCache;
use Illuminate\Support\ServiceProvider;

class TaxServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        $this->app->singleton('tax.cache', function ($app) {
            return new class
            {
                public function getAllTaxes()
                {
                    return TaxCache::getAllTaxes();
                }

                public function getTaxById($id)
                {
                    return TaxCache::getTaxById($id);
                }

                public function reset()
                {
                    return TaxCache::reset();
                }
            };
        });
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        //
    }
}
