<?php

namespace Corbital\Settings\Commands;

use Corbital\Settings\Facades\Settings;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class ExportSettingsCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'settings:export
                            {--groups= : Comma-separated list of groups to export (default: all)}
                            {--file= : Path to output file (default: settings_export_YYYY-MM-DD.json)}
                            {--pretty : Pretty print the JSON output}
                            {--tenant= : Export settings for a specific tenant}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Export settings to a JSON file';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->components->task('Refreshing settings cache', function () {
            return app('settings')->refreshCache();
        });

        $groups = $this->option('groups');
        $outputFile = $this->option('file');
        $prettyPrint = $this->option('pretty');
        $tenantId = $this->option('tenant');

        // Use a timestamp-based filename if not specified
        if (! $outputFile) {
            $timestamp = now()->format('Y-m-d');
            $outputFile = storage_path("settings_export_{$timestamp}.json");
        }

        // Get the settings manager (tenant-specific or global)
        $manager = $tenantId ? Settings::forTenant($tenantId) : app('settings');

        // Get the settings to export
        $settingsToExport = [];

        if ($groups) {
            $groupsArray = explode(',', $groups);

            foreach ($groupsArray as $group) {
                $this->components->task("Exporting {$group} settings", function () use ($manager, $group, &$settingsToExport) {
                    try {
                        $settingsToExport[$group] = $this->extractSettings($manager->group($group));

                        return true;
                    } catch (\Throwable $e) {
                        $this->components->error("Failed to export {$group} settings: {$e->getMessage()}");

                        return false;
                    }
                });
            }
        } else {
            $allSettings = $manager->all();

            foreach ($allSettings as $group => $settings) {
                $this->components->task("Exporting {$group} settings", function () use ($group, $settings, &$settingsToExport) {
                    $settingsToExport[$group] = $this->extractSettings($settings);

                    return true;
                });
            }
        }

        // Add metadata
        $settingsToExport['_metadata'] = [
            'exported_at' => now()->toIso8601String(),
            'tenant_id' => $tenantId,
            'environment' => app()->environment(),
        ];

        // Encode to JSON
        $jsonOptions = $prettyPrint ? JSON_PRETTY_PRINT : 0;
        $jsonContent = json_encode($settingsToExport, $jsonOptions | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);

        // Save to file
        $this->components->task("Writing to file {$outputFile}", function () use ($outputFile, $jsonContent) {
            $directory = dirname($outputFile);

            if (! File::isDirectory($directory)) {
                File::makeDirectory($directory, 0755, true);
            }

            File::put($outputFile, $jsonContent);

            return true;
        });

        $this->components->info("Settings exported successfully to {$outputFile}");

        return self::SUCCESS;
    }

    /**
     * Extract settings from a settings object.
     */
    protected function extractSettings(object $settings): array
    {
        $result = [];

        foreach (get_object_vars($settings) as $key => $value) {
            // Skip internal properties that start with underscore
            if (! str_starts_with($key, '_')) {
                $result[$key] = $value;
            }
        }

        return $result;
    }
}
