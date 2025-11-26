<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Enabled
    |--------------------------------------------------------------------------
    |
    | Determines if the module system is enabled.
    |
    */
    'enabled' => env('MODULES_ENABLED', true),

    /*
    |--------------------------------------------------------------------------
    | Module Directory
    |--------------------------------------------------------------------------
    |
    | This is the directory where all modules are stored.
    |
    */
    'directory' => base_path('Modules'),

    /*
    |--------------------------------------------------------------------------
    | Active Modules
    |--------------------------------------------------------------------------
    |
    | This array stores the names of all active modules.
    |
    */
    'active' => [
    ],

    /*
    |--------------------------------------------------------------------------
    | Auto Load Migrations
    |--------------------------------------------------------------------------
    |
    | Determine whether to automatically load migrations from all modules.
    |
    */
    'autoload_migrations' => true,

    /*
    |--------------------------------------------------------------------------
    | Auto Register Routes
    |--------------------------------------------------------------------------
    |
    | Determine whether to automatically register routes from all modules.
    |
    */
    'autoload_routes' => true,

    /*
    |--------------------------------------------------------------------------
    | Repository Settings
    |--------------------------------------------------------------------------
    |
    | Settings for module repository and updates
    |
    */
    'repository' => [
        'url' => env('MODULE_REPOSITORY_URL', 'https://modules.example.com/api'),
        'check_for_updates' => env('MODULE_CHECK_UPDATES', true),
        'check_interval' => env('MODULE_CHECK_INTERVAL', 86400), // Default: once per day (in seconds)
        'last_checked' => null,                                // Will be updated when updates are checked
    ],

    /*
    |--------------------------------------------------------------------------
    | Path Configuration
    |--------------------------------------------------------------------------
    |
    | Configuration for module paths, similar to Nwidart's module paths
    |
    */
    'paths' => [
        'modules' => base_path('Modules'),
        'assets' => public_path('modules'),
        'generator' => [
            // Configuration and basic structure
            'config' => ['path' => 'Config', 'generate' => true],
            'command' => ['path' => 'Console', 'generate' => true],
            'migration' => ['path' => 'Database/Migrations', 'generate' => true],
            'seeder' => ['path' => 'Database/Seeders', 'generate' => true],
            'factory' => ['path' => 'Database/Factories', 'generate' => true],
            'model' => ['path' => 'Models', 'generate' => true],
            'routes' => ['path' => 'Routes', 'generate' => true],
            'provider' => ['path' => 'Providers', 'generate' => true],

            // HTTP layer components
            'controller' => ['path' => 'Http/Controllers', 'generate' => true],
            'request' => ['path' => 'Http/Requests', 'generate' => true],
            'filter' => ['path' => 'Http/Middleware', 'generate' => true],

            // Laravel components
            'event' => ['path' => 'Events', 'generate' => true],
            'jobs' => ['path' => 'Jobs', 'generate' => true],
            'listener' => ['path' => 'Listeners', 'generate' => true],
            'notifications' => ['path' => 'Notifications', 'generate' => true],
            'policies' => ['path' => 'Policies', 'generate' => true],
            'rules' => ['path' => 'Rules', 'generate' => true],
            'exceptions' => ['path' => 'Exceptions', 'generate' => true],
            'observer' => ['path' => 'Observers', 'generate' => true],

            // Resources and views
            'views' => ['path' => 'resources/views', 'generate' => true],

            // Testing
            'test-unit' => ['path' => 'Tests/Unit', 'generate' => false],
            'test-feature' => ['path' => 'Tests/Feature', 'generate' => false],
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Module namespaces
    |--------------------------------------------------------------------------
    |
    | These are the default module namespaces used by modules.
    |
    */
    'namespaces' => [
        'modules' => 'Modules',
        'controller' => 'Modules\\{module}\\Http\\Controllers',
        'model' => 'Modules\\{module}\\Models',
        'view' => 'modules.{module}',
        'resource' => 'Modules\\{module}\\resources',
        'provider' => 'Modules\\{module}\\Providers',
    ],

    /*
    |--------------------------------------------------------------------------
    | Scan Path
    |--------------------------------------------------------------------------
    |
    | Here you define which paths will be scanned for modules. By default
    | this is the 'app/Modules' path.
    |
    */
    'scan' => [
        'enabled' => true,
        'paths' => [
            base_path('Modules'),
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Caching
    |--------------------------------------------------------------------------
    |
    | Here is the config for setting up caching feature.
    |
    */
    'cache' => [
        'enabled' => false,
        'key' => 'modules',
        'lifetime' => 60,
    ],

    /*
    |--------------------------------------------------------------------------
    | Module Types
    |--------------------------------------------------------------------------
    |
    | Configuration for different module types.
    |
    */
    'types' => [
        'core' => [
            'can_deactivate' => false,
            'can_remove' => false,
        ],
        'addon' => [
            'can_deactivate' => true,
            'can_remove' => true,
        ],
        'custom' => [
            'can_deactivate' => true,
            'can_remove' => true,
        ],
    ],
];
