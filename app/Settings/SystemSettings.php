<?php

namespace App\Settings;

use Spatie\LaravelSettings\Settings;

class SystemSettings extends Settings
{
    public ?string $site_name;

    public ?string $site_description;

    public ?string $timezone;

    public ?string $date_format;

    public ?string $time_format;

    public ?string $active_language;

    public ?string $company_name;

    public ?string $company_country_id;

    public ?string $company_email;

    public ?string $company_city;

    public ?string $company_state;

    public ?string $company_zip_code;

    public ?string $company_address;

    public ?array $default_country_code;

    public ?int $tables_pagination_limit;

    public ?int $max_queue_jobs;

    public ?bool $is_enable_landing_page;

    public static function group(): string
    {
        return 'system';
    }
}
