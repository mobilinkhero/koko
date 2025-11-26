<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Tenant Cache Configuration
    |--------------------------------------------------------------------------
    |
    | Configuration for the multi-tenant cache management system.
    | This controls cache behavior, dependencies, and strategies for tenant data.
    |
    */

    /*
    |--------------------------------------------------------------------------
    | Cache Store
    |--------------------------------------------------------------------------
    |
    | The cache store to use for tenant cache. If null, uses the default
    | Laravel cache store from config/cache.php.
    |
    */
    'store' => env('TENANT_CACHE_STORE', null), // Uses default cache store

    /*
    |--------------------------------------------------------------------------
    | Default TTL (Time To Live)
    |--------------------------------------------------------------------------
    |
    | Default cache lifetime in seconds for tenant cache entries.
    | Individual tags can override this in the registry.
    |
    */
    'default_ttl' => env('TENANT_CACHE_TTL', 3600), // 1 hour

    /*
    |--------------------------------------------------------------------------
    | Cache Prefix
    |--------------------------------------------------------------------------
    |
    | Prefix for all tenant cache keys to avoid conflicts.
    | This works in addition to Laravel's cache prefix.
    |
    */
    'prefix' => env('TENANT_CACHE_PREFIX', 'tenant'),

    /*
    |--------------------------------------------------------------------------
    | Enable Cache Tags
    |--------------------------------------------------------------------------
    |
    | Whether to use cache tags. Automatically detected based on cache driver,
    | but can be forced on/off here.
    |
    */
    'enable_tags' => env('TENANT_CACHE_ENABLE_TAGS', null),

    /*
    |--------------------------------------------------------------------------
    | Auto-invalidation
    |--------------------------------------------------------------------------
    |
    | Whether to automatically invalidate cache when models change.
    |
    */
    'auto_invalidation' => env('TENANT_CACHE_AUTO_INVALIDATION', true),

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
        'tenant.settings' => ['tenant.navigation', 'tenant.dashboard'],
        'tenant.users' => ['tenant.dashboard', 'tenant.statistics'],
        'tenant.subscriptions' => ['tenant.dashboard'],
        'tenant.campaigns' => ['tenant.dashboard', 'tenant.statistics'],
        'tenant.templates' => ['tenant.editor', 'tenant.campaigns'],
        'tenant.contacts' => ['tenant.dashboard', 'tenant.statistics'],
        'model.template' => ['tenant.editor', 'tenant.campaigns'],
        'model.campaign' => ['tenant.dashboard', 'tenant.statistics'],
        'model.contact' => ['tenant.dashboard', 'tenant.statistics'],
        'model.group' => ['tenant.contacts', 'tenant.campaigns'],
        'model.message' => ['tenant.campaigns', 'tenant.statistics'],
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
        'enabled' => env('TENANT_CACHE_WARMUP_ENABLED', true),
        'on_boot' => env('TENANT_CACHE_WARMUP_ON_BOOT', false),
        'critical_only' => env('TENANT_CACHE_WARMUP_CRITICAL_ONLY', true),
        'queue' => env('TENANT_CACHE_WARMUP_QUEUE', 'default'),
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
        'enabled' => env('TENANT_CACHE_MONITORING_ENABLED', true),
        'log_hits' => env('TENANT_CACHE_LOG_HITS', false),
        'log_misses' => env('TENANT_CACHE_LOG_MISSES', true),
        'log_invalidations' => env('TENANT_CACHE_LOG_INVALIDATIONS', true),
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
        'batch_size' => env('TENANT_CACHE_BATCH_SIZE', 100),
        'max_tags_per_key' => env('TENANT_CACHE_MAX_TAGS_PER_KEY', 10),
        'compression' => env('TENANT_CACHE_COMPRESSION', false),
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
        'on_failure' => env('TENANT_CACHE_FALLBACK_STRATEGY', 'return_default'),
        'retry_attempts' => env('TENANT_CACHE_RETRY_ATTEMPTS', 2),
        'retry_delay' => env('TENANT_CACHE_RETRY_DELAY', 100), // milliseconds
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
        'disable_in_testing' => env('TENANT_CACHE_DISABLE_IN_TESTING', true),
        'debug_mode' => env('TENANT_CACHE_DEBUG', false),
        'force_refresh' => env('TENANT_CACHE_FORCE_REFRESH', false),
    ],

    /*
    |--------------------------------------------------------------------------
    | Tenant-Specific Settings
    |--------------------------------------------------------------------------
    |
    | Settings specific to multi-tenant architecture.
    |
    */
    'tenant' => [
        'isolate_tenants' => env('TENANT_CACHE_ISOLATE_TENANTS', true),
        'shared_cache_allowed' => env('TENANT_CACHE_SHARED_ALLOWED', false),
        'max_size_per_tenant' => env('TENANT_CACHE_MAX_SIZE', 50 * 1024 * 1024), // 50MB
        'auto_prune' => env('TENANT_CACHE_AUTO_PRUNE', true),
    ],
];
