<?php

return [
    'name' => 'ApiWebhookManager',
    /*
    |--------------------------------------------------------------------------
    | Module Configuration
    |--------------------------------------------------------------------------
    |
    | Here you may define the configuration options for the ApiWebhookManager module.
    |
    */

    'enabled' => env('API_ENABLED', false),

    'token' => env('API_TOKEN', null),

    'token_generated_at' => null,

    'abilities' => [
        // contact abilities
        'contacts.create',
        'contacts.read',
        'contacts.update',
        'contacts.delete',

        // status abilities
        'statuses.create',
        'statuses.read',
        'statuses.update',
        'statuses.delete',

        // source abilities
        'sources.create',
        'sources.read',
        'sources.update',
        'sources.delete',

        // template abilities
        'templates.read',

        // templatebot abilities
        'templatebots.read',
        'templatebots.delete',

        // messagebot abilities
        'messagebots.create',
        'messagebots.read',
        'messagebots.delete',

        // group abilities
        'groups.create',
        'groups.read',
        'groups.update',
        'groups.delete',

        // send message ability
        'messages.send',

    ],

    'admin_abilities' => [

        'plans.read',
    ],

    /*
    |--------------------------------------------------------------------------
    | API Rate Limiting
    |--------------------------------------------------------------------------
    |
    | Here you can configure the rate limiting for your API endpoints.
    |
    */

    'rate_limiting' => [
        'enabled' => env('API_RATE_LIMIT_ENABLED', true),
        'max_attempts' => env('API_RATE_LIMIT_MAX', 60),
        'decay_minutes' => env('API_RATE_LIMIT_DECAY', 1),
    ],

    /*
    |--------------------------------------------------------------------------
    | Webhook Secret Key for Signing
    |--------------------------------------------------------------------------
    |
    | This key is used for signing webhook payloads to ensure they haven't been
    | tampered with during transmission.
    |
    */
    'signing_secret' => env('WEBHOOK_SIGNING_SECRET', '123'),

    /*
    |--------------------------------------------------------------------------
    | Webhook Headers
    |--------------------------------------------------------------------------
    |
    | Default headers to be sent with webhook requests
    |
    */
    'headers' => [
        'User-Agent' => 'whatsmark-Webhook/1.0',
        'Content-Type' => 'application/json',
        'Accept' => 'application/json',
    ],

    /*
    |--------------------------------------------------------------------------
    | Webhook Retry Configuration
    |--------------------------------------------------------------------------
    |
    | Configure retry attempts and timeout for failed webhook deliveries
    |
    */
    'retry' => [
        'max_attempts' => 3,
        'timeout' => 30,
    ],

];
