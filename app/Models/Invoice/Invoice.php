<?php

namespace App\Models\Invoice;

use App\Exceptions\IncompletePaymentException;
use App\Models\BaseModel;
use App\Models\CreditTransaction;
use App\Models\Currency;
use App\Models\Subscription;
use App\Models\Tenant;
use App\Models\TenantCreditBalance;
use App\Models\Transaction;
use App\Services\Billing\TransactionResult;
use App\Services\SubscriptionCache;
use Carbon\Carbon;
use Dompdf\Dompdf;
use Exception;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Validator;

/**
 * @property int $id
 * @property int $tenant_id
 * @property int|null $subscription_id
 * @property string $type
 * @property string $status
 * @property string $title
 * @property string|null $description
 * @property string|null $metadata
 * @property int $currency_id
 * @property numeric $total_tax_amount
 * @property numeric $fee
 * @property string|null $invoice_number
 * @property \Illuminate\Support\Carbon|null $due_date
 * @property \Illuminate\Support\Carbon|null $paid_at
 * @property string|null $cancelled_at
 * @property int $no_payment_required_when_free
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read Currency $currency
 * @property-read string $formatted_total
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Invoice\InvoiceItem> $items
 * @property-read int|null $items_count
 * @property-read Subscription|null $subscription
 * @property-read Tenant $tenant
 * @property-read \Illuminate\Database\Eloquent\Collection<int, Transaction> $transactions
 * @property-read int|null $transactions_count
 *
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Invoice changePlan()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Invoice newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Invoice newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Invoice newSubscription()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Invoice paid()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Invoice query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Invoice renewSubscription()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Invoice unpaid()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Invoice whereCancelledAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Invoice whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Invoice whereCurrencyId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Invoice whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Invoice whereDueDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Invoice whereFee($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Invoice whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Invoice whereInvoiceNumber($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Invoice whereMetadata($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Invoice whereNoPaymentRequiredWhenFree($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Invoice wherePaidAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Invoice whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Invoice whereSubscriptionId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Invoice whereTenantId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Invoice whereTitle($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Invoice whereType($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Invoice whereUpdatedAt($value)
 *
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Invoice\InvoiceTax> $taxes
 * @property-read int|null $taxes_count
 *
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Invoice whereTotalTaxAmount($value)
 *
 * @mixin \Eloquent
 */
class Invoice extends BaseModel
{
    use HasFactory;

    // Invoice statuses
    public const STATUS_NEW = 'new';

    public const STATUS_PAID = 'paid';

    public const STATUS_CANCELLED = 'cancelled';

    public const STATUS_REFUNDED = 'refunded';

    public const STATUS_PARTIALLY_REFUNDED = 'partially_refunded';

