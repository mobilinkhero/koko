<?php

namespace App\Settings;

use Spatie\LaravelSettings\Settings;

class AnnouncementSettings extends Settings
{
    public ?bool $isEnable;

    public ?string $message;

    public ?string $link;

    public ?string $link_text;

    public ?string $background_color;

    public ?string $link_text_color;

    public ?string $message_color;

    public static function group(): string
    {
        return 'announcement';
    }
}
