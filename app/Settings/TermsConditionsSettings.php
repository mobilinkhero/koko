<?php

namespace App\Settings;

use Spatie\LaravelSettings\Settings;

class TermsConditionsSettings extends Settings
{
    public ?string $title;

    public ?string $content;

    public $updated_at;

    public static function group(): string
    {
        return 'terms-conditions';
    }
}
