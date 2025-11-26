<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Settings Cache TTL
    |--------------------------------------------------------------------------
    |
    | This value determines the time-to-live for the settings cache in seconds.
    | Default is 3600 seconds (1 hour). Set to null for no expiration.
    |
    */
    'cache_ttl' => env('SETTINGS_CACHE_TTL', 3600),

    /*
    |--------------------------------------------------------------------------
    | Cache Key Prefix
    |--------------------------------------------------------------------------
    |
    | This prefix will be used when storing settings in the cache to avoid
    | conflicts with other cached data.
    |
    */
    'cache_prefix' => 'settings_',

    /*
    |--------------------------------------------------------------------------
    | Enable Tenant Support
    |--------------------------------------------------------------------------
    |
    | Enable or disable tenant-specific settings functionality.
    |
    */
    'enable_tenant_support' => env('SETTINGS_ENABLE_TENANT_SUPPORT', true),

    /*
    |--------------------------------------------------------------------------
    | Debug Mode
    |--------------------------------------------------------------------------
    |
    | When enabled, additional debugging information will be logged.
    |
    */
    'debug_mode' => env('SETTINGS_DEBUG_MODE', false),

    /*
    |--------------------------------------------------------------------------
    | Cache Lock Configuration
    |--------------------------------------------------------------------------
    |
    | Configuration for distributed locking to prevent cache stampedes.
    |
    */
    'lock_timeout' => env('SETTINGS_LOCK_TIMEOUT', 10),
    'lock_retry_delay' => env('SETTINGS_LOCK_RETRY_DELAY', 100),

    /*
    |--------------------------------------------------------------------------
    | Cache Warming Keys
    |--------------------------------------------------------------------------
    |
    | Common settings that should be preloaded for performance.
    |
    */
    'warm_cache_keys' => [
        'app.name',
        'app.timezone',
        'app.locale',
        'app.currency',
    ],

    /*
    |--------------------------------------------------------------------------
    | Tenant Settings
    |--------------------------------------------------------------------------
    */

    'tenant_header_name' => env('SETTINGS_TENANT_HEADER', 'X-Tenant-ID'),

    'tenant_param_name' => env('SETTINGS_TENANT_PARAM', 'tenant_id'),

    'tenant_subdomain_identification' => env('SETTINGS_TENANT_SUBDOMAIN', false),
];
