<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Module Directory
    |--------------------------------------------------------------------------
    |
    | This is the directory where your modules will be stored. The default is
    | the 'Modules' directory in the application path.
    |
    */
    'directory' => app_path('Modules'),

    /*
    |--------------------------------------------------------------------------
    | Module Namespaces
    |--------------------------------------------------------------------------
    |
    | Namespaces for modules and their components.
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
    | Active Modules
    |--------------------------------------------------------------------------
    |
    | List of currently active modules.
    |
    */
    'active' => [
        // List modules to be auto-loaded here
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

    /*
    |--------------------------------------------------------------------------
    | Module Automatic Database Migration and Seeding
    |--------------------------------------------------------------------------
    |
    | Controls whether migrations and seeders are automatically run when
    | activating a module.
    |
    */
    'auto_migrations' => [
        'enabled' => true, // Whether to automatically run migrations on module activation
        'seed' => true, // Whether to automatically run seeders after migrations
    ],

    /*
    |--------------------------------------------------------------------------
    | Module Command Specific Settings
    |--------------------------------------------------------------------------
    |
    | Settings for the module:make command
    |
    */
    'stubs' => [
        'path' => base_path('vendor/corbital/module-manager/src/Console/Commands/stubs'),
        'files' => [
            'routes/web' => 'Routes/web.php',
            'routes/api' => 'Routes/api.php',
            'views/index' => 'resources/views/index.blade.php',
            'views/master' => 'resources/views/layouts/master.blade.php',
            'scaffold/config' => 'Config/config.php',
            'composer' => 'composer.json',
            'module' => 'module.json',
        ],
        'replacements' => [
            'routes/web' => ['LOWER_NAME', 'STUDLY_NAME'],
            'routes/api' => ['LOWER_NAME'],
            'views/index' => ['LOWER_NAME'],
            'views/master' => ['LOWER_NAME', 'STUDLY_NAME'],
            'scaffold/config' => ['STUDLY_NAME'],
            'composer' => [
                'LOWER_NAME',
                'STUDLY_NAME',
                'VENDOR',
                'AUTHOR_NAME',
                'AUTHOR_EMAIL',
                'MODULE_NAMESPACE',
            ],
            'module' => ['LOWER_NAME', 'STUDLY_NAME', 'MODULE_NAMESPACE'],
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Module Cache
    |--------------------------------------------------------------------------
    |
    | Configure module caching behavior.
    |
    */
    'cache' => [
        'enabled' => false,
        'key' => 'corbital-modules',
        'lifetime' => 60, // minutes
    ],

    /*
    |--------------------------------------------------------------------------
    | Module Events
    |--------------------------------------------------------------------------
    |
    | Configuration for module events.
    |
    */
    'events' => [
        'before_module_activation' => [],
        'after_module_activation' => [],
        'before_module_deactivation' => [],
        'after_module_deactivation' => [],
    ],

    /*
    |--------------------------------------------------------------------------
    | Module Hooks
    |--------------------------------------------------------------------------
    |
    | Configuration for module hooks and validation.
    |
    */
    'hooks' => [
        'envato_validation' => [
            'enabled' => true,
            'skip_for_core_modules' => true,
            'api_endpoint' => 'https://api.envato.com/v1/market/author/sale',
            'timeout' => 30,
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Module Validation
    |--------------------------------------------------------------------------
    |
    | Settings for module validation and security.
    |
    */
    'validation' => [
        'log_attempts' => true,
        'max_attempts_per_hour' => 10,
        'block_after_failed_attempts' => 5,
    ],
];
