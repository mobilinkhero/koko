<?php

// namespace App\Services;

namespace Modules\SystemInfo\Services;

use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\File;

class EnvWatcher
{
    private const CACHE_KEY = 'env_last_modified';

    /**
     * Check for environment changes and clear cache if needed
     */
    public function checkForChanges(): void
    {
        $envPath = base_path('.env');
        if (! File::exists($envPath)) {
            return;
        }

        $lastModified = File::lastModified($envPath);
        $lastKnownModified = Cache::get(self::CACHE_KEY);

        if ($lastKnownModified !== $lastModified) {
            $this->clearCache();
            Cache::forever(self::CACHE_KEY, $lastModified);
        }
    }

    /**
     * Clear all relevant application cache
     */
    private function clearCache(): void
    {
        try {
            // Instead of trying to clear the config in memory (which doesn't work),
            // use Artisan to clear the config cache
            if (app()->environment() !== 'testing') {
                Artisan::call('config:clear');
            }

            // Clear any config-related cache tags
            if (Cache::supportsTags()) {
                Cache::tags(['config', 'env'])->flush();
            } else {
                // For cache drivers that don't support tags (like file)
                Cache::flush();
            }

        } catch (\Exception $e) {
            app_log(t('failed_to_clear_cache_after_env_change').' '.$e->getMessage(), 'error');
        }
    }
}
