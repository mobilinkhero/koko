<?php

namespace App\Models;

use App\Models\Invoice\Invoice;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id
 * @property int $invoice_id
 * @property int|null $payment_method_id
 * @property string $type
 * @property string $status
 * @property numeric $amount
 * @property int $currency_id
 * @property string|null $description
 * @property string|null $error
 * @property array<array-key, mixed>|null $metadata
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Currency $currency
 * @property-read Invoice $invoice
 * @property-read \App\Models\PaymentMethod|null $paymentMethod
 *
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Transaction failed()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Transaction newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Transaction newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Transaction pending()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Transaction query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Transaction successful()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Transaction whereAmount($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Transaction whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Transaction whereCurrencyId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Transaction whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Transaction whereError($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Transaction whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Transaction whereInvoiceId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Transaction whereMetadata($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Transaction wherePaymentMethodId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Transaction whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Transaction whereType($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Transaction whereUpdatedAt($value)
 *
 * @mixin \Eloquent
 */
class Transaction extends BaseModel
{
    use HasFactory;

    // Transaction statuses
    public const STATUS_PENDING = 'pending';

    public const STATUS_SUCCESS = 'success';

    public const STATUS_FAILED = 'failed';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'invoice_id',
        'payment_method_id',
        'type',
        'status',
        'amount',
        'currency_id',
        'description',
        'error',
        'idempotency_key',
        'metadata',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'amount' => 'decimal:2',
        'metadata' => 'json',
    ];

    /**
     * Boot the model.
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($transaction) {
            do_action('transaction.before_create', $transaction);
        });

        static::created(function ($transaction) {
            do_action('transaction.after_create', $transaction);
        });

        static::updating(function ($transaction) {
            do_action('transaction.before_update', $transaction);

            // Check for status changes
            if ($transaction->isDirty('status')) {
                $oldStatus = $transaction->getOriginal('status');
                $newStatus = $transaction->status;

                if ($oldStatus !== self::STATUS_SUCCESS && $newStatus === self::STATUS_SUCCESS) {
                    do_action('transaction.before_approve', $transaction);
                } elseif ($oldStatus !== self::STATUS_FAILED && $newStatus === self::STATUS_FAILED) {
                    do_action('transaction.before_reject', $transaction);
                }
            }
        });

        static::updated(function ($transaction) {
            do_action('transaction.after_update', $transaction);

            // Check for status changes
            if ($transaction->wasChanged('status')) {
                $oldStatus = $transaction->getOriginal('status');
                $newStatus = $transaction->status;

                if ($oldStatus !== self::STATUS_SUCCESS && $newStatus === self::STATUS_SUCCESS) {
                    do_action('transaction.after_approve', $transaction);
                    do_action('transaction.succeeded', $transaction);
                } elseif ($oldStatus !== self::STATUS_FAILED && $newStatus === self::STATUS_FAILED) {
                    do_action('transaction.after_reject', $transaction);
                    do_action('transaction.failed', $transaction);
                }
            }
        });

        static::deleting(function ($transaction) {
            do_action('transaction.before_delete', $transaction);
        });

        static::deleted(function ($transaction) {
            do_action('transaction.after_delete', $transaction);
        });
    }

    /**
     * Get the invoice that owns the transaction.
     */
    public function invoice(): BelongsTo
    {
        return $this->belongsTo(Invoice::class);
    }

    /**
     * Get the payment method that owns the transaction.
     */
    public function paymentMethod(): BelongsTo
    {
        return $this->belongsTo(PaymentMethod::class);
    }

    /**
     * Scope a query to only include pending transactions.
     */
    public function scopePending($query)
    {
        return $query->where('status', self::STATUS_PENDING);
    }

    /**
     * Scope a query to only include successful transactions.
     */
    public function scopeSuccessful($query)
    {
        return $query->where('status', self::STATUS_SUCCESS);
    }

    /**
     * Scope a query to only include failed transactions.
     */
    public function scopeFailed($query)
    {
        return $query->where('status', self::STATUS_FAILED);
    }

    /**
     * Check if transaction is pending.
     */
    public function isPending(): bool
    {
        return $this->status === self::STATUS_PENDING;
    }

    /**
     * Check if transaction is successful.
     */
    public function isSuccessful(): bool
    {
        return $this->status === self::STATUS_SUCCESS;
    }

    /**
     * Check if transaction is failed.
     */
    public function isFailed(): bool
    {
        return $this->status === self::STATUS_FAILED;
    }

    /**
     * Mark transaction as successful.
     */
    public function markAsSuccessful(?array $metadata = null): self
    {
        $this->status = self::STATUS_SUCCESS;

        if ($metadata) {
            $this->metadata = $metadata;
        }

        $this->save();

        return $this;
    }

    /**
     * Mark transaction as failed.
     */
    public function markAsFailed(string $error, ?array $metadata = null): self
    {
        $this->status = self::STATUS_FAILED;
        $this->error = $error;

        if ($metadata) {
            $this->metadata = $metadata;
        }

        $this->save();

        return $this;
    }

    public function currency(): BelongsTo
    {
        return $this->belongsTo(Currency::class);
    }

    /**
     * Get formatted amount with currency symbol.
     */
    public function formattedAmount(): string
    {
        if (! $this->relationLoaded('currency')) {
            $this->load('currency');
        }

        return $this->currency->format($this->amount);
    }

    /**
     * Get standardized payment details from transaction metadata
     */
    public function getPaymentDetails(): array
    {
        return \App\Services\PaymentDetailsExtractor::extract($this);
    }

    /**
     * Get payment reference (transaction ID from gateway)
     */
    public function getPaymentReference(): ?string
    {
        return $this->getPaymentDetails()['payment_reference'];
    }

    /**
     * Get payment date (when payment was actually processed)
     */
    public function getPaymentDate(): ?\Carbon\Carbon
    {
        return $this->getPaymentDetails()['payment_date'];
    }

    /**
     * Get payment method display name
     */
    public function getPaymentMethod(): string
    {
        return $this->getPaymentDetails()['payment_method'];
    }

    /**
     * Get additional payment details as array
     */
    public function getAdditionalDetails(): array
    {
        return $this->getPaymentDetails()['additional_details'] ?? [];
    }

    /**
     * Get payment details text
     */
    public function getPaymentDetailsText(): ?string
    {
        return $this->getPaymentDetails()['payment_details'];
    }

    /**
     * Get gateway-specific status
     */
    public function getGatewayStatus(): ?string
    {
        return $this->getPaymentDetails()['gateway_status'];
    }

    /**
     * Get amount received from gateway
     */
    public function getAmountReceived(): ?string
    {
        return $this->getPaymentDetails()['amount_received'];
    }

    /**
     * Get gateway transaction ID
     */
    public function getGatewayTransactionId(): ?string
    {
        return $this->getPaymentDetails()['transaction_id'];
    }

    /**
     * Get human-readable payment summary
     */
    public function getPaymentSummary(): string
    {
        return \App\Services\PaymentDetailsExtractor::getSummary($this);
    }
}
