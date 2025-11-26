<?php

namespace App\Providers;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Support\ServiceProvider;

class SchedulingServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        $this->app->booted(
            function () {
                $schedule = $this->app->make(Schedule::class);
                $settings = get_batch_settings(['system.timezone']);
                $timezone = $settings['system.timezone'] ?? config('app.timezone');

                do_action('before_scheduling_tasks_registered', $schedule, $timezone);

                // Messaging tasks
                $this->registerMessagingTasks($schedule, $timezone);

                // System maintenance tasks
                $this->registerSystemTasks($schedule, $timezone);

                // Subscription tasks
                $this->registerSubscriptionTasks($schedule, $timezone);

                // License validation cronjob COMPLETELY REMOVED - NO EXTERNAL VALIDATION

                do_action('after_scheduling_tasks_registered', $schedule, $timezone);
            }
        );
    }

    /**
     * Register messaging related tasks
     */
    private function registerMessagingTasks(Schedule $schedule, string $timezone): void
    {
        // Add a global status update for the entire scheduler
        $schedule->command('cron:monitor start')
            ->everyMinute()
            ->name('scheduler-start-monitor')
            ->withoutOverlapping();

        // Run every minute to check for campaigns that need to be sent
        $schedule->command('campaigns:process-scheduled')
            ->everyMinute()
            ->timezone($timezone)
            ->withoutOverlapping();
    }

    /**
     * Register system maintenance tasks
     */
    private function registerSystemTasks(Schedule $schedule, string $timezone): void
    {
        // Run chat history cleanup daily at midnight
        $schedule->command('whatsapp:clear-chat-history')
            ->everyMinute()
            ->timezone($timezone)
            ->withoutOverlapping();

        // Clean up deleted tenants data when their subscriptions expire
        $schedule->command('tenants:cleanup-deleted')
            ->everyMinute()
            ->timezone($timezone)
            ->withoutOverlapping();

        // Queue worker for processing background tasks
        $maxJobs = get_settings_by_group('system')->max_queue_jobs ?? 100;
        $schedule->command("queue:work --queue=whatsapp-messages,default --stop-when-empty --sleep=3 --tries=3 --timeout=60 --backoff=5 --max-time=3600 --max-jobs={$maxJobs}")
            ->withoutOverlapping()
            ->everyMinute();
    }

    /**
     * Register subscription related tasks
     */
    private function registerSubscriptionTasks(Schedule $schedule, string $timezone): void
    {
        // 1. Process all subscription renewals at once (recommended approach)
        $schedule->command('subscriptions:process-renewals')
            ->everyMinute()
            ->timezone($timezone)
            ->withoutOverlapping();

        // 2. Send renewal reminders to users with expiring subscriptions
        $schedule->command('subscriptions:send-renewal-reminders')
            ->everyMinute()
            ->timezone($timezone)
            ->withoutOverlapping();
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        //
    }
}