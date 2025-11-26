<?php

namespace App\Settings;

use Spatie\LaravelSettings\Settings;

class WhatsMarkSettings extends Settings
{
    public ?string $wm_version;

    public ?string $wm_verification_id;

    public ?string $wm_verification_token;

    public ?string $wm_last_verification;

    public ?string $wm_support_until;

    public ?string $whatsmark_latest_version;

    public ?bool $wm_validate;

    public static function group(): string
    {
        return 'whats-mark';
    }
}
