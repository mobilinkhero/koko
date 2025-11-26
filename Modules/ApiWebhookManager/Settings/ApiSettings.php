<?php

namespace Modules\ApiWebhookManager\Settings;

use Spatie\LaravelSettings\Settings;

class ApiSettings extends Settings
{
    public bool $enable_api = false;

    public string $api_token = '';

    public array $abilities = [];

    public string $last_used_at = '';

    public string $token_generated_at = '';

    public static function group(): string
    {
        return 'api';
    }
}
