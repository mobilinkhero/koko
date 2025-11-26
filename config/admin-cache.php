<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Admin Cache Configuration
    |--------------------------------------------------------------------------
    |
    | Configuration for the centralized admin cache management system.
    | This controls cache behavior, dependencies, and strategies.
    |
    */

    /*
    |--------------------------------------------------------------------------
    | Cache Store
    |--------------------------------------------------------------------------
    |
    | The cache store to use for admin cache. If null, uses the default
    | Laravel cache store from config/cache.php.
    |
    */
    'store' => env('ADMIN_CACHE_STORE', null), // Uses default cache store

    /*
    |--------------------------------------------------------------------------
    | Default TTL (Time To Live)
    |--------------------------------------------------------------------------
    |
    | Default cache lifetime in seconds for admin cache entries.
    | Individual tags can override this in the registry.
    |
    */
    'default_ttl' => env('ADMIN_CACHE_TTL', 3600), // 1 hour

    /*
    |--------------------------------------------------------------------------
    | Cache Prefix
    |--------------------------------------------------------------------------
    |
    | Prefix for all admin cache keys to avoid conflicts.
    | This works in addition to Laravel's cache prefix.
    |
    */
    'prefix' => env('ADMIN_CACHE_PREFIX', 'admin'),

    /*
    |--------------------------------------------------------------------------
    | Enable Cache Tags
    |--------------------------------------------------------------------------
    |
    | Whether to use cache tags. Automatically detected based on cache driver,
    | but can be forced on/off here.
    |
    */
    'enable_tags' => env('ADMIN_CACHE_ENABLE_TAGS', null),

    /*
    |--------------------------------------------------------------------------
    | Auto-invalidation
    |--------------------------------------------------------------------------
    |
    | Whether to automatically invalidate cache when models change.
    |
    */
    'auto_invalidation' => env('ADMIN_CACHE_AUTO_INVALIDATION', true),

    /*
    |--------------------------------------------------------------------------
    | Cache Dependencies
    |--------------------------------------------------------------------------
    |
    | Define cache tag dependencies. When a source tag is cleared,
    | dependent tags will also be cleared automatically.
    |
    */
    'dependencies' => [
        'admin.plans' => ['admin.navigation', 'admin.dashboard', 'frontend.pricing'],
        'admin.users' => ['admin.dashboard', 'admin.statistics'],
        'admin.tenants' => ['admin.dashboard', 'admin.statistics'],
        'admin.settings' => ['admin.navigation', 'frontend.menu'],
        'admin.permissions' => ['admin.navigation'],
        'admin.roles' => ['admin.navigation', 'admin.permissions'],
        'admin.transactions' => ['admin.dashboard', 'admin.statistics'],
        'admin.currencies' => ['admin.settings', 'frontend.pricing'],
        'model.language' => ['admin.navigation', 'frontend.menu'],
        'model.currency' => ['admin.settings', 'frontend.pricing'],
        'model.plan' => ['admin.dashboard', 'frontend.pricing'],
        'model.user' => ['admin.dashboard', 'admin.statistics'],
        'model.tenant' => ['admin.dashboard', 'admin.statistics'],
        'model.page' => ['admin.navigation', 'frontend.menu'],
    ],

    /*
    |--------------------------------------------------------------------------
    | Warmup Configuration
    |--------------------------------------------------------------------------
    |
    | Configuration for cache warming strategies.
    |
    */
    'warmup' => [
        'enabled' => env('ADMIN_CACHE_WARMUP_ENABLED', true),
        'on_boot' => env('ADMIN_CACHE_WARMUP_ON_BOOT', false),
        'critical_only' => env('ADMIN_CACHE_WARMUP_CRITICAL_ONLY', true),
        'queue' => env('ADMIN_CACHE_WARMUP_QUEUE', 'default'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Monitoring and Logging
    |--------------------------------------------------------------------------
    |
    | Configuration for cache monitoring and logging.
    |
    */
    'monitoring' => [
        'enabled' => env('ADMIN_CACHE_MONITORING_ENABLED', true),
        'log_hits' => env('ADMIN_CACHE_LOG_HITS', false),
        'log_misses' => env('ADMIN_CACHE_LOG_MISSES', true),
        'log_invalidations' => env('ADMIN_CACHE_LOG_INVALIDATIONS', true),
    ],

    /*
    |--------------------------------------------------------------------------
    | Performance Settings
    |--------------------------------------------------------------------------
    |
    | Performance-related cache settings.
    |
    */
    'performance' => [
        'batch_size' => env('ADMIN_CACHE_BATCH_SIZE', 100),
        'max_tags_per_key' => env('ADMIN_CACHE_MAX_TAGS_PER_KEY', 10),
        'compression' => env('ADMIN_CACHE_COMPRESSION', false),
    ],

    /*
    |--------------------------------------------------------------------------
    | Fallback Strategies
    |--------------------------------------------------------------------------
    |
    | What to do when cache operations fail.
    |
    */
    'fallback' => [
        'on_failure' => env('ADMIN_CACHE_FALLBACK_STRATEGY', 'return_default'),
        'retry_attempts' => env('ADMIN_CACHE_RETRY_ATTEMPTS', 2),
        'retry_delay' => env('ADMIN_CACHE_RETRY_DELAY', 100), // milliseconds
    ],

    /*
    |--------------------------------------------------------------------------
    | Development Settings
    |--------------------------------------------------------------------------
    |
    | Settings for development and debugging.
    |
    */
    'development' => [
        'disable_in_testing' => env('ADMIN_CACHE_DISABLE_IN_TESTING', true),
        'debug_mode' => env('ADMIN_CACHE_DEBUG', false),
        'force_refresh' => env('ADMIN_CACHE_FORCE_REFRESH', false),
    ],
];
