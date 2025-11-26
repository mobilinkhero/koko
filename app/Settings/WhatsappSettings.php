<?php

namespace App\Settings;

use Spatie\LaravelSettings\Settings;

class WhatsappSettings extends Settings
{
    public ?string $wm_fb_app_id;

    public ?string $wm_fb_app_secret;

    public ?string $wm_fb_config_id;

    public ?string $webhook_verify_token;

    public ?string $is_webhook_connected;

    public string $api_version;

    public string $daily_limit;

    public string $queue;

    public string $paths;

    public string $logging;

    public static function group(): string
    {
        return 'whatsapp';
    }
}
