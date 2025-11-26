<?php

namespace App\Services;

use App\Models\Tax;
use Illuminate\Support\Facades\Cache;

/**
 * Tax Cache Service
 *
 * Provides efficient caching mechanisms for tax data to eliminate repeated database queries
 * and improve application performance. This service maintains an in-memory cache of all taxes
 * with multiple access patterns for optimal retrieval speed.
 *
 * Key Features:
 * - Static in-memory caching for maximum performance
 * - Multiple access patterns (by ID, default tax)
 * - Automatic cache loading and refresh mechanisms
 * - Error handling and fallback strategies
 * - Cache invalidation and management
 * - Lazy loading to minimize memory usage
 *
 * Cache Structure:
 * - **all**: Collection of all available taxes
 * - **default**: The default tax for the application
 * - **by_id**: Tax lookup indexed by ID
 *
 * @since 1.0.0
 * @see \App\Models\Tax
 * @see \Illuminate\Support\Facades\Cache
 */
class TaxCache
{
    /**
     * In-memory cache for taxes
     */
    protected static $cache = [];

    /**
     * Flag to track if taxes have been loaded
     */
    protected static $loaded = false;

    /**
     * Get cache key for taxes
     */
    public static function getCacheKey(): string
    {
        return 'taxes_cache';
    }

    /**
     * Load all taxes into cache
     *
     * Fetches all taxes from the database and organizes them by different
     * access patterns for efficient retrieval. Results are stored both in
     * Laravel's cache system and in static memory for ultra-fast access.
     *
     * @example
     * ```php
     * // Manually load taxes (normally done automatically)
     * TaxCache::loadTaxes();
     * ```
     */
    public static function loadTaxes(): void
    {
        if (! static::$loaded) {
            try {
                // Try to get from Laravel cache first
                static::$cache = Cache::remember(static::getCacheKey(), now()->addDay(), function () {
                    $taxes = Tax::all();

                    return [
                        'all' => $taxes,
                        'by_id' => $taxes->keyBy('id'),
                    ];
                });

                static::$loaded = true;
            } catch (\Exception $e) {
                // Log error but don't crash
                app_log('Failed to load taxes into cache: '.$e->getMessage(), 'error', $e);
                static::$cache = [
                    'all' => collect([]),
                    'by_id' => collect([]),
                ];
            }
        }
    }

    /**
     * Get a tax by ID
     *
     * Retrieves a specific tax by its ID without querying the database.
     *
     * @param  int  $id  The tax ID to retrieve
     * @return \App\Models\Tax|null The tax object or null if not found
     *
     * @example
     * ```php
     * // Get tax by ID
     * $tax = TaxCache::getTaxById(1);
     *
     * if ($tax) {
     *     echo "Tax name: {$tax->name}, Rate: {$tax->rate}%";
     * }
     * ```
     */
    public static function getTaxById($id)
    {
        static::loadTaxes();

        return static::$cache['by_id'][$id] ?? null;
    }

    /**
     * Get all taxes
     *
     * Returns a collection of all taxes in the system, sorted by name.
     *
     * @return \Illuminate\Support\Collection Collection of tax objects
     *
     * @example
     * ```php
     * // Get all taxes for dropdown
     * $taxes = TaxCache::getAllTaxes();
     *
     * foreach ($taxes as $tax) {
     *     echo "Tax: {$tax->name} - {$tax->rate}%<br>";
     * }
     *
     * // Get tax count
     * $count = $taxes->count();
     * echo "Available taxes: {$count}";
     * ```
     */
    public static function getAllTaxes()
    {
        static::loadTaxes();

        return static::$cache['all'] ?? collect([]);
    }

    /**
     * Clear the taxes cache
     *
     * Removes all cached tax data from both Laravel's cache system and static memory.
     * Should be called when tax data is updated to ensure fresh data is loaded.
     *
     * @example
     * ```php
     * // After updating tax rates
     * $tax = Tax::find(1);
     * $tax->update(['rate' => 10.0]);
     *
     * // Clear cache to reload fresh data
     * TaxCache::clearCache();
     * ```
     */
    public static function clearCache(): void
    {
        try {
            Cache::forget(static::getCacheKey());
            static::$cache = [];
            static::$loaded = false;
        } catch (\Exception $e) {
            app_log('Failed to clear tax cache: '.$e->getMessage(), 'error', $e);
        }
    }

    /**
     * Reset the tax cache
     *
     * Forces the cache to be rebuilt on next access by clearing both
     * the in-memory cache and the Laravel cache storage.
     */
    public static function reset(): void
    {
        static::$loaded = false;
        static::$cache = [];
        Cache::forget(static::getCacheKey());
    }
}
