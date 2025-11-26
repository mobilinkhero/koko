<?php

namespace App\Settings;

use Spatie\LaravelSettings\Settings;

class EmailSettings extends Settings
{
    public ?string $mailer;

    public $smtp_port;

    public ?string $smtp_username;

    public ?string $smtp_password;

    public ?string $smtp_encryption;

    public ?string $sender_name;

    public ?string $sender_email;

    public ?string $smtp_host;

    public static function group(): string
    {
        return 'email';
    }
}
