<?php

namespace App\Services;

use App\Facades\AdminCache;
use App\Models\Currency;

/**
 * Currency Cache Service
 *
 * Provides efficient caching mechanisms for currency data to eliminate repeated database queries
 * and improve application performance. This service maintains an in-memory cache of all currencies
 * with multiple access patterns for optimal retrieval speed.
 *
 * Key Features:
 * - Static in-memory caching for maximum performance
 * - Multiple access patterns (by ID, by code, default currency)
 * - Automatic cache loading and refresh mechanisms
 * - Error handling and fallback strategies
 * - Cache invalidation and management
 * - Lazy loading to minimize memory usage
 *
 * Cache Structure:
 * - **all**: Collection of all available currencies
 * - **default**: The default/base currency for the application
 * - **by_id**: Currency lookup indexed by ID
 * - **by_code**: Currency lookup indexed by currency code (USD, EUR, etc.)
 *
 * @author corbitaltech dev team
 *
 * @since 1.0.0
 * @see \App\Models\Currency
 * @see \Illuminate\Support\Facades\Cache
 *
 * @example
 * ```php
 * // Get default currency
 * $defaultCurrency = CurrencyCache::getBaseCurrency();
 * echo "Base currency: {$defaultCurrency->code}";
 *
 * // Get currency by code
 * $usd = CurrencyCache::getCurrencyByCode('USD');
 * if ($usd) {
 *     echo "USD Symbol: {$usd->symbol}";
 * }
 *
 * // Get all currencies for dropdown
 * $currencies = CurrencyCache::getAllCurrencies();
 * foreach ($currencies as $currency) {
 *     echo "{$currency->code} - {$currency->name}";
 * }
 *
 * // Clear cache when currencies are updated
 * CurrencyCache::clearCache();
 * ```
 */
class CurrencyCache
{
    /**
     * Static cache storage for currency data
     *
     * @var array
     */
    protected static $cache = [];

    /**
     * Flag to track if currencies have been loaded
     *
     * @var bool
     */
    protected static $loaded = false;

    /**
     * Get cache key for currencies
     *
     * @return string The cache key used for storing currency data
     */
    protected static function getCacheKey(): string
    {
        return 'currencies_cache';
    }

    /**
     * Load all currencies into cache
     *
     * Performs a one-time load of all currency data from the database into both
     * Laravel's cache system and static memory cache for optimal performance.
     * Uses a 6-hour cache TTL to balance performance with data freshness.
     *
     *
     * @example
     * ```php
     * // Manually trigger currency loading (usually automatic)
     * CurrencyCache::loadCurrencies();
     * ```
     *
     * @see Currency::all()
     * @see Cache::remember()
     */
    public static function loadCurrencies(): void
    {
        if (static::$loaded) {
            return;
        }

        $cacheKey = static::getCacheKey();

        static::$cache = AdminCache::remember($cacheKey, function () {
            try {
                $currencies = Currency::all();

                $defaultCurrency = $currencies->firstWhere('is_default', 1);

                return [
                    'all' => $currencies,
                    'default' => $defaultCurrency ?? $currencies->first(),
                    'by_id' => $currencies->keyBy('id'),
                    'by_code' => $currencies->keyBy('code'),
                ];
            } catch (\Exception $e) {
                app_log('Error loading currencies', 'error', $e, [
                    'error' => $e->getMessage(),
                ]);

                return [
                    'all' => collect([]),
                    'default' => null,
                    'by_id' => collect([]),
                    'by_code' => collect([]),
                ];
            }
        }, ['admin.settings', 'model.currency']);

        static::$loaded = true;
    }

