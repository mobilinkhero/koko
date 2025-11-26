<?php

namespace App\Services;

class LanguageCacheService
{
    /**
     * Warm up language caches
     */
    public function warmUpCaches(): void
    {
        try {
            // Rebuild language cache
            \App\Models\Language::getCached();
        } catch (\Exception $e) {
            app_log('Failed to warm up language caches', 'error', $e, [
                'error' => $e->getMessage(),
            ]);
        }
    }
}
