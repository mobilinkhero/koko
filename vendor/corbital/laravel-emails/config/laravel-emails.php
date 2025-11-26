<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Throw Exceptions
    |--------------------------------------------------------------------------
    |
    | When enabled, exceptions encountered during email sending will be thrown.
    | When disabled, emails will fail silently.
    |
    */
    'throw_exceptions' => env('EMAIL_THROW_EXCEPTIONS', true),

    /*
    |--------------------------------------------------------------------------
    | Admin UI Route
    |--------------------------------------------------------------------------
    |
    | Configure the admin UI routes.
    |
    */
    'admin_route' => [
        'enabled' => env('EMAIL_ADMIN_ROUTE_ENABLED', true),
        'prefix' => env('EMAIL_ADMIN_ROUTE_PREFIX', 'admin/emails'),
        'middleware' => ['web', 'auth'],
    ],

    /*
    |--------------------------------------------------------------------------
    | Editor Configuration
    |--------------------------------------------------------------------------
    |
    | Configure the template editor options.
    |
    */
    'editor' => [
        'height' => 500,
        'theme' => 'light',
    ],

    /*
    |--------------------------------------------------------------------------
    | Template Variables
    |--------------------------------------------------------------------------
    |
    | Default variables that are available in all email templates.
    |
    */
    'default_variables' => [
        'app_name' => config('app.name'),
        'app_url' => config('app.url'),
    ],
];
