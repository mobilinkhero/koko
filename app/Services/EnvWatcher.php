<?php

namespace App\Services;

use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\File;

/**
 * EnvWatcher Service
 *
 * Monitors environment file changes and automatically clears application cache
 * to ensure configuration changes take effect immediately. Essential for
 * maintaining consistency in multi-tenant environments where configuration
 * changes must be reflected across all tenant contexts.
 *
 * Key Features:
 * - Environment file modification detection
 * - Automatic cache invalidation on changes
 * - Configuration cache clearing via Artisan commands
 * - Tagged cache support for selective clearing
 * - Comprehensive error handling and logging
 *
 * Monitoring Strategy:
 * - Tracks .env file modification timestamp
 * - Compares with cached timestamp to detect changes
 * - Clears multiple cache layers when changes detected
 * - Updates cached timestamp to prevent repeated clearing
 *
 * Cache Clearing Process:
 * 1. Configuration cache clearing via `config:clear`
 * 2. Tagged cache clearing (config, env tags)
 * 3. Full cache flush fallback for non-tagged drivers
 * 4. Comprehensive error logging
 *
 * Usage Scenarios:
 * - Development environment configuration updates
 * - Production deployment configuration changes
 * - Runtime environment variable modifications
 * - Multi-tenant configuration management
 *
 * Usage Examples:
 * ```php
 * $envWatcher = new EnvWatcher();
 *
 * // Check for environment changes (typically in middleware)
 * $envWatcher->checkForChanges();
 *
 * // In a scheduled task or middleware
 * if (app()->environment(['local', 'staging'])) {
 *     $envWatcher->checkForChanges();
 * }
 * ```
 *
 * @see \Illuminate\Support\Facades\Artisan
 * @see \Illuminate\Support\Facades\Cache
 *
 * @version 1.0.0
 */
class EnvWatcher
{
    /**
     * Cache key for storing environment file modification timestamp
     *
     * @var string
     */
    private const CACHE_KEY = 'env_last_modified';

    /**
     * Monitor environment file for changes and clear cache when needed
     *
     * Compares the current modification timestamp of the .env file with
     * the cached timestamp. If changes are detected, clears all relevant
     * application cache and updates the cached timestamp.
     *
     * Process Flow:
     * 1. Check if .env file exists
     * 2. Get current file modification timestamp
     * 3. Compare with cached timestamp
     * 4. Clear cache if timestamps differ
     * 5. Update cached timestamp
     * 6. Log change event
     *
     *
     * @example
     * ```php
     * // In middleware or scheduled task
     * $envWatcher = new EnvWatcher();
     * $envWatcher->checkForChanges();
     *
     * // Will log: "Environment file changed, cache cleared at 2024-01-15 10:30:00"
     * ```
     *
     * @see clearCache()
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
     * Clear all application cache layers
     *
     * Performs comprehensive cache clearing to ensure environment changes
     * take effect immediately. Uses multiple strategies based on cache
     * driver capabilities and environment context.
     *
     * Cache Clearing Strategy:
     * 1. Artisan config:clear - Clears Laravel configuration cache
     * 2. Tagged cache flush - Selective clearing for supported drivers
     * 3. Full cache flush - Fallback for non-tagged drivers
     * 4. Error handling - Logs failures without breaking execution
     *
     *
     * @throws \Exception When cache clearing fails (caught and logged)
     *
     * @example
     * ```php
     * // Called automatically by checkForChanges()
     * // Clears config cache, tagged cache, or full cache
     * // Logs: "Failed to clear cache after env change: [error message]"
     * ```
     *
     * @see checkForChanges()
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
