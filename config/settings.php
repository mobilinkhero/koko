<?php

return [

    /*
     * Each settings class used in your application must be registered, you can
     * put them (manually) here.
     */
    'settings' => [

    ],

    /*
     * The path where the settings classes will be created.
     */
    'setting_class_path' => app_path('Settings'),

    /*
     * In these directories settings migrations will be stored and ran when migrating. A settings
     * migration created via the make:settings-migration command will be stored in the first path or
     * a custom defined path when running the command.
     */
    'migrations_paths' => [
        database_path('settings'),
    ],

    /*
     * When no repository was set for a settings class the following repository
     * will be used for loading and saving settings.
     */
    'default_repository' => 'database',

    /*
     * Settings will be stored and loaded from these repositories.
     */
    'repositories' => [
        'database' => [
            'type' => Spatie\LaravelSettings\SettingsRepositories\DatabaseSettingsRepository::class,
            'model' => null,
            'table' => null,
            'connection' => null,
        ],
        'redis' => [
            'type' => Spatie\LaravelSettings\SettingsRepositories\RedisSettingsRepository::class,
            'connection' => null,
            'prefix' => null,
        ],
    ],

    /*
     * The encoder and decoder will determine how settings are stored and
     * retrieved in the database. By default, `json_encode` and `json_decode`
     * are used.
     */
    'encoder' => null,
    'decoder' => null,

    /*
     * The contents of settings classes can be cached through your application,
     * settings will be stored within a provided Laravel store and can have an
     * additional prefix.
     *
     * ENHANCED: Integrated with Corbital Settings for enhanced caching performance
     */
    'cache' => [
        'enabled' => env('SETTINGS_CACHE_ENABLED', true),
        'store' => null,
        'prefix' => 'settings_',
        'ttl' => env('SETTINGS_CACHE_TTL', 3600),
    ],

    /*
     * These global casts will be automatically used whenever a property within
     * your settings class isn't a default PHP type.
     */
    'global_casts' => [
        DateTimeInterface::class => Spatie\LaravelSettings\SettingsCasts\DateTimeInterfaceCast::class,
        DateTimeZone::class => Spatie\LaravelSettings\SettingsCasts\DateTimeZoneCast::class,
        //        Spatie\DataTransferObject\DataTransferObject::class => Spatie\LaravelSettings\SettingsCasts\DtoCast::class,
        // Spatie\LaravelData\Data::class => Spatie\LaravelSettings\SettingsCasts\DataCast::class,
    ],

    /*
     * The package will look for settings in these paths and automatically
     * register them.
     */
    'auto_discover_settings' => [
        app_path('Settings'),
    ],

    /*
     * Automatically discovered settings classes can be cached, so they don't
     * need to be searched each time the application boots up.
     */
    'discovered_settings_cache_path' => base_path('bootstrap/cache'),

    /*
    |--------------------------------------------------------------------------
    | CORBITAL SETTINGS EXTENSIONS
    |--------------------------------------------------------------------------
    |
    | These are additional configuration options for the Corbital Settings
    | package that extend Spatie Laravel Settings with enhanced features.
    |
    */

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
