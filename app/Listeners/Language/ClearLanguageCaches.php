<?php

namespace App\Listeners\Language;

use App\Events\Language\LanguageEvent;
use App\Services\LanguageCacheService;
use Illuminate\Events\Dispatcher;

class ClearLanguageCaches
{
    public function __construct(
        private LanguageCacheService $languageCacheService
    ) {}

    /**
     * Register the listeners for the subscriber.
     */
    public function subscribe(Dispatcher $events): void
    {
        $events->listen(
            LanguageEvent::class,
            [ClearLanguageCaches::class, 'handle']
        );
    }

    /**
     * Handle all language events
     */
    public function handle(LanguageEvent $event): void
    {
        match ($event->action) {
            LanguageEvent::ACTION_CREATED => $this->handleLanguageCreated($event),
            LanguageEvent::ACTION_UPDATED => $this->handleLanguageUpdated($event),
            LanguageEvent::ACTION_DELETED => $this->handleLanguageDeleted($event),
            default => app_log('Unknown language event action', 'warning', null, ['action' => $event->action])
        };
    }

    /**
     * Handle language created event
     */
    private function handleLanguageCreated(LanguageEvent $event): void
    {
        $this->languageCacheService->warmUpCaches();
    }

    /**
     * Handle language updated event
     */
    private function handleLanguageUpdated(LanguageEvent $event): void
    {
        $this->languageCacheService->warmUpCaches();
    }

    /**
     * Handle language deleted event
     */
    private function handleLanguageDeleted(LanguageEvent $event): void
    {
        $this->languageCacheService->warmUpCaches();
    }

    /**
     * Clear user-specific language caches
     */
    private function clearUserLanguageCaches(int $userId): void
    {
        // Clear any user-specific language preference caches
        cache()->forget("user_language_preference.{$userId}");
        cache()->forget("user_settings.{$userId}");
    }
}