    /**
     * Get the base (default) currency
     *
     * Returns the currency marked as default in the system. If no default is set,
     * returns the first available currency as fallback.
     *
     * @return \App\Models\Currency|null The default currency or null if none available
     *
     * @example
     * ```php
     * $baseCurrency = CurrencyCache::getBaseCurrency();
     * if ($baseCurrency) {
     *     echo "Default currency: {$baseCurrency->code} ({$baseCurrency->symbol})";
     *     $baseRate = $baseCurrency->exchange_rate;
     * }
     * ```
     *
     * @see loadCurrencies()
     */
    public static function getBaseCurrency()
    {
        static::loadCurrencies();

        return static::$cache['default'] ?? null;
    }

    /**
     * Get a currency by its code
     *
     * Retrieves a specific currency using its ISO currency code (e.g., 'USD', 'EUR', 'GBP').
     * Returns null if the currency code is not found.
     *
     * @param  string  $code  The ISO currency code (case-sensitive)
     * @return \App\Models\Currency|null The currency object or null if not found
     *
     * @example
     * ```php
     * $usd = CurrencyCache::getCurrencyByCode('USD');
     * $eur = CurrencyCache::getCurrencyByCode('EUR');
     *
     * if ($usd && $eur) {
     *     $rate = $eur->exchange_rate / $usd->exchange_rate;
     *     echo "EUR to USD rate: {$rate}";
     * }
     * ```
     *
     * @see loadCurrencies()
     */
    public static function getCurrencyByCode(string $code)
    {
        static::loadCurrencies();

        return static::$cache['by_code'][$code] ?? null;
    }

    /**
     * Get a currency by its ID
     *
     * Retrieves a specific currency using its database primary key ID.
     * Returns null if the currency ID is not found.
     *
     * @param  int  $id  The currency database ID
     * @return \App\Models\Currency|null The currency object or null if not found
     *
     * @example
     * ```php
     * $currency = CurrencyCache::getCurrencyById(1);
     * if ($currency) {
     *     echo "Currency: {$currency->name} ({$currency->code})";
     *     echo "Symbol: {$currency->symbol}";
     *     echo "Exchange Rate: {$currency->exchange_rate}";
     * }
     * ```
     *
     * @see loadCurrencies()
     */
    public static function getCurrencyById(int $id)
    {
        static::loadCurrencies();

        return static::$cache['by_id'][$id] ?? null;
    }

    /**
     * Get all currencies
     *
     * Returns a collection of all available currencies in the system.
     * Useful for building currency selection dropdowns or currency lists.
     *
     * @return \Illuminate\Support\Collection Collection of Currency models
     *
     * @example
     * ```php
     * $currencies = CurrencyCache::getAllCurrencies();
     *
     * // Build currency dropdown options
     * $options = $currencies->map(function ($currency) {
     *     return [
     *         'value' => $currency->id,
     *         'label' => "{$currency->code} - {$currency->name}",
     *         'symbol' => $currency->symbol
     *     ];
     * });
     *
     * // Get currency count
     * $count = $currencies->count();
     * echo "Available currencies: {$count}";
     * ```
     *
     * @see loadCurrencies()
     */
    public static function getAllCurrencies()
    {
        static::loadCurrencies();

        return static::$cache['all'] ?? collect([]);
    }

    /**
     * Clear the currencies cache
     *
     * Removes all cached currency data from both Laravel's cache system and static memory.
     * Should be called when currency data is updated to ensure fresh data is loaded.
     *
     *
     * @example
     * ```php
     * // After updating currency exchange rates
     * $currency = Currency::find(1);
     * $currency->update(['exchange_rate' => 1.25]);
     *
     * // Clear cache to reload fresh data
     * CurrencyCache::clearCache();
     *
     * // Next access will load updated data
     * $freshCurrency = CurrencyCache::getCurrencyById(1);
     * ```
     *
     * @see Cache::forget()
     */
    public static function clearCache(): void
    {
        try {
            AdminCache::invalidateTags(['admin.settings', 'model.currency']);
            static::$cache = [];
            static::$loaded = false;

        } catch (\Exception $e) {
            app_log('Error clearing currency cache', 'error', $e, [
                'error' => $e->getMessage(),
            ]);
        }
    }
}
