<?php

namespace App\Settings;

use Spatie\LaravelSettings\Settings;

class TenantSettings extends Settings
{
    public ?bool $isRegistrationEnabled;

    public ?bool $isVerificationEnabled;

    public ?bool $isEmailConfirmationEnabled;

    public ?bool $isEnableWelcomeEmail;

    public ?string $set_default_tenant_language;

    public static function group(): string
    {
        return 'tenant';
    }
}
