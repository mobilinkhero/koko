<?php

namespace App\Settings;

use Spatie\LaravelSettings\Settings;

class PusherSettings extends Settings
{
    public ?string $app_id;

    public ?string $app_key;

    public ?string $app_secret;

    public ?string $cluster;

    public ?bool $real_time_notify;

    public ?bool $desk_notify;

    public $dismiss_desk_notification;

    public static function group(): string
    {
        return 'pusher';
    }

    public function toArray(): array
    {
        return [
            'driver' => 'pusher',
            'key' => $this->app_key ?? null,
            'secret' => $this->app_secret ?? null,
            'app_id' => $this->app_id ?? null,
            'options' => [
                'cluster' => $this->cluster ?? 'ap2',
                'host' => 'api-'.$this->cluster.'.pusher.com',
                'port' => 443,
                'scheme' => 'https',
                'encrypted' => true,
                'useTLS' => true,
            ],
        ];
    }
}
