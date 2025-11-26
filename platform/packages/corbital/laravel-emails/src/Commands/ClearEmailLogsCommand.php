<?php

namespace Corbital\LaravelEmails\Commands;

use Corbital\LaravelEmails\Models\SimplifiedEmailLog;
use Illuminate\Console\Command;

class ClearEmailLogsCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'email:clear-logs
                            {--days=30 : Clear logs older than this number of days}
                            {--all : Clear all logs}
                            {--failed : Clear only failed email logs}
                            {--force : Force the operation without confirmation}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Clear old email logs from the database';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        if ($this->option('all')) {
            if (! $this->option('force') && ! $this->confirm('Are you sure you want to delete all email logs? This action cannot be undone.', false)) {
                $this->info('Operation cancelled.');

                return 0;
            }

            $count = SimplifiedEmailLog::count();
            SimplifiedEmailLog::truncate();
            $this->info("All email logs cleared successfully. {$count} records deleted.");

            return 0;
        }

        // Get the number of days
        $days = $this->option('days');
        $this->info("Using retention period: {$days} days");

        if (! is_numeric($days) || $days <= 0) {
            $this->error('The number of days must be a positive number.');

            return 1;
        }

        $query = SimplifiedEmailLog::where('created_at', '<', now()->subDays($days));

        if ($this->option('failed')) {
            $query->where('status', 'failed');
            $this->info("Clearing failed email logs older than {$days} days...");
        } else {
            $this->info("Clearing all email logs older than {$days} days...");
        }

        $count = $query->count();

        if ($count === 0) {
            $this->info('No matching logs found to delete.');

            return 0;
        }

        if (! $this->option('force') && ! $this->confirm("Are you sure you want to delete {$count} email logs?", true)) {
            $this->info('Operation cancelled.');

            return 0;
        }

        $query->delete();

        $this->info("Email logs cleared successfully. {$count} records deleted.");

        return 0;
    }
}
