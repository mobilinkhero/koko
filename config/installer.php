<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Minimum PHP Version
    |--------------------------------------------------------------------------
    |
    | This value determines the minimum PHP version required to run the
    | application. This check is performed during the installation process.
    |
    */
    'minPhpVersion' => '8.3',

    /*
    |--------------------------------------------------------------------------
    | Installation Requirements
    |--------------------------------------------------------------------------
    |
    | This section defines the PHP extensions, functions and other requirements
    | needed to properly run the application.
    |
    */
    'requirements' => [
        'php' => [
            'bcmath',
            'ctype',
            'fileinfo',
            'json',
            'mbstring',
            'openssl',
            'pdo',
            'tokenizer',
            'xml',
            'curl',
        ],
        'functions' => [
            'symlink',
            'file_get_contents',
            'file_put_contents',
        ],
        'recommended' => [
            'php' => [
                'zip',
                'gd',
                'intl',
            ],
            'functions' => [],
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Folder Permissions
    |--------------------------------------------------------------------------
    |
    | This value contains list of folders that need to be writable during the
    | installation process. These folders require write permissions for the
    | web server user.
    |
    */
    'permissions' => [
        'storage/app' => '0755',
        'storage/framework' => '0755',
        'storage/logs' => '0755',
        'bootstrap/cache' => '0755',
    ],

    /*
    |--------------------------------------------------------------------------
    | Installation Routes
    |--------------------------------------------------------------------------
    |
    | This section defines routing configuration for the installer.
    |
    */
    'routes' => [
        'prefix' => 'install',
        'middleware' => ['web'],
        'as' => 'install.',
    ],

    /*
    |--------------------------------------------------------------------------
    | Admin User Setup
    |--------------------------------------------------------------------------
    |
    | Configure the admin user creation during installation
    |
    */
    'admin_setup' => [
        'model' => '\App\Models\User',
        'fields' => [
            'firstname' => true,
            'lastname' => true,
            'email' => true,
            'password' => true,
            'timezone' => true,
        ],
        'role_field' => 'role_id',
        'admin_role_value' => null,
        'admin_flag_field' => 'is_admin',
        'admin_flag_value' => 1,
        'verified_field' => 'email_verified_at',
        'verified_field_value' => now(),
    ],

    /*
    |--------------------------------------------------------------------------
    | Installation Completed Lock File
    |--------------------------------------------------------------------------
    |
    | This is the file that indicates the application is installed.
    |
    */
    'installed_file' => '.installed',

    /*
    |--------------------------------------------------------------------------
    | Installation Storage Path
    |--------------------------------------------------------------------------
    |
    | Path where the installation marker file will be stored.
    |
    */
    'storage_path' => 'storage',

    /*
    |--------------------------------------------------------------------------
    | Installation Route
    |--------------------------------------------------------------------------
    |
    | The base route for the installation wizard
    |
    */
    'install_route' => 'install',

    /*
    |--------------------------------------------------------------------------
    | License Verification
    |--------------------------------------------------------------------------
    |
    | Configuration for the Envato license verification step
    |
    */
    'license_verification' => [
        'product_id' => '58714968',
        'api_endpoint' => 'aHR0cHM6Ly9wYXNzdGhlY29kZS5jb3JiaXRhbHRlY2guZGV2L2FwaS92Mw==',
        'required' => true,
        'current_version' => '1.4.0',
        'verify_type' => 'envato',
        'root_path' => storage_path('app/public/updates/'),
        'module_name' => 'WhatsMarkSaaS - WhatsApp Marketing & Automation SaaS Platform with Bots, Chats, Bulk Sender & AI',
        'support_url' => 'aHR0cHM6Ly9zdXBwb3J0LmNvcmJpdGFsdGVjaC5kZXYvbG9naW4',
        'renew_support_url' => 'https://codecanyon.net/item/whatsmarksaas-whatsapp-marketing-automation-saas-platform-with-bots-chats-bulk-sender-ai/58714968',
    ],
];
