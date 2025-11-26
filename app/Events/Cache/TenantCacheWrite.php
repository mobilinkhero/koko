<?php

namespace App\Events\Cache;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/**
 * Tenant Cache Write Event
 *
 * Fired when data is written to tenant cache
 */
class TenantCacheWrite
{
    use Dispatchable;
    use SerializesModels;

    public string $tenantId;

    public string $key;

    public array $tags;

    public string $strategy;

    public \DateTime $timestamp;

    public function __construct(string $tenantId, string $key, array $tags = [], string $strategy = 'cache_aside')
    {
        $this->tenantId = $tenantId;
        $this->key = $key;
        $this->tags = $tags;
        $this->strategy = $strategy;
        $this->timestamp = new \DateTime;
    }

    /**
     * Get the cache key used for this write
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
            'event_type' => 'cache_write',
            'tenant_id' => $this->tenantId,
            'cache_key' => $this->key,
            'tags' => $this->tags,
            'strategy' => $this->strategy,
            'timestamp' => $this->timestamp->format('Y-m-d H:i:s'),
        ];
    }
}
