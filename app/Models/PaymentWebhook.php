<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;

/**
 * @property int $id
 * @property string $provider
 * @property string|null $webhook_id
 * @property string $endpoint_url
 * @property string|null $secret
 * @property bool $is_active
 * @property array<array-key, mixed>|null $events
 * @property array<array-key, mixed>|null $metadata
 * @property \Illuminate\Support\Carbon|null $last_pinged_at
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 *
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PaymentWebhook active()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PaymentWebhook forProvider($provider)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PaymentWebhook newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PaymentWebhook newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PaymentWebhook query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PaymentWebhook whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PaymentWebhook whereEndpointUrl($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PaymentWebhook whereEvents($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PaymentWebhook whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PaymentWebhook whereIsActive($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PaymentWebhook whereLastPingedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PaymentWebhook whereMetadata($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PaymentWebhook whereProvider($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PaymentWebhook whereSecret($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PaymentWebhook whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PaymentWebhook whereWebhookId($value)
 *
 * @mixin \Eloquent
 */
class PaymentWebhook extends BaseModel
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<string>
     */
    protected $fillable = [
        'provider',
        'webhook_id',
        'endpoint_url',
        'secret',
        'is_active',
        'events',
        'metadata',
        'last_pinged_at',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'is_active' => 'boolean',
        'events' => 'array',
        'metadata' => 'array',
        'last_pinged_at' => 'datetime',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'secret',
    ];

    /**
     * Get the registered events as a flat array.
     */
    public function getEventsArray(): array
    {
        return $this->events ?? [];
    }

    /**
     * Scope a query to only include active webhooks.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope a query to only include webhooks for a specific provider.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @param  string  $provider
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeForProvider($query, $provider)
    {
        return $query->where('provider', $provider);
    }
}
