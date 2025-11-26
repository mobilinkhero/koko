<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Enable Email Logging
    |--------------------------------------------------------------------------
    |
    | When enabled, all sent emails will be logged to the database.
    | This can be helpful for debugging and auditing.
    |
    */
    'enable_logging' => env('EMAIL_LOGGING_ENABLED', true),

    /*
    |--------------------------------------------------------------------------
    | Email Logs Retention Period
    |--------------------------------------------------------------------------
    |
    | The number of days to keep email logs in the database.
    | Set to null to keep logs indefinitely.
    |
    */
    'logs_retention_days' => env('EMAIL_LOGS_RETENTION_DAYS', 30),

    /*
    |--------------------------------------------------------------------------
    | Email Rate Limiting
    |--------------------------------------------------------------------------
    |
    | Configure rate limiting for email sending.
    | max_per_minute: Maximum emails per minute (null to disable)
    | max_per_hour: Maximum emails per hour (null to disable)
    | max_per_day: Maximum emails per day (null to disable)
    |
    */
    'rate_limiting' => [
        'enabled' => env('EMAIL_RATE_LIMITING_ENABLED', false),
        'max_per_minute' => env('EMAIL_MAX_PER_MINUTE', 10),
        'max_per_hour' => env('EMAIL_MAX_PER_HOUR', 100),
        'max_per_day' => env('EMAIL_MAX_PER_DAY', 1000),
    ],

    /*
    |--------------------------------------------------------------------------
    | Throw Exceptions
    |--------------------------------------------------------------------------
    |
    | When enabled, exceptions encountered during email sending will be thrown.
    | When disabled, emails will fail silently (but still be logged if logging is enabled).
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
    | Queue Configuration
    |--------------------------------------------------------------------------
    |
    | Configure the default queue for email sending.
    |
    */
    'queue' => [
        'connection' => env('EMAIL_QUEUE_CONNECTION', env('QUEUE_CONNECTION', 'sync')),
        'default_queue' => env('EMAIL_QUEUE_NAME', 'emails'),
        'enabled' => env('EMAIL_QUEUE_ENABLED', false),
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
];
