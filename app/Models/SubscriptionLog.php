<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id
 * @property int $subscription_id
 * @property string $type
 * @property string|null $description
 * @property int|null $transaction_id
 * @property array<array-key, mixed>|null $data
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Subscription $subscription
 * @property-read \App\Models\Transaction|null $transaction
 *
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SubscriptionLog newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SubscriptionLog newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SubscriptionLog query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SubscriptionLog whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SubscriptionLog whereData($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SubscriptionLog whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SubscriptionLog whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SubscriptionLog whereSubscriptionId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SubscriptionLog whereTransactionId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SubscriptionLog whereType($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SubscriptionLog whereUpdatedAt($value)
 *
 * @mixin \Eloquent
 */
class SubscriptionLog extends BaseModel
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'subscription_id',
        'type',
        'description',
        'transaction_id',
        'data',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'data' => 'json',
    ];

    /**
     * Get the subscription that owns the log.
     */
    public function subscription(): BelongsTo
    {
        return $this->belongsTo(Subscription::class);
    }

    /**
     * Get the transaction that is related to the log.
     */
    public function transaction(): BelongsTo
    {
        return $this->belongsTo(Transaction::class);
    }

    /**
     * Get the data for a specific key.
     */
    public function getData($key = null, $default = null)
    {
        $data = json_decode($this->data, true) ?: [];

        if (is_null($key)) {
            return $data;
        }

        return $data[$key] ?? $default;
    }

    /**
     * Get a formatted message based on the log type.
     */
    public function getFormattedMessage(): string
    {
        return match ($this->type) {
            'created' => 'Subscription created for '.$this->getData('plan'),
            'activated' => 'Subscription activated for '.$this->getData('plan'),
            'renewed' => 'Subscription renewed for '.$this->getData('plan').' until '.$this->getData('end_date'),
            'cancelled' => 'Subscription cancelled on '.$this->getData('cancel_date'),
            'ended' => 'Subscription ended on '.$this->getData('end_date'),
            'terminated' => 'Subscription terminated by admin on '.$this->getData('terminate_date'),
            'plan_changed' => 'Plan changed from '.$this->getData('from_plan').' to '.$this->getData('to_plan'),
            'recurring_enabled' => 'Auto-renewal enabled for '.$this->getData('plan'),
            'recurring_disabled' => 'Auto-renewal disabled for '.$this->getData('plan'),
            default => ucfirst(str_replace('_', ' ', $this->type)),
        };
    }
}
