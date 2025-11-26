<?php

namespace Corbital\Settings\Commands;

use Corbital\Settings\Exceptions\SettingsException;
use Corbital\Settings\Facades\Settings;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class ImportSettingsCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'settings:import
                            {file : Path to the JSON file containing settings}
                            {--groups= : Comma-separated list of groups to import (default: all)}
                            {--tenant= : Import settings for a specific tenant}
                            {--force : Skip validation and force import}
                            {--dry-run : Simulate the import without making changes}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Import settings from a JSON file';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $filePath = $this->argument('file');
        $groups = $this->option('groups');
        $tenantId = $this->option('tenant');
        $force = $this->option('force');
        $dryRun = $this->option('dry-run');

        // Check if the file exists
        if (! File::exists($filePath)) {
            $this->components->error("File does not exist: {$filePath}");

            return self::FAILURE;
        }

        // Read and decode the file
        $jsonContent = File::get($filePath);
        $settings = json_decode($jsonContent, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            $this->components->error('Invalid JSON file: '.json_last_error_msg());

            return self::FAILURE;
        }

        // Get the settings manager (tenant-specific or global)
        $manager = $tenantId ? Settings::forTenant($tenantId) : app('settings');

        // Filter groups if specified
        $groupsToImport = [];

        if ($groups) {
            $groupsArray = explode(',', $groups);

            foreach ($groupsArray as $group) {
                if (isset($settings[$group])) {
                    $groupsToImport[$group] = $settings[$group];
                } else {
                    $this->components->warn("Group '{$group}' not found in the import file");
                }
            }
        } else {
            // Import all groups except metadata
            foreach ($settings as $group => $values) {
                if ($group !== '_metadata') {
                    $groupsToImport[$group] = $values;
                }
            }
        }

        // Display import summary
        $this->components->info('Settings to import:');

        foreach ($groupsToImport as $group => $values) {
            $this->components->bulletList([
                "Group: {$group}",
                'Settings: '.count($values),
            ]);
        }

        // Confirm import if not forced
        if (! $force && ! $dryRun && ! $this->components->confirm('Do you want to proceed with the import?', true)) {
            $this->components->info('Import cancelled');

            return self::SUCCESS;
        }

        // Perform the import
        $this->components->info($dryRun ? 'Simulating import (dry run)...' : 'Importing settings...');

        $importedGroups = 0;
        $failedGroups = 0;

        foreach ($groupsToImport as $group => $values) {
            $result = $this->components->task("Importing {$group} settings", function () use ($manager, $group, $values, $dryRun, $force) {
                try {
                    // Check if the group exists
                    try {
                        $settingsObj = $manager->group($group);
                    } catch (SettingsException $e) {
                        $this->components->error("Group '{$group}' does not exist");

                        return false;
                    }

                    // Validate values if not forced
                    if (! $force) {
                        try {
                            $this->validateSettings($group, $values);
                        } catch (\Throwable $e) {
                            $this->components->error("Validation failed for {$group}: {$e->getMessage()}");

                            return false;
                        }
                    }

                    // Actually import if not a dry run
                    if (! $dryRun) {
                        $manager->setBatch($group, $values);
                    }

                    return true;
                } catch (\Throwable $e) {
                    $this->components->error("Failed to import {$group} settings: {$e->getMessage()}");

                    return false;
                }
            });

            if ($result) {
                $importedGroups++;
            } else {
                $failedGroups++;
            }
        }

        if ($dryRun) {
            $this->components->info("Dry run completed. {$importedGroups} groups would be imported successfully.");
        } else {
            $this->components->info("Import completed. {$importedGroups} groups imported successfully.");
        }

        if ($failedGroups > 0) {
            $this->components->warn("{$failedGroups} groups failed to import.");

            return self::FAILURE;
        }

        return self::SUCCESS;
    }

    /**
     * Validate settings before importing.
     */
    protected function validateSettings(string $group, array $values): bool
    {
        $settingsClasses = app('settings')->getSettingsClasses();

        if (! isset($settingsClasses[$group])) {
            throw new SettingsException("Settings class for group '{$group}' not found.");
        }

        $class = $settingsClasses[$group];

        try {
            $settingsObj = app($class);
        } catch (\Throwable $e) {
            throw new SettingsException("Failed to instantiate settings class for group '{$group}': {$e->getMessage()}");
        }

        // Check if all settings exist
        foreach ($values as $key => $value) {
            if (! property_exists($settingsObj, $key)) {
                throw new SettingsException("Setting '{$key}' not found in group '{$group}'.");
            }
        }

        // Check if the class has validation rules
        if (method_exists($class, 'validationRules')) {
            $rules = $class::validationRules();
            $validationRules = [];

            // Only validate the settings that are being imported
            foreach ($values as $key => $value) {
                if (isset($rules[$key])) {
                    $validationRules[$key] = $rules[$key];
                }
            }

            if (! empty($validationRules)) {
                $validator = Validator::make($values, $validationRules);

                if ($validator->fails()) {
                    throw ValidationException::withMessages($validator->errors()->toArray());
                }
            }
        }

        return true;
    }
}
