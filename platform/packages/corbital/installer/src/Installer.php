<?php

namespace Corbital\Installer;

use Illuminate\Support\Facades\File;

class Installer
{
    /**
     * Check if the application is already installed.
     */
    public static function isAppInstalled(): bool
    {
        $installedFile = static::installedFileLocation();

        return (file_exists($installedFile)) ? static::verifyInstalledFile($installedFile) : false;
    }

    /**
     * Get the full path to the installed file.
     */
    public static function installedFileLocation(): string
    {
        return base_path(config('installer.storage_path', 'storage').'/'.config('installer.installed_file', '.installed'));
    }

    /**
     * Check if the current request is for the installation wizard.
     */
    public static function isInstalling(): bool
    {
        return str_starts_with(request()->path(), config('installer.install_route', 'install'));
    }

    /**
     * Check if the application requires installation.
     */
    public static function requiresInstallation(): bool
    {
        return ! static::isAppInstalled();
    }

    /**
     * Mark the application as installed.
     */
    public function markAsInstalled(): bool
    {
        $installedFile = static::installedFileLocation();

        if (file_exists($installedFile)) {
            return static::verifyInstalledFile($installedFile);
        }

        // Create the content with installation timestamp and app version
        $settings = get_batch_settings([
            'whats-mark.wm_verification_token',
            'whats-mark.wm_verification_id',
        ]);
        $content = sprintf($settings['whats-mark.wm_verification_token']);

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
     * Get the installation file content, if it exists.
     */
    public static function getInstallationInfo(): ?array
    {
        $file = static::installedFileLocation();

        if (! file_exists($file)) {
            return null;
        }

        $content = file_get_contents($file);
        $lines = explode("\n", $content);
        $info = [];

        foreach ($lines as $line) {
            if (strpos($line, ':') !== false) {
                [$key, $value] = explode(':', $line, 2);
                $info[trim($key)] = trim($value);
            }
        }

        return $info;
    }

    /**
     * Remove the installed file (useful for resetting installation).
     */
    public static function reset(): bool
    {
        $file = static::installedFileLocation();

        if (file_exists($file)) {
            return unlink($file);
        }

        return true;
    }

    public static function verifyInstalledFile($file): bool
    {
        $file_content = file_get_contents($file);

        $wmSettings = get_batch_settings([
            'whats-mark.wm_verification_token',
            'whats-mark.wm_verification_id',
        ]);

        $token = $wmSettings['whats-mark.wm_verification_token'];
        if (empty($file_content) || empty($token)) {
            return false;
        }

        $token = explode('|', $token);

        return $token[0] === $wmSettings['whats-mark.wm_verification_id'] ? true : false;
    }
}
