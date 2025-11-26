<?php

namespace Corbital\Installer\Classes;

class PermissionsChecker
{
    /**
     * The folders to check permissions for.
     */
    protected array $folders;

    /**
     * Create a new PermissionsChecker instance.
     */
    public function __construct(?array $folders = null)
    {
        $this->folders = $folders ?? config('installer.permissions', [
            'storage/app' => '0755',
            'storage/framework' => '0755',
            'storage/logs' => '0755',
            'bootstrap/cache' => '0755',
        ]);
    }

    /**
     * Check all folders for correct permissions.
     */
    public function check(): array
    {
        $results = [];
        $hasErrors = false;

        foreach ($this->folders as $folder => $permission) {
            // Get the full path by prepending the base path
            $fullPath = base_path($folder);

            // Check if the folder exists
            $exists = file_exists($fullPath);

            // Check if the folder is writable
            $isWritable = $exists ? is_writable($fullPath) : false;

            $results[] = [
                'folder' => $folder,
                'permission' => $permission,
                'exists' => $exists,
                'isWritable' => $isWritable,
            ];

            if (! $exists || ! $isWritable) {
                $hasErrors = true;
            }
        }

        return [
            'items' => $results,
            'errors' => $hasErrors,
        ];
    }

    /**
     * Get the current process user.
     */
    public static function getCurrentProcessUser(): string
    {
        if (function_exists('posix_getpwuid') && function_exists('posix_geteuid')) {
            $user = posix_getpwuid(posix_geteuid());

            return $user['name'] ?? 'unknown';
        }

        return 'unknown';
    }

    /**
     * Get suggestions for fixing permissions.
     */
    public function getFixSuggestions(): array
    {
        $user = $this->getCurrentProcessUser();
        $basePath = base_path();

        return [
            "sudo chown -R {$user}:www-data {$basePath}",
            "sudo find {$basePath} -type d -exec chmod 775 {} \;",
            "sudo find {$basePath} -type f -exec chmod 664 {} \;",
        ];
    }
}
