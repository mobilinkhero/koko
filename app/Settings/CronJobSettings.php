<?php

namespace App\Settings;

use Spatie\LaravelSettings\Settings;

class CronJobSettings extends Settings
{
    public string $last_cron_run = '';

    public string $status = 'unknown';

    public string $last_cron_stats = '{}';

    public int $last_execution_time = 0;

    public int $job_start_time;

    public static function group(): string
    {
        return 'cron-job';
    }
}
