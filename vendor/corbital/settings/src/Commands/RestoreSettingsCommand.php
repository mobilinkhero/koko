<?php

namespace Corbital\Settings\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class RestoreSettingsCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'settings:restore
                            {backup? : Backup file name to restore (if not provided, will show a list of available backups)}
                            {--groups= : Comma-separated list of groups to restore (default: all)}
                            {--tenant= : Restore settings for a specific tenant}
                            {--force : Skip confirmation}
                            {--dry-run : Simulate the restore without making changes}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Restore settings from a backup';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $backupsPath = storage_path('settings/backups');
        $backupToRestore = $this->argument('backup');

        // Check if backups directory exists
        if (! File::isDirectory($backupsPath)) {
            $this->components->error('No backups found');

            return self::FAILURE;
        }

        // Load the manifest
        $manifestPath = "{$backupsPath}/manifest.json";

        if (! File::exists($manifestPath)) {
            $this->components->error('Backup manifest not found');

            return self::FAILURE;
        }

        $manifestContent = File::get($manifestPath);
        $manifest = json_decode($manifestContent, true) ?: [];

        if (empty($manifest)) {
            $this->components->error('No backups found in manifest');

            return self::FAILURE;
        }

        // If no backup specified, show list and ask user to select
        if (! $backupToRestore) {
            $backupToRestore = $this->selectBackupFromList($manifest);

            if (! $backupToRestore) {
                $this->components->info('Restore cancelled');

                return self::SUCCESS;
            }
        }

        // Find the backup file
        $backupFilePath = "{$backupsPath}/{$backupToRestore}";

        if (! File::exists($backupFilePath)) {
            $this->components->error("Backup file not found: {$backupToRestore}");

            return self::FAILURE;
        }

        // Get backup details from manifest
        $backupInfo = null;

        foreach ($manifest as $entry) {
            if ($entry['file'] === $backupToRestore) {
                $backupInfo = $entry;
                break;
            }
        }

        if (! $backupInfo) {
            $this->components->warn("Backup found but not in manifest: {$backupToRestore}");
        } else {
            $this->components->info('Backup details:');
            $this->components->bulletList([
                'Created at: '.$backupInfo['created_at'],
                'Environment: '.$backupInfo['environment'],
                'Tenant ID: '.($backupInfo['tenant_id'] ?? 'global'),
                'Groups: '.($backupInfo['groups'] ? implode(', ', $backupInfo['groups']) : 'all'),
            ]);
        }

        // Prepare import options
        $options = [
            'file' => $backupFilePath,
        ];

        if ($this->option('groups')) {
            $options['--groups'] = $this->option('groups');
        }

        if ($this->option('tenant')) {
            $options['--tenant'] = $this->option('tenant');
        }

        if ($this->option('force')) {
            $options['--force'] = true;
        }

        if ($this->option('dry-run')) {
            $options['--dry-run'] = true;
        }

        // Confirm restore if not forced
        if (! $this->option('force') && ! $this->option('dry-run') && ! $this->components->confirm('Do you want to proceed with the restore?', true)) {
            $this->components->info('Restore cancelled');

            return self::SUCCESS;
        }

        // Create a backup of current settings before restoring
        if (! $this->option('dry-run') && $this->components->confirm('Create a backup of current settings before restoring?', true)) {
            $backupCommand = $this->findCommand('settings:backup');

            if ($backupCommand) {
                $backupOptions = [];

                if ($this->option('groups')) {
                    $backupOptions['--groups'] = $this->option('groups');
                }

                if ($this->option('tenant')) {
                    $backupOptions['--tenant'] = $this->option('tenant');
                }

                $this->components->info('Creating backup of current settings...');
                $backupResult = $this->runCommand($backupCommand, $backupOptions);

                if ($backupResult !== 0) {
                    $this->components->error('Failed to create backup of current settings');

                    if (! $this->components->confirm('Continue with restore anyway?', false)) {
                        $this->components->info('Restore cancelled');

                        return self::SUCCESS;
                    }
                }
            }
        }

        // Use the import command to restore the backup
        $importCommand = $this->findCommand('settings:import');

        if (! $importCommand) {
            $this->components->error('Import command not found');

            return self::FAILURE;
        }

        $this->components->info($this->option('dry-run') ? 'Simulating restore (dry run)...' : 'Restoring settings...');

        $restoreResult = $this->runCommand($importCommand, $options);

        if ($restoreResult !== 0) {
            $this->components->error('Restore failed');

            return self::FAILURE;
        }

        if ($this->option('dry-run')) {
            $this->components->info('Restore simulation completed successfully');
        } else {
            $this->components->info("Settings restored successfully from {$backupToRestore}");
        }

        return self::SUCCESS;
    }

    /**
     * Let the user select a backup from the list.
     */
    protected function selectBackupFromList(array $manifest): ?string
    {
        $choices = [];

        foreach ($manifest as $index => $entry) {
            $date = (new \DateTime($entry['created_at']))->format('Y-m-d H:i:s');
            $tenant = $entry['tenant_id'] ?? 'global';
            $groups = $entry['groups'] ? implode(', ', $entry['groups']) : 'all';

            $choices[$index + 1] = "{$entry['file']} ({$date}, Tenant: {$tenant}, Groups: {$groups})";
        }

        $this->components->info('Available backups:');

        foreach ($choices as $index => $choice) {
            $this->components->bulletList(["[{$index}] {$choice}"]);
        }

        $selection = $this->components->ask('Select a backup to restore (or 0 to cancel)', '1');

        if ($selection === '0' || ! isset($manifest[$selection - 1])) {
            return null;
        }

        return $manifest[$selection - 1]['file'];
    }

    /**
     * Find a command by name.
     */
    protected function findCommand(string $name)
    {
        return $this->getApplication()->find($name);
    }

    /**
     * Run a command.
     */
    protected function runCommand($command, array $arguments = [], $output = null)
    {
        return $command->run(new \Symfony\Component\Console\Input\ArrayInput($arguments), $output ?: $this->output);
    }
}
