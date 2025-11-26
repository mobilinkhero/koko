<?php

namespace App\Helpers;

use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;

class ModuleHelper
{
    /**
     * Ensure the module temp directory exists and is writable
     *
     * @return string The path to the temp directory
     */
    public static function ensureTempDirectory()
    {
        $tempPath = storage_path('app/temp-modules');

        if (! File::exists($tempPath)) {
            try {
                File::makeDirectory($tempPath, 0755, true);
                Log::info("Created module temp directory: {$tempPath}");
            } catch (\Exception $e) {
                Log::error("Failed to create module temp directory: {$e->getMessage()}");
                throw new \RuntimeException("Failed to create module temp directory: {$e->getMessage()}");
            }
        }

        return $tempPath;
    }

    /**
     * Find the module.json file in the extracted directory.
     *
     * @param  string  $directory  The directory to search in
     * @return string|null The path to module.json or null if not found
     */
    public static function findModuleJson($directory)
    {
        if (! $directory || ! File::exists($directory)) {
            Log::warning("Directory does not exist: {$directory}");

            return null;
        }

        // First, check if module.json exists in the root of the extracted directory
        if (File::exists($directory.'/module.json')) {
            return $directory.'/module.json';
        }

        // If not found in the root, check first-level subdirectories
        foreach (File::directories($directory) as $subDir) {
            if (File::exists($subDir.'/module.json')) {
                return $subDir.'/module.json';
            }

            // Check one level deeper
            foreach (File::directories($subDir) as $subSubDir) {
                if (File::exists($subSubDir.'/module.json')) {
                    return $subSubDir.'/module.json';
                }
            }
        }

        // If still not found, try a more exhaustive search
        try {
            $files = File::allFiles($directory);
            foreach ($files as $file) {
                if ($file->getFilename() === 'module.json') {
                    return $file->getRealPath();
                }
            }
        } catch (\Exception $e) {
            Log::error("Error searching for module.json: {$e->getMessage()}");
        }

        Log::warning("Could not find module.json in {$directory}");

        return null;
    }

    /**
     * Create a storage link if it doesn't exist
     */
    public static function createStorageLink()
    {
        if (! file_exists(public_path('storage'))) {
            try {
                symlink(storage_path('app/public'), public_path('storage'));
                Log::info('Created storage symlink');
            } catch (\Exception $e) {
                Log::error("Failed to create storage symlink: {$e->getMessage()}");
            }
        }
    }
}
