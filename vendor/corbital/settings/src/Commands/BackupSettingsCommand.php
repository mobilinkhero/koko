<?php

namespace Corbital\Settings\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class BackupSettingsCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'settings:backup
                            {--groups= : Comma-separated list of groups to backup (default: all)}
                            {--tenant= : Backup settings for a specific tenant}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a backup of current settings';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->components->task('Refreshing settings cache', function () {
            return app('settings')->refreshCache();
        });
        $groups = $this->option('groups');
        $tenantId = $this->option('tenant');

        // Create backups directory if it doesn't exist
        $backupsPath = storage_path('settings/backups');

        if (! File::isDirectory($backupsPath)) {
            File::makeDirectory($backupsPath, 0755, true);
        }

        // Generate a timestamp for the backup
        $timestamp = now()->format('Y-m-d_H-i-s');
        $tenantSuffix = $tenantId ? "_tenant_{$tenantId}" : '';
        $backupFileName = "settings_backup_{$timestamp}{$tenantSuffix}.json";
        $backupFilePath = "{$backupsPath}/{$backupFileName}";

        // Use the export command to create the backup
        $exportCommand = $this->findCommand('settings:export');

        if (! $exportCommand) {
            $this->components->error('Export command not found');

            return self::FAILURE;
        }

        $options = [
            '--file' => $backupFilePath,
            '--pretty' => true,
        ];

        if ($groups) {
            $options['--groups'] = $groups;
        }

        if ($tenantId) {
            $options['--tenant'] = $tenantId;
        }

        $this->components->info('Creating settings backup...');

        $exportResult = $this->runCommand($exportCommand, $options);

        if ($exportResult !== 0) {
            $this->components->error('Backup failed');

            return self::FAILURE;
        }

        // Create a manifest file to track backups
        $this->updateBackupManifest($backupFileName, $tenantId, $groups);

        $this->components->info("Settings backup created: {$backupFileName}");

        return self::SUCCESS;
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

    /**
     * Update the backup manifest file.
     */
    protected function updateBackupManifest(string $backupFileName, ?string $tenantId, ?string $groups): void
    {
        $manifestPath = storage_path('settings/backups/manifest.json');

        $manifest = [];

        if (File::exists($manifestPath)) {
            $manifestContent = File::get($manifestPath);
            $manifest = json_decode($manifestContent, true) ?: [];
        }

        $manifest[] = [
            'file' => $backupFileName,
            'created_at' => now()->toIso8601String(),
            'tenant_id' => $tenantId,
            'groups' => $groups ? explode(',', $groups) : null,
            'environment' => app()->environment(),
        ];

        // Keep the manifest sorted by creation date (newest first)
        usort($manifest, function ($a, $b) {
            return strtotime($b['created_at']) - strtotime($a['created_at']);
        });

        // Save the manifest
        File::put($manifestPath, json_encode($manifest, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE));
    }
}
