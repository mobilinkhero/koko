<?php

use Spatie\LaravelSettings\Migrations\SettingsMigration;

return new class extends SettingsMigration
{
    public function up(): void
    {
        if (! $this->migrator->exists('cron-job.last_cron_run')) {
            $this->migrator->add('cron-job.last_cron_run', false);
        }
        if (! $this->migrator->exists('cron-job.status')) {
            $this->migrator->add('cron-job.status', 'unknown');
        }
        if (! $this->migrator->exists('cron-job.last_cron_stats')) {
            $this->migrator->add('cron-job.last_cron_stats', '{}');
        }
        if (! $this->migrator->exists('cron-job.last_execution_time')) {
            $this->migrator->add('cron-job.last_execution_time', 0);
        }
        if (! $this->migrator->exists('cron-job.job_start_time')) {
            $this->migrator->add('cron-job.job_start_time', 0);
        }
    }

    public function down(): void
    {
        if ($this->migrator->exists('cron-job.last_cron_run')) {
            $this->migrator->delete('cron-job.last_cron_run');
        }
        if ($this->migrator->exists('cron-job.status')) {
            $this->migrator->delete('cron-job.status');
        }
        if ($this->migrator->exists('cron-job.last_cron_stats')) {
            $this->migrator->delete('cron-job.last_cron_stats');
        }
        if ($this->migrator->exists('cron-job.last_execution_time')) {
            $this->migrator->delete('cron-job.last_execution_time');
        }
        if ($this->migrator->exists('cron-job.job_start_time')) {
            $this->migrator->delete('cron-job.job_start_time');
        }
    }
};
