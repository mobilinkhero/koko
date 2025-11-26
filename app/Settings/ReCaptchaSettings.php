<?php

namespace App\Settings;

use Spatie\LaravelSettings\Settings;

class ReCaptchaSettings extends Settings
{
    public ?bool $isReCaptchaEnable;

    public ?string $site_key;

    public ?string $secret_key;

    public static function group(): string
    {
        return 're-captcha';
    }
}
