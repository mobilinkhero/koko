<?php

namespace App\Observers;

use App\Services\Cache\AdminCacheManager;
use Illuminate\Database\Eloquent\Model;

/**
 * Cache Invalidation Observer
 *
 * Automatically handles cache invalidation when models are created,
 * updated, or deleted. This observer is attached to relevant models
 * to ensure cache consistency.
 */
class CacheInvalidationObserver
{
    protected AdminCacheManager $cacheManager;

    public function __construct(AdminCacheManager $cacheManager)
    {
        $this->cacheManager = $cacheManager;
    }

    /**
     * Handle the model "created" event
     */
    public function created(Model $model): void
    {
        if ($this->shouldHandle()) {
            $this->cacheManager->handleModelEvent(
                get_class($model),
                'created',
                $model
            );

            $this->logCacheEvent($model, 'created');
        }
    }

    /**
     * Handle the model "updated" event
     */
    public function updated(Model $model): void
    {
        if ($this->shouldHandle()) {
            $this->cacheManager->handleModelEvent(
                get_class($model),
                'updated',
                $model
            );

            $this->logCacheEvent($model, 'updated');
        }
    }

    /**
     * Handle the model "deleted" event
     */
    public function deleted(Model $model): void
    {
        if ($this->shouldHandle()) {
            $this->cacheManager->handleModelEvent(
                get_class($model),
                'deleted',
                $model
            );

            $this->logCacheEvent($model, 'deleted');
        }
    }

    /**
     * Handle the model "restored" event
     */
    public function restored(Model $model): void
    {
        if ($this->shouldHandle()) {
            $this->cacheManager->handleModelEvent(
                get_class($model),
                'restored',
                $model
            );

            $this->logCacheEvent($model, 'restored');
        }
    }

    /**
     * Handle the model "force deleted" event
     */
    public function forceDeleted(Model $model): void
    {
        if ($this->shouldHandle()) {
            $this->cacheManager->handleModelEvent(
                get_class($model),
                'forceDeleted',
                $model
            );

            $this->logCacheEvent($model, 'forceDeleted');
        }
    }

    /**
     * Check if cache invalidation should be handled
     */
    protected function shouldHandle(): bool
    {
        // Don't handle during testing unless explicitly enabled
        if (app()->environment('testing') && ! config('admin-cache.development.disable_in_testing', true)) {
            return false;
        }

        // Check if auto-invalidation is enabled
        return config('admin-cache.auto_invalidation', true);
    }

    /**
     * Log cache invalidation events
     */
    protected function logCacheEvent(Model $model, string $event): void
    {
        if (config('admin-cache.monitoring.log_invalidations', true)) {

        }
    }
}
