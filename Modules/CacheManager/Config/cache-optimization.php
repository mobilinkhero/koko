<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Cache Optimization Configuration
    |--------------------------------------------------------------------------
    |
    | This configuration controls the periodic cache optimization system
    | that ensures your application caches are optimized and performing well.
    |
    */

    /*
    |--------------------------------------------------------------------------
    | Cache Optimization Job Schedule
    |--------------------------------------------------------------------------
    |
    | This option allows you to configure when the cache optimization
    | will be performed. The optimization is triggered by Laravel's scheduler
    | and runs asynchronously to improve cache performance.
    |
    */

    'job_schedule' => [
        'enabled' => env('CACHEMANAGER_CACHE_OPTIMIZATION_ENABLED', true),
    ],
];
