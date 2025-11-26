<?php

namespace Corbital\ModuleManager\Classes;

use Illuminate\Support\Facades\File;

class ModuleInstall
{
    /**
     * Mark the application as installed.
     *
     * @throws \Exception
     */
    public function markAsInstalled($data): void
    {
        if (! $this->markAsModuleInstalled($data)) {
            throw new \Exception('Failed to mark application as installed.');
        }
    }

    /**
     * Check if the application is already installed.
     */
    public static function isAppInstalled($item_id): bool
    {
        $installedFile = static::installedFileLocation($item_id);

        return (file_exists($installedFile)) ? static::verifyInstalledFile($installedFile, $item_id) : false;
    }

    /**
     * Get the full path to the installed file.
     */
    public static function installedFileLocation($item_id): string
    {
        return base_path(config('installer.storage_path', 'storage').'/.module_'.$item_id);
    }

    /**
     * Check if the application requires installation.
     */
    public static function requiresInstallation($item_id): bool
    {
        return ! static::isAppInstalled($item_id);
    }

    /**
     * Mark the application as installed.
     */
    public function markAsModuleInstalled($data): bool
    {
        $installedFile = static::installedFileLocation($data['item_id']);

        if (file_exists($installedFile)) {
            return static::verifyInstalledFile($installedFile, $data['item_id']);
        }

        $settings = get_module($data['item_id']);
        $content = sprintf($settings['payload']['token']);
        // Ensure storage directory exists
        $storagePath = base_path(config('installer.storage_path', 'storage'));
        if (! File::exists($storagePath)) {
            File::makeDirectory($storagePath, 0755, true);
        }

        // Write the installed file
        $bytes = File::put($installedFile, $content);

        return $bytes !== false;
    }

    /**
     * Remove the installed file (useful for resetting installation).
     */
    public static function reset($item_id): bool
    {
        $file = static::installedFileLocation($item_id);

        if (file_exists($file)) {
            return unlink($file);
        }

        return true;
    }

    public static function verifyInstalledFile($file, $item_id): bool
    {
        $file_content = file_get_contents($file);
        $moduleData = get_module($item_id);

        if (empty($moduleData)) {
            return false;
        }

        $token = $moduleData['payload']['verification_token'];
        if (empty($file_content) || empty($token)) {
            return false;
        }

        $token = explode('|', $token);

        return $token[0] === $moduleData['payload']['verification_id'] ? true : false;
    }
}