    protected $with = ['currency'];

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'tenant_id',
        'subscription_id',
        'type',
        'title',
        'description',
        'status',
        'currency_id',
        'coupon_id',
        'coupon_discount',
        'coupon_code',
        'total_tax_amount',
        'fee',
        'invoice_number',
        'due_date',
        'no_payment_required_when_free',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'paid_at' => 'datetime',
        'due_date' => 'datetime',
        'total_tax_amount' => 'decimal:2',
        'fee' => 'decimal:2',
        'coupon_discount' => 'decimal:2',
        'coupon_snapshot' => 'array',
    ];

    /**
     * The accessors to append to the model's array form.
     *
     * @var array
     */
    protected $appends = ['formatted_total', 'total'];

    /**
     * Boot the model.
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($invoice) {
            do_action('invoice.before_create', $invoice);
        });

        static::created(function ($invoice) {
            do_action('invoice.after_create', $invoice);
        });

        static::updating(function ($invoice) {
            do_action('invoice.before_update', $invoice);

            // Check for status changes
            if ($invoice->isDirty('status')) {
                $oldStatus = $invoice->getOriginal('status');
                $newStatus = $invoice->status;

                if ($oldStatus !== self::STATUS_PAID && $newStatus === self::STATUS_PAID) {
                    do_action('invoice.before_paid', $invoice);
                } elseif ($oldStatus !== self::STATUS_CANCELLED && $newStatus === self::STATUS_CANCELLED) {
                    do_action('invoice.before_void', $invoice);
                }
            }
        });

        static::updated(function ($invoice) {
            do_action('invoice.after_update', $invoice);

            // Check for status changes
            if ($invoice->wasChanged('status')) {
                $oldStatus = $invoice->getOriginal('status');
                $newStatus = $invoice->status;

                if ($oldStatus !== self::STATUS_PAID && $newStatus === self::STATUS_PAID) {
                    do_action('invoice.after_paid', $invoice);
                } elseif ($oldStatus !== self::STATUS_CANCELLED && $newStatus === self::STATUS_CANCELLED) {
                    do_action('invoice.after_void', $invoice);
                }
            }
        });

        static::deleting(function ($invoice) {
            do_action('invoice.before_delete', $invoice);
        });

        static::deleted(function ($invoice) {
            do_action('invoice.after_delete', $invoice);
        });
    }

    /**
     * Get the customer that owns the invoice.
     */
    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    /**
     * Get the subscription that owns the invoice.
     */
    public function subscription(): BelongsTo
    {
        return $this->belongsTo(Subscription::class);
    }

    /**
     * Get the invoice items for the invoice.
     */
    public function items(): HasMany
    {
        return $this->hasMany(InvoiceItem::class);
    }

    /**
     * Get the transactions for the invoice.
     */
    public function transactions(): HasMany
    {
        return $this->hasMany(Transaction::class);
    }

    /**
     * Get the currency that owns the invoice.
     */
    public function currency(): BelongsTo
    {
        return $this->belongsTo(Currency::class);
    }

    /**
     * Get the taxes applied to this invoice.
     */
    public function taxes(): HasMany
    {
        return $this->hasMany(InvoiceTax::class, 'invoice_id');
    }

    /**
     * Get the coupon that owns the invoice.
     */
    public function coupon(): BelongsTo
    {
        return $this->belongsTo(\App\Models\Coupon::class);
    }

    /**
     * Scope a query to only include new subscription invoices.
     */
    public function scopeNewSubscription($query)
    {
        return $query->where('type', InvoiceNewSubscription::TYPE_NEW_SUBSCRIPTION);
    }

    /**
     * Scope a query to only include renewal invoices.
     */
    public function scopeRenewSubscription($query)
    {
        return $query->where('type', InvoiceRenewSubscription::TYPE_RENEW_SUBSCRIPTION);
    }

    /**
     * Scope a query to only include plan change invoices.
     */
    public function scopeChangePlan($query)
    {
        return $query->where('type', InvoiceChangePlan::TYPE_CHANGE_PLAN);
    }

    /**
     * Scope a query to only include unpaid invoices.
     */
    public function scopeUnpaid($query)
    {
        return $query->where('status', self::STATUS_NEW);
    }

    /**
     * Scope a query to only include paid invoices.
     */
    public function scopePaid($query)
    {
        return $query->where('status', self::STATUS_PAID);
    }

    /**
     * Return the appropriate invoice class based on type.
     */
    public function mapType(): Invoice
    {
        return match ($this->type) {
            InvoiceNewSubscription::TYPE_NEW_SUBSCRIPTION => InvoiceNewSubscription::find($this->id),
            InvoiceRenewSubscription::TYPE_RENEW_SUBSCRIPTION => InvoiceRenewSubscription::find($this->id),
            InvoiceChangePlan::TYPE_CHANGE_PLAN => InvoiceChangePlan::find($this->id),
            default => $this,
        };
    }

    /**
     * Calculate subtotal (sum of all items).
     */
    public function subTotal(): float
    {
        // Ensure the items relationship is loaded
        if (! $this->relationLoaded('items')) {
            $this->load('items');
        }

        return $this->items->sum(function ($item) {
            return $item->amount * $item->quantity;
        });
    }

    /**
     * Calculate tax amount.
     */
    public function getTax(): float
    {
        return $this->total_tax_amount ?: $this->taxes()->sum('amount');
    }

    /**
     * Calculate and update the total tax amount for this invoice.
     */
    public function calculateTotalTaxAmount(): float
    {
        $totalTaxAmount = $this->taxes()->sum('amount');
        $this->total_tax_amount = $totalTaxAmount;
        $this->save();

        return $totalTaxAmount;
    }

    /**
     * Apply taxes to this invoice based on subtotal.
     *
     * @param  \Illuminate\Support\Collection|null  $taxes  Optional collection of Tax models to apply
     * @return array Array of created InvoiceTax records
     */
    public function applyTaxes($taxes = null): array
    {

        // If invoice is already paid or cancelled, don't modify taxes
        if ($this->isPaid() || $this->isCancelled()) {
            return [];
        }
        // If the invoice already has taxes, don't reapply them
        if ($this->taxes()->exists()) {
            return $this->taxes()->get()->toArray();
        }

        // If no taxes provided, use default taxes from settings at the time of invoice creation
        if ($taxes === null) {
            $taxes = get_default_taxes();
        }

        // Calculate subtotal
        $subtotal = $this->subTotal();

        $taxRecords = [];
        $totalTaxAmount = 0;

        // Create tax records for each tax
        foreach ($taxes as $tax) {
            $taxAmount = round(abs($subtotal * ($tax->rate / 100)), 2);

            $invoiceTax = $this->taxes()->create([
                'tax_id' => $tax->id,
                'name' => $tax->name,
                'rate' => $tax->rate,
                'amount' => $taxAmount,
            ]);

            $taxRecords[] = $invoiceTax;
            $totalTaxAmount += $taxAmount;
        }

        // Update the total tax amount on the invoice
        $this->total_tax_amount = $totalTaxAmount;
        $this->save();

        return $taxRecords;
    }

    /**
     * Get all tax details for this invoice.
     */
    public function getTaxDetails(): array
    {
        // Get taxes with the invoice relationship loaded to avoid lazy loading issues
        $taxes = $this->taxes()->get();

        // Explicitly load the invoice relationship on each tax
        foreach ($taxes as $tax) {
            $tax->setRelation('invoice', $this);
        }

        return $taxes->map(function ($tax) {
            return [
                'id' => $tax->id,
                'name' => $tax->name,
                'rate' => $tax->rate,
                'amount' => $tax->amount,
                'formatted_rate' => $tax->formattedRate(),
                'formatted_amount' => $tax->formattedAmount(),
            ];
        })->toArray();
    }

    /**
     * Apply coupon to invoice
     */
    public function applyCoupon(\App\Models\Coupon $coupon): void
    {
        $discountAmount = $coupon->calculateDiscount($this->subTotal());

        $this->coupon_id = $coupon->id;
        $this->coupon_code = $coupon->code;
        $this->coupon_discount = $discountAmount;

        // Save coupon snapshot for historical records
        $this->coupon_snapshot = [
            'id' => $coupon->id,
            'code' => $coupon->code,
            'name' => $coupon->name,
            'description' => $coupon->description,
            'type' => $coupon->type,
            'value' => $coupon->value,
            'minimum_amount' => $coupon->minimum_amount,
            'maximum_discount' => $coupon->maximum_discount,
            'applicable_plans' => $coupon->applicable_plans,
            'applicable_billing_periods' => $coupon->applicable_billing_periods,
            'first_payment_only' => $coupon->first_payment_only,
            'usage_count_at_application' => $coupon->usage_count,
            'calculated_discount' => $discountAmount,
            'subtotal_at_application' => $this->subTotal(),
            'applied_at' => now()->toISOString(),
        ];

        $this->save();
    }

    /**
     * Remove coupon from invoice
     */
    public function removeCoupon(): void
    {
        $this->coupon_id = null;
        $this->coupon_code = null;
        $this->coupon_discount = 0;
        $this->coupon_snapshot = null;
        $this->save();
    }

    /**
     * Check if invoice has coupon applied
     */
    public function hasCoupon(): bool
    {
        return ! is_null($this->coupon_id);
    }

    /**
     * Get coupon discount amount
     */
    public function getCouponDiscount(): float
    {
        return (float) $this->coupon_discount;
    }

    /**
     * Get subtotal after coupon discount
     */
    public function subTotalAfterCoupon(): float
    {
        return max(0, $this->subTotal() - $this->getCouponDiscount());
    }

    /**
     * Calculate total (subtotal + tax + fee) without coupon discount.
     * Coupon discount is only applied after credit deduction.
     */
    public function total(): float
    {
        $subtotal = $this->subTotal();
        $taxAmount = $this->getTax();
        $fee = (float) $this->fee;

        // Total without coupon discount - coupon is applied after credit deduction
        return $subtotal + $taxAmount + $fee;
    }

    /**
     * Format total with currency symbol.
     */
    public function formattedTotal(): string
    {
        $currency = $this->currency ?? Currency::getDefault();

        return $currency
            ? $currency->format($this->total())
            : number_format($this->total(), 2);
    }

    /**
     * Calculate final payable amount after credit and coupon deduction.
     * Order: total - credit - coupon discount on remaining amount
     */
    public function finalPayableAmount(float $creditAmount = 0): float
    {
        $total = $this->total();

        // First deduct credit
        $afterCredit = max(0, $total - $creditAmount);

        // Then apply coupon discount to the remaining amount
        if ($this->hasCoupon() && $afterCredit > 0) {
            // Get the coupon to recalculate discount on remaining amount
            $coupon = $this->coupon;
            if ($coupon) {
                $newDiscount = $coupon->calculateDiscount($afterCredit);
                $afterCredit = max(0, $afterCredit - $newDiscount);
            }
        }

        return $afterCredit;
    }

    /**
     * Calculate coupon discount for amount after credit deduction.
     */
    public function getCouponDiscountAfterCredit(float $creditAmount = 0): float
    {
        if (! $this->hasCoupon()) {
            return 0;
        }

        $total = $this->total();
        $afterCredit = max(0, $total - $creditAmount);

        if ($afterCredit <= 0) {
            return 0;
        }

        $coupon = $this->coupon;
        if ($coupon) {
            return $coupon->calculateDiscount($afterCredit);
        }

        return 0;
    }

    /**
     * Format any amount with the invoice's currency.
     */
    public function formatAmount(float $amount): string
    {
        if (! $this->relationLoaded('currency') || ! $this->currency) {
            return number_format($amount, 2); // Fallback format
        }

        return $this->currency->format($amount);
    }

    /**
     * Check if invoice is free (total = 0).
     * For plan changes, we don't consider prorated amounts making total 0 as "free"
     */
    public function isFree(): bool
    {
        // For plan changes, we never consider it free even if total is 0 due to proration
        if ($this->type === InvoiceChangePlan::TYPE_CHANGE_PLAN) {
            return false;
        }

        return $this->total() <= 0;
    }

    /**
     * Check if invoice is unpaid.
     */
    public function isUnpaid(): bool
    {
        return $this->status == self::STATUS_NEW;
    }

    /**
     * Check if invoice is paid.
     */
    public function isPaid(): bool
    {
        return $this->status == self::STATUS_PAID;
    }

    /**
     * Check if invoice is cancelled.
     */
    public function isCancelled(): bool
    {
        return $this->status == self::STATUS_CANCELLED;
    }

    /**
     * Mark invoice as cancelled.
     */
    public function markAsCancelled(): self
    {
        $this->status = self::STATUS_CANCELLED;
        $this->cancelled_at = now();
        $this->save();

        return $this;
    }

    /**
     * Mark invoice as paid.
     */
    public function markAsPaid(): self
    {
        $this->status = self::STATUS_PAID;
        $this->paid_at = now();
        $this->save();

        $this->createInvoiceNumber(); // Ensure invoice number is created on payment

        // Get the typed instance to ensure proper processing
        $typedInvoice = $this->mapType();

        // If this invoice maps to a specialized type, use that instance to process
        if ($typedInvoice->id === $this->id && get_class($typedInvoice) !== get_class($this)) {
            $typedInvoice->afterPaymentProcessed();
        } else {
            // Process after payment
            $this->afterPaymentProcessed();
        }

        return $this;
    }

    /**
     * Process after payment (to be overridden by specific invoice types).
     */
    public function afterPaymentProcessed(): void
    {
        // Load the subscription relationship if not already loaded
        if (! $this->relationLoaded('subscription')) {
            $this->load('subscription');
        }

        $this->subscription->status = Subscription::STATUS_ACTIVE;
        $this->subscription->current_period_ends_at = $this->subscription->getPeriodEndsAt(Carbon::now());
        $this->subscription->save();

        SubscriptionCache::clearCache($this->subscription->tenant_id);
    }

    /**
     * Create a pending transaction for this invoice.
     */
    public function createPendingTransaction($gateway, $tenant_id): Transaction
    {
        // Check if there's already a pending transaction
        $pendingTransaction = $this->transactions()
            ->where('status', Transaction::STATUS_PENDING)
            ->first();

        if ($pendingTransaction) {
            return $pendingTransaction;
        }

        $currency_id = $this->currency_id;

        $balance = TenantCreditBalance::getOrCreateBalance($tenant_id, $currency_id);
        $credit = 0;
        if ($balance->balance > 0) {
            $total = $this->total();
            $credit = $balance->balance;
            if ($credit > $total) {
                $credit = $total;
            }
        }

        // Use finalPayableAmount to calculate correct amount including coupon after credit
        $amount = $this->finalPayableAmount($credit);

        // Create new transaction
        $transaction = new Transaction([
            'invoice_id' => $this->id,
            'amount' => $amount,
            'currency_id' => $currency_id,
            'status' => Transaction::STATUS_PENDING,
            'type' => $gateway->getType(),
            'description' => 'Payment for Invoice',
        ]);

        $transaction->save();

        return $transaction;
    }

    /**
     * Check if the invoice has any pending transactions.
     */
    public function hasPendingTransactions(): bool
    {
        return $this->transactions()
            ->where('status', Transaction::STATUS_PENDING)
            ->exists();
    }

    /**
     * Check if the invoice only has failed transactions.
     */
    public function hasOnlyFailedTransactions(): bool
    {
        // If there are no transactions, return false
        if ($this->transactions()->count() === 0) {
            return false;
        }

        // Return true if all transactions are failed (no pending or successful ones)
        return ! $this->transactions()->where('status', '!=', Transaction::STATUS_FAILED)->exists();
    }

    /**
     * Process checkout with a payment gateway.
     */
    public function checkout($gateway, \Closure $executePayment): bool
    {
        try {
            // Create a pending transaction
            $transaction = $this->createPendingTransaction($gateway, $this->tenant_id);

            // Execute payment
            $result = $executePayment($this, $transaction);

            // Handle transaction result
            return $this->handleTransactionResult($transaction, $result);
        } catch (Exception $e) {
            payment_log('Payment error:', 'error', [
                'tenant_id' => tenant_id(),
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    /**
     * Handle transaction result.
     */
    public function handleTransactionResult($transaction, TransactionResult $result): bool
    {
        if ($result->isDone()) {
            // Payment successful
            $transaction->status = Transaction::STATUS_SUCCESS;
            $transaction->save();

            // Mark invoice as paid
            $this->markAsPaid();

            event(new \App\Events\TransactionSuccessful($transaction));

            return true;
        } elseif ($result->isPending()) {
            // Payment is pending (e.g., offline payment or 3D Secure)
            $transaction->status = Transaction::STATUS_PENDING;
            $transaction->save();

            // For 3D Secure, we need to throw an IncompletePaymentException
            if ($result->getMessage() === 'requires_action') {
                $paymentIntentId = $transaction->metadata['stripe_payment_intent_id'] ?? null;
                if ($paymentIntentId) {
                    $tenant = current_tenant();
                    $paymentIntent = $tenant->stripe()->paymentIntents->retrieve($paymentIntentId);
                    throw new IncompletePaymentException($paymentIntent);
                }
            }

            event(new \App\Events\TransactionPending($transaction));

            return true;
        } else {
            // Payment failed
            $transaction->status = Transaction::STATUS_FAILED;
            $transaction->error = $result->getMessage();
            $transaction->save();

            event(new \App\Events\TransactionFailed($transaction));

            return false;
        }
    }

    /**
     * Bypass payment for free invoices.
     */
    public function bypassPayment(): bool
    {
        // First check if this is a plan change invoice
        if ($this->type === InvoiceChangePlan::TYPE_CHANGE_PLAN) {
            throw new Exception('Cannot bypass payment for plan upgrades');
        }

        if (! $this->isFree() && ! $this->no_payment_required_when_free) {
            throw new Exception('Cannot bypass payment for non-free invoice');
        }

        // Create a pseudo transaction
        $transaction = new Transaction([
            'invoice_id' => $this->id,
            'amount' => 0,
            'currency_id' => $this->currency_id,
            'status' => Transaction::STATUS_SUCCESS,
            'type' => 'free',
            'description' => 'Free invoice, no payment required',
        ]);

        $transaction->save();

        // Mark invoice as paid
        $this->markAsPaid();

        return true;
    }

    /**
     * Check if invoice has billing information.
     */
    public function hasBillingInformation(): bool
    {
        return ! empty($this->billing_first_name) && ! empty($this->billing_last_name) && ! empty($this->billing_address) && ! empty($this->billing_city) && ! empty($this->billing_country_id) && ! empty($this->billing_email);
    }

    /**
     * Update billing information.
     */
    public function updateBillingInformation($data)
    {
        $validator = Validator::make($data, [
            'billing_first_name' => 'required|string|max:255',
            'billing_last_name' => 'required|string|max:255',
            'billing_address' => 'required|string|max:255',
            'billing_city' => 'required|string|max:255',
            'billing_state' => 'nullable|string|max:255',
            'billing_postal_code' => 'nullable|string|max:50',
            'billing_country_id' => 'required|exists:countries,id',
            'billing_phone' => 'nullable|string|max:50',
            'billing_email' => 'required|email|max:255',
        ]);

        if ($validator->fails()) {
            return $validator;
        }

        $this->fill($data);
        $this->save();

        return $validator;
    }

    /**
     * Get billing information as an array.
     */
    public function getBillingInfo(): array
    {
        return [
            'billing_first_name' => $this->billing_first_name,
            'billing_last_name' => $this->billing_last_name,
            'billing_name' => $this->billing_first_name.' '.$this->billing_last_name,
            'billing_address' => $this->billing_address,
            'billing_city' => $this->billing_city,
            'billing_state' => $this->billing_state,
            'billing_postal_code' => $this->billing_postal_code,
            'billing_country' => $this->billing_country_id,
            'billing_phone' => $this->billing_phone,
            'billing_email' => $this->billing_email,
        ];
    }

    /**
     * Generate invoice number.
     */
    public function createInvoiceNumber(): string
    {
        if ($this->invoice_number) {
            return $this->invoice_number;
        }

        $systemSettings = get_batch_settings([
            'invoice.prefix',
        ]);
        $prefix = $systemSettings['invoice.prefix'] ?? 'INV-';
        $year = date('Y');
        $month = date('m');

        // Get latest invoice number
        $latestInvoice = Invoice::whereNotNull('invoice_number')
            ->orderBy('id', 'desc')
            ->first();

        if ($latestInvoice && preg_match('/(\d+)$/', $latestInvoice->invoice_number, $matches)) {
            $nextNumber = intval($matches[1]) + 1;
        } else {
            $nextNumber = 1;
        }

        $invoiceNumber = $prefix.$year.$month.'-'.str_pad($nextNumber, 6, '0', STR_PAD_LEFT);

        $this->invoice_number = $invoiceNumber;
        $this->save();

        return $invoiceNumber;
    }

    /**
     * Get PDF template content.
     */
    public static function getTemplateContent(): string
    {
        $path = resource_path('views/invoices/pdf.blade.php');

        if (! file_exists($path)) {
            return self::getDefaultTemplate();
        }

        return file_get_contents($path);
    }

    public function getTransactionidFrominvoices($invoiceId)
    {
        return Transaction::where('invoice_id', $invoiceId)->get();
    }

    /**
     * Get default PDF template.
     */
    protected static function getDefaultTemplate(): string
    {
        return '
<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <title>{{ t(\'invoice\') }} #{{ $invoice->invoice_number ?? format_draft_invoice_number() }}</title>
    <style>
        /* Modern Professional Invoice Styling - Dompdf Compatible */
        body {
            font-family: Arial, Helvetica, sans-serif;
            margin: 0;
            padding: 0;
            color: #2d3748;
            position: relative;
            font-size: 14px;
            line-height: 1.6;
            background-color: #f8fafc;
        }

        /* Main Container */
        .invoice-container {
            max-width: 850px;
            margin: 8px auto;
            padding: 15px;
            position: relative;
            background-color: #fff;
            border-radius: 10px;
        }

        /* Modern Header */
        .invoice-header {
            margin-bottom: 12px;
            padding-bottom: 12px;
            border-bottom: 1px solid #e2e8f0;
        }

        .invoice-header::after {
            content: "";
            display: table;
            clear: both;
        }

        /* Logo Styling */
        .logo {
            float: left;
            max-width: 200px;
            width: 40%;
        }

        .logo img {
            max-height: 70px;
            width: auto;
        }

        .logo h2 {
            color: #4f46e5;
            font-size: 24px;
            margin: 0;
            font-weight: bold;
        }

        /* Invoice Info Section */
        .invoice-info {
            float: right;
            text-align: right;
            width: 55%;
        }

        .invoice-title {
            color: #4f46e5;
            font-size: 36px;
            font-weight: bold;
            margin: 0 0 10px;
            letter-spacing: 0.5px;
        }

        .invoice-meta {
            font-size: 15px;
            line-height: 1.6;
            color: #64748b;
        }

        .invoice-meta strong {
            color: #334155;
        }

        /* Status Badge */
        .status-container {
            margin-bottom: 15px;
            text-align: right;
        }

        .status-badge {
            display: inline-block;
            padding: 5px 15px;
            border-radius: 30px;
            font-size: 15px;
            font-weight: bold;
            letter-spacing: 0.5px;
            text-transform: uppercase;
        }

        /* Status Colors */
        .status-new {
            background-color: #eff6ff;
            color: #2563eb;
            border: 1px solid #93c5fd;
        }

        .status-paid {
            background-color: #ecfdf5;
            color: #059669;
            border: 1px solid #6ee7b7;
        }

        .status-cancelled {
            background-color: #fef2f2;
            color: #dc2626;
            border: 1px solid #fca5a5;
        }

        /* Two-column Layout for Addresses */
        .address-section {
            display: flex;
            justify-content: space-between;
            margin-bottom: 15px;
            gap: 15px;
        }

        .address-block {
            width: 49%;
            padding: 8px;
            background-color: #f8fafc;
            border-radius: 8px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.05);
        }

        .address-block h3 {
            color: #4f46e5;
            font-size: 13px;
            font-weight: 600;
            margin-top: 0;
            margin-bottom: 4px;
            padding-bottom: 3px;
            border-bottom: 1px solid #e2e8f0;
        }

        .address-content strong {
            display: inline-block;
            margin-bottom: 2px;
            color: #334155;
        }

        .address-content {
            font-size: 12px;
            line-height: 1.2;
        }

        /* Table Styling */
        .invoice-items {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
            font-size: 14px;
        }

        .invoice-items thead {
            background-color: #4f46e5;
            color: white;
        }

        .invoice-items th {
            padding: 8px 10px;
            text-align: left;
            font-weight: bold;
        }

        .invoice-items td {
            padding: 10px;
            text-align: left;
            border-bottom: 1px solid #e2e8f0;
            vertical-align: top;
            font-family: \'DejaVu Sans\';
        }

        .invoice-items tbody tr:nth-child(even) {
            background-color: #f8fafc;
        }

        .item-title {
            font-weight: bold;
            color: #334155;
            margin-bottom: 5px;
        }

        .item-description {
            color: #64748b;
            font-size: 14px;
        }

        /* Totals Section */
        .invoice-summary {
            float: right;
            width: 350px;
        }

        .invoice-summary-table {
            width: 100%;
            border-collapse: collapse;
            background-color: #f8fafc;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 1px 3px rgba(0,0,0,0.05);
        }

        .invoice-summary-table th {
            text-align: left;
            padding: 8px 10px;
            font-weight: 600;
            color: #4b5563;
            border-bottom: 1px solid #e2e8f0;
        }

        .invoice-summary-table td {
            text-align: right;
            padding: 8px 10px;
            border-bottom: 1px solid #e2e8f0;
            font-family: \'DejaVu Sans\';
        }

        /* Breakdown Section */
        .price-breakdown {
            background-color: #f0f9ff;
            border-left: 3px solid #4f46e5;
            padding: 10px 15px;
        }

        .price-breakdown-title {
            font-weight: bold;
            color: #4f46e5;
            margin-bottom: 5px;
        }

        .price-breakdown-item {
            padding: 3px 0 3px 10px;
            color: #4b5563;
            font-size: 14px;
        }

        .breakdown-base {
            font-weight: bold;
            color: #334155;
        }

        .tax-row th,
        .tax-row td {
            font-size: 15px;
            color: #4b5563;
        }

        .total-row th,
        .total-row td {
            border-top: 2px solid #4f46e5;
            font-weight: 700;
            font-size: 16px;
            color: #4f46e5;
            padding-top: 15px;
            padding-bottom: 15px;
        }

        /* Payment Information */
        .payment-details {
            clear: both;
            margin-top: 20px;
            padding: 15px;
            background-color: #ecfdf5;
            border-radius: 8px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.05);
            width: 100%;
            box-sizing: border-box;
        }

        .payment-title {
            font-weight: 600;
            color: #059669;
            margin-bottom: 8px;
            font-size: 13px;
        }

        .payment-info {
            color: #065f46;
            font-size: 14px;
        }

        /* Footer */
        .invoice-footer {
            clear: both;
            margin-top: 25px;
            padding-top: 15px;
            border-top: 1px solid #e2e8f0;
            text-align: center;
            font-size: 13px;
            color: #64748b;
        }

        /* Watermark */
        .watermark {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%) rotate(-35deg);
            font-size: 120px;
            font-weight: 800;
            text-transform: uppercase;
            opacity: 0.03;
            z-index: 0;
            width: 100%;
            text-align: center;
            pointer-events: none;
        }

        .watermark-paid {
            color: #059669;
        }

        .watermark-new {
            color: #2563eb;
        }

        .watermark-cancelled {
            color: #dc2626;
        }

        /* Bank Details */
        .bank-details {
            margin-top: 20px;
            padding: 15px;
            background-color: #f8fafc;
            border-radius: 8px;
            font-size: 14px;
            color: #4b5563;
        }

        .bank-details h3 {
            color: #4f46e5;
            font-size: 16px;
            font-weight: bold;
            margin-top: 0;
            margin-bottom: 10px;
            padding-bottom: 5px;
            border-bottom: 2px solid #e2e8f0;
        }

        /* Bank details table */
        .bank-details-table {
            width: 100%;
            border-collapse: collapse;
        }

        .bank-details-table td {
            padding: 4px 0;
            border: none;
        }

        .bank-details-label {
            font-weight: bold;
            width: 140px;
            color: #334155;
        }

        .bank-details-value {
            color: #4b5563;
        }

        /* Clearfix */
        .clearfix::after {
            content: "";
            clear: both;
            display: table;
        }
    </style>
</head>

<body>
    @php
    // Safely get invoice settings with null checks
    $invoiceSettings = $invoiceSettings ?? new stdClass();
    $bankDetails = [
        \'bank_name\' => $invoiceSettings->bank_name ?? \'\',
        \'account_name\' => $invoiceSettings->account_name ?? \'\',
        \'account_number\' => $invoiceSettings->account_number ?? \'\',
        \'ifsc_code\' => $invoiceSettings->ifsc_code ?? \'\',
    ];
    $footerText = $invoiceSettings->footer_text ?? \'\';

    // Ensure required variables exist
    $company_name = $company_name ?? config(\'app.name\', \'Company Name\');
    $company_logo = $company_logo ?? null;
    $invoice = $invoice ?? null;
    $tenant = $tenant ?? null;
    $items = $items ?? collect();
    @endphp

    <!-- Status Watermark -->
    @if($invoice)
    <div class="watermark watermark-{{ strtolower($invoice->status ?? \'new\') }}">
        {{ strtoupper($invoice->status ?? \'NEW\') }}
    </div>
    @endif

    <div class="invoice-container">
        <div class="invoice-header">
            <div class="logo">
                @if($company_logo)
                <img src="{{ $company_logo }}" alt="{{ $company_name }}" />
                @else
                <h2>{{ $company_name }}</h2>
                @endif
            </div>

            <div class="invoice-info">
                @if($invoice)
                    <div class="status-container">
                        <span class="status-badge status-{{ strtolower($invoice->status ?? \'new\') }}">
                            {{ ucfirst($invoice->status ?? \'New\') }}
                        </span>
                    </div>
                    <div class="invoice-title">INVOICE</div>
                    <div class="invoice-meta">
                        <strong>{{ t(\'invoice\') }} #:</strong> {{ $invoice->invoice_number ?? format_draft_invoice_number() }}<br>
                        <strong>{{ t(\'date\') }}:</strong> {{ $invoice->created_at ? $invoice->created_at->format("F j, Y") : date("F j, Y") }}<br>

                    @foreach ($transactions as $transaction)
                    <strong>
                       {{ t(\'transaction_id\') }}:
                    </strong>

                     @if ($transaction->type === \'offline\')
                        {{ $transaction->metadata[\'payment_reference\'] ?? \'-\' }}
                     @else
                        {{ $transaction->idempotency_key ?? \'-\' }}
                     @endif
                    @endforeach
                    </div>
                @endif
            </div>
        </div>

        <div class="address-section">
            <div class="address-block">
                <h3>{{ t(\'from\') }}</h3>
                <div class="address-content">
                    <strong>{{ $company_name }}</strong><br>
                    @if(isset($invoiceSettings->company_address) && !empty($invoiceSettings->company_address))
                    {!! nl2br(e($invoiceSettings->company_address)) !!}
                    @endif
                </div>
            </div>

            @if($tenant)
                <div class="address-block">
                    <h3>{{ t(\'bill_to\') }}</h3>
                    <div class="address-content">
                        <strong>{{ $tenant->billing_name ?? $tenant->company_name ?? \'Customer\' }}</strong><br>
                        @if(!empty($tenant->billing_address))
                        {!! nl2br(e($tenant->billing_address)) !!}@if(!empty($tenant->billing_city)),@endif<br>
                        @endif
                        @if(!empty($tenant->billing_city))
                        {{ $tenant->billing_city }}@if(!empty($tenant->billing_state)), {{ $tenant->billing_state }}@endif
                        {{ $tenant->billing_zip_code ?? \'\' }}<br>
                        @endif
                        @if(!empty($tenant->billing_phone))
                        {{ $tenant->billing_phone }}<br>
                        @endif
                        @if(!empty($tenant->billing_email))
                        {{ $tenant->billing_email }}
                        @endif
                    </div>
                </div>
            @endif
        </div>

        @if($items && $items->count() > 0)
            <table class="invoice-items">
                <thead>
                    <tr>
                        <th width="55%">{{ t(\'description\') }}</th>
                        <th width="15%">{{ t(\'price\') }}</th>
                        <th width="10%">{{ t(\'quantity\') }}</th>
                        <th width="20%">{{ t(\'amount\') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($items as $item)
                    <tr>
                        <td>
                            <div class="item-title">{{ $item->title ?? \'Item\' }}</div>
                            @if(!empty($item->description))
                            <div class="item-description">{!! nl2br(e($item->description)) !!}</div>
                            @endif
                        </td>
                        <td style="font-family": \'DejaVu Sans\';>{{ $invoice ? $invoice->formatAmount($item->amount ?? 0) : number_format($item->amount ?? 0, 2) }}</td>
                        <td>{{ $item->quantity ?? 1 }}</td>
                        <td style="font-family": \'DejaVu Sans\';>{{ $invoice ? $invoice->formatAmount(($item->amount ?? 0) * ($item->quantity ?? 1)) : number_format(($item->amount ?? 0) * ($item->quantity ?? 1), 2) }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        @endif

        @if($invoice)
            <div class="invoice-summary">
                <table class="invoice-summary-table">
                    @php
                        $subtotal = $invoice->subTotal();
                        $taxDetails = $invoice->getTaxDetails();
                        $taxAmount = 0;

                        // Calculate actual tax amount if needed - matching web version logic
                        foreach ($taxDetails as $tax) {
                            $amount = $tax[\'amount\'] ?? 0;
                            if ($amount <= 0 && ($tax[\'rate\'] ?? 0) > 0) {
                                $amount = $subtotal * (($tax[\'rate\'] ?? 0) / 100);
                            }
                            $taxAmount += $amount;
                        }

                        $fee = $invoice->fee ?? 0;
                        $calculatedTotal = $subtotal + $taxAmount + $fee;

                        // Use calculated total if different from invoice total
                        if (abs($calculatedTotal - $invoice->total()) > 0.01) {
                            $totalDisplay = $invoice->formatAmount($calculatedTotal);
                        } else {
                            $totalDisplay = $invoice->formattedTotal();
                        }
                    @endphp

                    {{-- Subtotal --}}
                    <tr>
                        <th>{{ t(\'subtotal\') }}</th>
                        <td style="font-family": \'DejaVu Sans\';>{{ $invoice->formatAmount($subtotal) }}</td>
                    </tr>

                    {{-- Tax Details --}}
                    @if(count($taxDetails) > 0)
                        @foreach($taxDetails as $tax)
                        @php
                            $taxAmount = $tax[\'amount\'] ?? 0;
                            if ($taxAmount <= 0 && ($tax[\'rate\'] ?? 0) > 0) {
                                $taxAmount = $subtotal * (($tax[\'rate\'] ?? 0) / 100);
                                $formattedTaxAmount = $invoice->formatAmount($taxAmount);
                            } else {
                                $formattedTaxAmount = $tax[\'formatted_amount\'] ?? $invoice->formatAmount($taxAmount);
                            }
                        @endphp
                        <tr class="tax-row">
                            <th>{{ $tax[\'name\'] ?? \'Tax\' }} ({{ $tax[\'formatted_rate\'] ?? \'0%\' }})</th>
                            <td style="font-family": \'DejaVu Sans\';>{{ $formattedTaxAmount }}</td>
                        </tr>
                        @endforeach
                    @else
                        <tr class="tax-row">
                            <th>{{ t(\'tax\') }} (0%)</th>
                            <td style="font-family": \'DejaVu Sans\';>{{ $invoice->formatAmount(0) }}</td>
                        </tr>
                    @endif

                    {{-- Fee --}}
                    @if($fee > 0)
                        <tr>
                            <th>{{ t(\'fee\') }}</th>
                            <td style="font-family": \'DejaVu Sans\';>{{ $invoice->formatAmount($fee) }}</td>
                        </tr>
                    @endif

                    @if (count($creditTransactions) > 0)
                        <tr class="tax-row">
                            <th>{{ t(\'credit_applied\') }}</th>
                            <td style="font-family: \'DejaVu Sans\';">
                                @php
                                    $credits = $creditTransactions->sum(\'amount\');
                                    if ($credits > $calculatedTotal) {
                                        $credits = $calculatedTotal;
                                    }
                                @endphp
                                {{ \'- \' . $invoice->formatAmount($credits) }}
                            </td>
                        </tr>
                        <tr class="tax-row">
                            <th>{{ $invoice->status == \'paid\' ? t(\'amount_paid\') : t(\'amount_due\') }}</th>
                            <td style="font-family: \'DejaVu Sans\';">
                                @php
                                    $finalamount = $calculatedTotal - $credits;
                                @endphp
                                {{ $invoice->formatAmount($finalamount) }}
                            </td>
                        </tr>
                    @endif

                    {{-- Total --}}
                    <tr class="total-row">
                        <th>{{ t(\'total\') }}</th>
                        <td style="font-family": \'DejaVu Sans\';>{{ $totalDisplay }}</td>
                    </tr>
                </table>
            </div>
        @endif

        <div class="clearfix"></div>

        @if($invoice && $invoice->status === "paid")
            <div class="payment-details">
                <div class="payment-title">{{ t(\'payment_information\') }}</div>
                <div class="payment-info">
                   {{ t(\'paid_on\') }} {{ $invoice->paid_at ? $invoice->paid_at->format("F j, Y") : "N/A" }}<br>
                    {{ t(\'payment_received_message\') }}
                </div>
            </div>
        @endif

        <div class="invoice-footer">
            @if(!empty($footerText))
            {!! nl2br(e($footerText)) !!}
            @else
                {{ t(\'payment_successful_message\') }}
            @endif
        </div>
    </div>
</body>

</html>';
    }

    /**
     * Get invoice HTML.
     */
    public function getInvoiceHtml(): string
    {
        // Ensure relationships are loaded
        $this->loadMissing(['tenant', 'items', 'currency']);

        // Create invoice number if not exists
        // if (empty($this->invoice_number)) {
        //     $this->createInvoiceNumber();
        // }

        // Get the view template content (Blade string)
        $template = self::getTemplateContent();

        // Get system settings
        $systemSettings = get_batch_settings([
            'system.company_name',
            'theme.site_logo',
            'system.company_address',
            'system.company_city',
            'system.company_state',
            'system.company_zip_code',
            'system.company_country_id',
            'system.company_email',
        ]);

        // Get invoice-specific settings
        try {
            $invoiceSettings = get_settings_by_group('invoice');
        } catch (\Exception $e) {
            $invoiceSettings = new \stdClass;
        }

        // Convert logo to base64
        $logoPath = $systemSettings['theme.site_logo'] ?? '';
        $logo = $this->getLogoBase64Optimized($logoPath) ?? $this->getDefaultLogoBase64();

        // Company info block
        $companyInfo = [
            'company_name' => $systemSettings['system.company_name'] ?? config('app.name'),
            'company_logo' => $logo,
            'company_address' => $systemSettings['system.company_address'] ?? '',
            'company_city' => $systemSettings['system.company_city'] ?? '',
            'company_state' => $systemSettings['system.company_state'] ?? '',
            'company_zip_code' => $systemSettings['system.company_zip_code'] ?? '',
            'company_country_id' => $systemSettings['system.company_country_id'] ?? '',
            'company_email' => $systemSettings['system.company_email'] ?? '',
        ];

        // Prepare final view data
        $viewData = array_merge($companyInfo, [
            'invoice' => $this,
            'tenant' => $this->tenant,
            'items' => $this->items,
            'invoiceSettings' => $invoiceSettings,
            'creditTransactions' => $this->getCreditTransactions(),
            'transactions' => $this->getTransactionidFrominvoices($this->id),
        ]);

        // Render and return HTML
        return Blade::render($template, $viewData);
    }

    public function getCreditTransactions()
    {
        return CreditTransaction::where('invoice_id', $this->id)->where('type', 'debit')->get();
    }

    /**
     * Get the available credit amount for this invoice's tenant.
     * This calculates the tenant's total available credit balance.
     */
    public function getAvailableCreditAmount(): float
    {
        $allCreditTransactions = CreditTransaction::where('tenant_id', $this->tenant_id)->get();

        $totalCredit = $allCreditTransactions->where('type', 'credit')->sum('amount');
        $totalDebit = $allCreditTransactions->where('type', 'debit')->sum('amount');

        return max(0, $totalCredit - $totalDebit);
    }

    /**
     * Get the credit amount that should be applied to this specific invoice.
     * This is the minimum of available credit and invoice total.
     */
    public function getApplicableCreditAmount(): float
    {
        $availableCredit = $this->getAvailableCreditAmount();
        $invoiceTotal = $this->total();

        return min($availableCredit, $invoiceTotal);
    }

    private function getLogoBase64Optimized(?string $path): ?string
    {
        if (empty($path)) {
            return null;
        }

        try {
            // Handle both absolute and relative paths
            if (str($path)->startsWith('http')) {
                // For URLs, try to get content directly
                $data = file_get_contents($path);
                if ($data === false) {
                    return null;
                }
            } else {
                // For local files, resolve the path
                $resolvedPath = str($path)->startsWith(['//', '/', 'C:\\', 'D:\\'])
                    ? $path
                    : (str($path)->startsWith('storage/')
                        ? public_path($path)
                        : storage_path('app/public/'.$path));

                if (! file_exists($resolvedPath)) {
                    app_log("Invoice logo not found: {$resolvedPath}", 'warning');

                    return null;
                }

                $data = file_get_contents($resolvedPath);
                if ($data === false) {
                    return null;
                }
            }

            // Get file extension and mime type
            $extension = strtolower(pathinfo($path, PATHINFO_EXTENSION));
            $mime = match ($extension) {
                'jpg', 'jpeg' => 'image/jpeg',
                'png' => 'image/png',
                'svg' => 'image/svg+xml',
                default => null
            };

            if (! $mime) {
                app_log("Invalid logo file type: {$extension}", 'warning');

                return null;
            }

            return 'data:'.$mime.';base64,'.base64_encode($data);
        } catch (\Exception $e) {
            app_log('Error processing invoice logo', 'error', $e);

            return null;
        }
    }

    private function getDefaultLogoBase64(): string
    {
        $defaultLogoPath = public_path('img/light_logo.png');

        try {
            if (file_exists($defaultLogoPath)) {
                $data = file_get_contents($defaultLogoPath);

                return 'data:image/png;base64,'.base64_encode($data);
            }
        } catch (\Exception $e) {
            app_log('Error loading default logo', 'error', $e);
        }

        // Fallback to a simple text-based SVG if the default logo file is not found
        return 'data:image/svg+xml;base64,'.base64_encode('
            <svg xmlns="http://www.w3.org/2000/svg" width="200" height="60" viewBox="0 0 200 60">
                <rect width="200" height="60" fill="#f8f9fa"/>
                <text x="100" y="35" font-family="Segoe UI, Arial, Helvetica, sans-serif" font-size="16"
                    fill="#495057" text-anchor="middle" dominant-baseline="middle">
                    '.htmlspecialchars(config('app.name', 'Company Logo')).'
                </text>
            </svg>
        ');
    }

    /**
     * Generate PDF from invoice.
     */
    public function exportToPdf(): string
    {
        $html = $this->getInvoiceHtml();
        // Create PDF
        $dompdf = new Dompdf;
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();

        // Return PDF content
        return $dompdf->output();
    }

    /**
     * Save PDF to storage.
     */
    public function savePdf(?string $path = null): string
    {
        if (! $path) {
            $path = storage_path('app/invoices/'.$this->invoice_number.'.pdf');
        }

        // Create directory if not exists
        $directory = dirname($path);
        if (! file_exists($directory)) {
            mkdir($directory, 0755, true);
        }

        // Generate and save PDF
        $pdf = $this->exportToPdf();
        file_put_contents($path, $pdf);

        return $path;
    }

    /**
     * Get the currency code.
     */
    public function getCurrencyCode(): string
    {
        if (! $this->relationLoaded('currency') || ! $this->currency) {
            return 'USD'; // Default fallback
        }

        return $this->currency->code;
    }

    /**
     * Get the formatted total attribute.
     */
    public function getFormattedTotalAttribute(): string
    {
        return $this->formattedTotal();
    }

    /**
     * Get the total attribute.
     */
    public function getTotalAttribute(): float
    {
        return $this->total();
    }

    /**
     * Generate a PDF version of the invoice
     *
     * @return string The PDF content as a string
     */
    public function generatePDF()
    {
        try {
            // Ensure relationships are loaded
            $this->loadMissing(['tenant', 'items', 'currency']);

            // Prepare the data for the view
            $settings = get_batch_settings([
                'system.site_name',
                'theme.site_logo',
            ]);
            // Get invoice settings with error handling
            try {
                $invoiceSettings = get_settings_by_group('invoice');
            } catch (\Exception $e) {
                $invoiceSettings = new \stdClass;
            }

            $logoPath = $systemSettings['theme.site_logo'] ?? '';
            $logo = $this->getLogoBase64Optimized($logoPath) ?? $this->getDefaultLogoBase64();

            $data = [
                'invoice' => $this,
                'tenant' => $this->tenant,
                'items' => $this->items,
                'company_name' => $settings['system.site_name'] ?? config('app.name'),
                'company_logo' => $logo,
                'invoiceSettings' => $invoiceSettings,
            ];
            // Render the view to HTML
            $html = view('invoices.pdf', $data)->render();

            // Create Dompdf instance with safe configuration
            $dompdf = new \Dompdf\Dompdf([
                'isRemoteEnabled' => false, // Disable remote content for security and reliability
                'isHtml5ParserEnabled' => true,
                'isPhpEnabled' => false,
                'tempDir' => storage_path('app/temp'),
                'chroot' => public_path(),
                'defaultFont' => 'Arial',
            ]);

            $dompdf->setPaper('A4', 'portrait');

            $dompdf->loadHtml($html);

            $dompdf->render();

            return $dompdf->output();
        } catch (\Exception $e) {
            return $this->getDefaultTemplate();
        }
    }
}
