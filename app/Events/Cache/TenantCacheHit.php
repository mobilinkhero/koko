<?php

namespace App\Events\Cache;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/**
 * Tenant Cache Hit Event
 *
 * Fired when a cache key is successfully retrieved from tenant cache
 */
class TenantCacheHit
{
    use Dispatchable;
    use SerializesModels;

    public string $tenantId;

    public string $key;

    public array $tags;

    public \DateTime $timestamp;

    public function __construct(string $tenantId, string $key, array $tags = [])
    {
        $this->tenantId = $tenantId;
        $this->key = $key;
        $this->tags = $tags;
        $this->timestamp = new \DateTime;
    }

    /**
     * Get the cache key used for this hit
     */
    public function getCacheKey(): string
    {
        return "tenant_{$this->tenantId}_{$this->key}";
    }

    /**
     * Get event data for logging/analytics
     */
    public function getEventData(): array
    {
        return [
            'event_type' => 'cache_hit',
            'tenant_id' => $this->tenantId,
            'cache_key' => $this->key,
            'tags' => $this->tags,
            'timestamp' => $this->timestamp->format('Y-m-d H:i:s'),
        ];
    }
}
