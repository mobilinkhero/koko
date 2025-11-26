<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class MonitorCronStatus extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'cron:monitor {status=start} {--reset} {--check}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Monitor cron job execution status and check current status';

    /**
     * Status constants
     */
    private const STATUS_RUNNING = 'running';

    private const STATUS_COMPLETED = 'completed';

    private const STATUS_FAILED = 'failed';

    private const STATUS_UNKNOWN = 'unknown';

    /**
     * Setting keys
     */
    private const SETTING_STATUS = 'cron-job.status';

    private const SETTING_START_TIME = 'cron-job.job_start_time';

    private const SETTING_LAST_RUN = 'cron-job.last_cron_run';

    private const SETTING_EXECUTION_TIME = 'cron-job.last_execution_time';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        try {
            $status = $this->argument('status');
            $reset = $this->option('reset');
            $check = $this->option('check');

            if ($check) {
                $this->checkCronStatus();

                return Command::SUCCESS;
            }

            if ($reset) {
                $this->updateStatus(self::STATUS_UNKNOWN, 'reset');
                $this->info('Cron status has been reset.');

                return Command::SUCCESS;
            }

            switch ($status) {
                case 'start':
                    $this->markCronAsStarted();
                    $this->info('Cron status set to running.');
                    break;
                case 'end':
                    $this->updateStatus(self::STATUS_COMPLETED, 'completed');
                    $this->info('Cron status set to completed.');
                    break;
                case 'fail':
                    $this->updateStatus(self::STATUS_FAILED, 'failed');
                    $this->info('Cron status set to failed.');
                    break;
                default:
                    $this->error('Invalid status. Use start, end, or fail.');

                    return Command::FAILURE;
            }

            return Command::SUCCESS;
        } catch (\Exception $e) {
            $this->error('Command failed: '.$e->getMessage());

            return Command::FAILURE;
        }
    }

    /**
     * Check and display the current cron status
     */
    private function checkCronStatus(): void
    {
        try {
            // Get status using get_setting function
            $status = get_setting(self::SETTING_STATUS, self::STATUS_UNKNOWN);
            $lastRun = get_setting(self::SETTING_LAST_RUN, null);
            $executionTime = get_setting(self::SETTING_EXECUTION_TIME, 0);
            $startTime = get_setting(self::SETTING_START_TIME, 0);
            $currentTime = now()->timestamp;

            $this->info('====== CRON STATUS REPORT ======');
            $this->info('Current Status: '.$status);
            $this->info('Current Time: '.date('Y-m-d H:i:s', $currentTime).' ('.$currentTime.')');

            if ($lastRun) {
                $lastRunTime = date('Y-m-d H:i:s', (int) $lastRun);
                $this->info("Last Completed Run: $lastRunTime (Timestamp: $lastRun)");
                $this->info('Time Since Last Completion: '.($currentTime - (int) $lastRun).' seconds');
            } else {
                $this->info('Last Completed Run: Never');
            }

            if ($status === self::STATUS_RUNNING && $startTime > 0) {
                $runningFor = $currentTime - $startTime;
                $this->info('Current Run Started: '.date('Y-m-d H:i:s', (int) $startTime)." (Timestamp: $startTime)");
                $this->info("Running for: {$runningFor} seconds");

                if ($runningFor > 300) { // 5 minutes
                    $this->warn('WARNING: Cron has been running for more than 5 minutes!');
                }
            }

            $this->info('Last Execution Time: '.($executionTime > 0 ? $executionTime.' seconds' : 'Not available'));
        } catch (\Exception $e) {
            $this->error('Error retrieving cron status: '.$e->getMessage());
        }
    }

    /**
     * Mark cron as started.
     */
    private function markCronAsStarted(): void
    {
        try {
            // First check if a previous run is still marked as "running"
            $status = get_setting(self::SETTING_STATUS, self::STATUS_UNKNOWN);
            $startTime = get_setting(self::SETTING_START_TIME, 0);
            $currentTime = now()->timestamp;

            // If status is "running" and it started more than 10 minutes ago, we assume it failed
            if ($status === self::STATUS_RUNNING && $startTime > 0 && ($currentTime - $startTime > 600)) {
                $this->warn("Found a stale 'running' status from ".date('Y-m-d H:i:s', $startTime));
                $this->warn('Assuming previous cron run failed and resetting status before starting new run');

                // Update both the settings cache and model
                set_setting(self::SETTING_STATUS, self::STATUS_FAILED);

                $cronSettings = app(\App\Settings\CronJobSettings::class);
                $cronSettings->status = self::STATUS_FAILED;
                $cronSettings->save();
            }

            $this->info('Setting cron status to running...');

            // Update the settings cache
            set_setting(self::SETTING_STATUS, self::STATUS_RUNNING);
            set_setting(self::SETTING_START_TIME, $currentTime);

            // Update the settings model
            $cronSettings = app(\App\Settings\CronJobSettings::class);
            $cronSettings->status = self::STATUS_RUNNING;
            $cronSettings->job_start_time = $currentTime;
            $cronSettings->save();

            $this->info('Status set to: running');
            $this->info('Start time set to: '.$currentTime);
        } catch (\Exception $e) {
            $this->error('Failed to update cron status: '.$e->getMessage());
        }
    }

    /**
     * Update cron status (for end or fail)
     *
     * @param  string  $status  The status to set
     * @param  string  $action  The action being performed (for logging)
     */
    private function updateStatus(string $status, string $action): void
    {
        try {
            $this->info("Setting cron status to $status...");
            $settings = get_batch_settings([self::SETTING_START_TIME]);
            $startTime = (int) $settings[self::SETTING_START_TIME] ?? 0;
            $endTime = now()->timestamp;
            $executionTime = $startTime > 0 ? ($endTime - $startTime) : 0;

            // First update the spatie settings model
            $cronSettings = app(\App\Settings\CronJobSettings::class);
            $cronSettings->status = $status;

            if ($status !== self::STATUS_UNKNOWN) {
                $cronSettings->last_cron_run = (string) $endTime;
                $cronSettings->last_execution_time = $executionTime;
            }

            $cronSettings->save();

            // Then update the cached settings
            set_setting(self::SETTING_STATUS, $status);

            if ($status !== self::STATUS_UNKNOWN) {
                set_setting(self::SETTING_LAST_RUN, $endTime);
                set_setting(self::SETTING_EXECUTION_TIME, $executionTime);
                $this->info('Last run set to: '.$endTime);
                $this->info('Execution time set to: '.$executionTime.' seconds');
            }

            $this->info("Status set to: $status");
        } catch (\Exception $e) {
            $this->error('Failed to update cron job status: '.$e->getMessage());
        }
    }
}
