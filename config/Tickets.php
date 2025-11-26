<?php

return [
    'name' => 'Tickets',

    /*
    |--------------------------------------------------------------------------
    | Module Configuration
    |--------------------------------------------------------------------------
    |
    | Here you may define the configuration options for the Tickets module.
    |
    */

    /*
    |--------------------------------------------------------------------------
    | Ticket Configuration
    |--------------------------------------------------------------------------
    |
    | Configuration options for ticket management system.
    |
    */
    'ticket' => [
        'id_prefix' => env('TICKET_ID_PREFIX', 'TKT'),
        'id_length' => env('TICKET_ID_LENGTH', 8),
        'default_priority' => env('TICKET_DEFAULT_PRIORITY', 'medium'),
        'default_status' => env('TICKET_DEFAULT_STATUS', 'open'),
        'auto_close_after_days' => env('TICKET_AUTO_CLOSE_DAYS', null), // null = disabled
        'max_attachments' => env('TICKET_MAX_ATTACHMENTS', 5),
        'max_attachment_size' => env('TICKET_MAX_ATTACHMENT_SIZE', 10240), // KB
        'allowed_file_types' => [
            'jpg', 'jpeg', 'png', 'gif', 'pdf', 'doc', 'docx', 'txt', 'zip',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Ticket Statuses and Priorities
    |--------------------------------------------------------------------------
    |
    | Define available ticket statuses and priorities.
    |
    */
    'statuses' => [
        'open' => 'Open',
        'pending' => 'Pending',
        'answered' => 'Answered',
        'closed' => 'Closed',
        'on_hold' => 'On Hold',
    ],

    'priorities' => [
        'low' => 'Low',
        'medium' => 'Medium',
        'high' => 'High',
    ],

    /*
    |--------------------------------------------------------------------------
    | Email Notifications
    |--------------------------------------------------------------------------
    |
    | Configure email notifications for ticket events.
    |
    */
    'notifications' => [
        'enabled' => env('TICKET_NOTIFICATIONS_ENABLED', true),
        'admin_email' => env('TICKET_ADMIN_EMAIL', null),
        'send_to_client' => [
            'ticket_created' => true,
            'ticket_updated' => true,
            'ticket_closed' => true,
            'reply_received' => true,
        ],
        'send_to_admin' => [
            'ticket_created' => true,
            'reply_received' => true,
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Pagination Settings
    |--------------------------------------------------------------------------
    |
    | Configure pagination for ticket lists.
    |
    */
    'pagination' => [
        'per_page' => env('TICKET_PER_PAGE', 15),
        'admin_per_page' => env('TICKET_ADMIN_PER_PAGE', 20),
    ],

    /*
    |--------------------------------------------------------------------------
    | Storage Configuration
    |--------------------------------------------------------------------------
    |
    | Configure file storage for ticket attachments.
    |
    */
    'storage' => [
        'disk' => env('TICKET_STORAGE_DISK', 'public'),
        'path' => env('TICKET_STORAGE_PATH', 'tickets'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Security Settings
    |--------------------------------------------------------------------------
    |
    | Security-related configurations for the ticket system.
    |
    */
    'security' => [
        'sanitize_content' => env('TICKET_SANITIZE_CONTENT', true),
        'allow_html' => env('TICKET_ALLOW_HTML', false),
        'max_body_length' => env('TICKET_MAX_BODY_LENGTH', 10000),
    ],

    /*
    |--------------------------------------------------------------------------
    | Cache Settings
    |--------------------------------------------------------------------------
    |
    | Configure caching for ticket data.
    |
    */
    'cache' => [
        'enabled' => env('TICKET_CACHE_ENABLED', true),
        'ttl' => env('TICKET_CACHE_TTL', 3600), // seconds
        'prefix' => 'tickets_',
    ],
];
