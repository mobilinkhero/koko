<?php

namespace App\Models;

use App\Events\InvoicePaid;
use App\Events\SubscriptionActivated;
use App\Events\SubscriptionCancelled;
use App\Events\SubscriptionCreated;
use App\Events\SubscriptionDowngraded;
use App\Events\SubscriptionPlanChanged;
use App\Events\SubscriptionRenewed;
use App\Events\SubscriptionUpgraded;
use App\Models\Invoice\Invoice;
use App\Models\Invoice\InvoiceChangePlan;
use App\Models\Invoice\InvoiceNewSubscription;
use App\Models\Invoice\InvoiceRenewSubscription;
use App\Services\FeatureService;
use Carbon\Carbon;
use Exception;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;

/**
 * @property int $id
 * @property int $tenant_id
 * @property int $plan_id
 * @property string $status
 * @property \Illuminate\Support\Carbon|null $current_period_ends_at
 * @property string|null $trial_starts_at
 * @property \Illuminate\Support\Carbon|null $trial_ends_at
 * @property bool $is_recurring
 * @property \Illuminate\Support\Carbon|null $cancelled_at
 * @property \Illuminate\Support\Carbon|null $ended_at
 * @property \Illuminate\Support\Carbon|null $terminated_at
 * @property string|null $canceled_at
 * @property string|null $cancellation_reason
 * @property int $payment_attempt_count
 * @property string|null $last_payment_attempt_at
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, Invoice> $invoices
 * @property-read int|null $invoices_count
 * @property-read \App\Models\Plan $plan
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\SubscriptionLog> $subscriptionLogs
 * @property-read int|null $subscription_logs_count
 * @property-read \App\Models\Tenant $tenant
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Transaction> $transactions
 * @property-read int|null $transactions_count
 *
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Subscription active()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Subscription cancelled()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Subscription ended()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Subscription new()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Subscription newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Subscription newOrActive()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Subscription newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Subscription pause()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Subscription query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Subscription terminated()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Subscription trial()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Subscription whereCanceledAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Subscription whereCancellationReason($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Subscription whereCancelledAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Subscription whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Subscription whereCurrentPeriodEndsAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Subscription whereEndedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Subscription whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Subscription whereIsRecurring($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Subscription whereLastPaymentAttemptAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Subscription wherePaymentAttemptCount($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Subscription wherePlanId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Subscription whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Subscription whereTenantId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Subscription whereTerminatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Subscription whereTrialEndsAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Subscription whereTrialStartsAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Subscription whereUpdatedAt($value)
 *
 * @mixin \Eloquent
 */
class Subscription extends BaseModel
{
    use HasFactory;

    // Subscription statuses
    public const STATUS_NEW = 'new';        // Initial state, not yet paid

    public const STATUS_ACTIVE = 'active';     // Active subscription

    public const STATUS_ENDED = 'ended';      // Subscription that reached end date

    public const STATUS_CANCELLED = 'cancelled';  // Customer cancelled

    public const STATUS_TERMINATED = 'terminated'; // Admin forcefully terminated

    public const STATUS_TRIAL = 'trial';      // Trial period

    public const STATUS_PAUSED = 'paused';     // Paused subscription

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'tenant_id',
        'plan_id',
        'status',
        'current_period_ends_at',
        'trial_starts_at',
        'trial_ends_at',
        'is_recurring',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'current_period_ends_at' => 'datetime',
        'trial_ends_at' => 'datetime',
        'cancelled_at' => 'datetime',
        'ended_at' => 'datetime',
        'terminated_at' => 'datetime',
        'is_recurring' => 'boolean',
    ];

    /**
     * The relationships that should be eager loaded.
     *
     * @var array
     */
    protected $with = ['plan', 'tenant'];

    /**
     * Get the plan that owns the subscription.
     */
    public function plan(): BelongsTo
    {
        return $this->belongsTo(Plan::class);
    }

    /**
     * Get the customer that owns the subscription.
     */
    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    /**
     * Get related invoices.
     */
    public function invoices(): HasMany
    {
        return $this->hasMany(Invoice::class);
    }

    /**
     * Get subscription logs.
     */
    public function subscriptionLogs(): HasMany
    {
        return $this->hasMany(SubscriptionLog::class);
    }

    /**
     * Get related transactions.
     */
    public function transactions(): HasManyThrough
    {
        return $this->hasManyThrough(
            Transaction::class,
            Invoice::class,
            'subscription_id',
            'invoice_id'
        );
    }

    /**
     * Scope a query to only include new subscriptions.
     */
    public function scopeNew($query)
    {
        return $query->where('status', self::STATUS_NEW);
    }

    /**
     * Scope a query to only include active subscriptions.
     */
    public function scopeActive($query)
    {
        return $query->where('status', self::STATUS_ACTIVE);
    }

    /**
     * Scope a query to only include ended subscriptions.
     */
    public function scopeEnded($query)
    {
        return $query->where('status', self::STATUS_ENDED);
    }

    /**
     * Scope a query to only include cancelled subscriptions.
     */
    public function scopeCancelled($query)
    {
        return $query->where('status', self::STATUS_CANCELLED);
    }

    /**
     * Scope a query to only include terminated subscriptions.
     */
    public function scopeTerminated($query)
    {
        return $query->where('status', self::STATUS_TERMINATED);
    }

    /**
     * Scope a query to only include trial subscriptions.
     */
    public function scopeTrial($query)
    {
        return $query->where('status', self::STATUS_TRIAL);
    }

    /**
     * Scope a query to only include pause subscriptions.
     */
    public function scopePause($query)
    {
        return $query->where('status', self::STATUS_PAUSED);
    }

    /**
     * Scope a query to include new or active subscriptions.
     */
    public function scopeNewOrActive($query)
    {
        return $query->whereIn('status', [self::STATUS_NEW, self::STATUS_ACTIVE]);
    }

    /**
     * Check if subscription is new.
     */
    public function isNew(): bool
    {
        return $this->status === self::STATUS_NEW;
    }

    /**
     * Check if subscription is active.
     */
    public function isActive(): bool
    {
        return $this->status === self::STATUS_ACTIVE;
    }

    /**
     * Check if subscription is ended.
     */
    public function isEnded(): bool
    {
        return $this->status === self::STATUS_ENDED;
    }

    /**
     * Check if subscription is cancelled.
     */
    public function isCancelled(): bool
    {
        return $this->status === self::STATUS_CANCELLED;
    }

    /**
     * Check if subscription is terminated.
     */
    public function isTerminated(): bool
    {
        return $this->status === self::STATUS_TERMINATED;
    }

    /**
     * Check if subscription is terminated.
     */
    public function isTrial(): bool
    {
        return $this->status === self::STATUS_TRIAL;
    }

    /**
     * Check if subscription is terminated.
     */
    public function isPause(): bool
    {
        return $this->status === self::STATUS_PAUSED;
    }

    /**
     * Activate a subscription.
     *
     * @throws Exception
     */
    public function activate(): self
    {
        if (! $this->isNew()) {
            throw new Exception('Only new subscriptions can be activated.');
        }

        $this->status = self::STATUS_ACTIVE;
        $this->save();

        // Add subscription log
        $this->addLog('activated', [
            'plan' => $this->plan->name,
            'price' => $this->plan->price,
        ]);

        // Fire event
        event(new SubscriptionActivated($this));

        return $this;
    }

    /**
     * End a subscription.
     *
     * @throws Exception
     */
    public function end(): self
    {
        if (! $this->isActive()) {
            throw new Exception('Only active subscriptions can be ended.');
        }

        $this->status = self::STATUS_ENDED;
        $this->ended_at = now();
        $this->save();

        // Reset feature usage
        app(FeatureService::class)->resetUsage($this->tenant_id);

        // Add subscription log
        $this->addLog('ended', [
            'plan' => $this->plan->name,
            'end_date' => $this->ended_at->format('Y-m-d H:i:s'),
        ]);

        return $this;
    }

    /**
     * Cancel a subscription.
     *
     * @throws Exception
     */
    public function cancel(): self
    {
        if (! $this->isActive() && ! $this->isPause()) {
            throw new Exception('Only active subscriptions can be cancelled.');
        }

        $this->status = self::STATUS_CANCELLED;
        $this->cancelled_at = now();
        $this->save();

        // Reset feature usage
        app(FeatureService::class)->resetUsage($this->tenant_id);

        // Add subscription log
        $this->addLog('cancelled', [
            'plan' => $this->plan->name,
            'cancel_date' => $this->cancelled_at->format('Y-m-d H:i:s'),
        ]);

        // Fire event
        event(new SubscriptionCancelled($this->id));

        return $this;
    }

    /**
     * Terminate a subscription (admin action).
     *
     * @throws Exception
     */
    public function terminate(): self
    {
        if (! $this->isActive() && ! $this->isNew()) {
            throw new Exception('Only active or new subscriptions can be terminated.');
        }

        $this->status = self::STATUS_TERMINATED;
        $this->terminated_at = now();
        $this->save();

        // Reset feature usage
        app(FeatureService::class)->resetUsage($this->tenant_id);

        // Add subscription log
        $this->addLog('terminated', [
            'plan' => $this->plan->name,
            'terminate_date' => $this->terminated_at->format('Y-m-d H:i:s'),
        ]);

        return $this;
    }

    /**
     * Calculate the end date of a period from a given start date.
     */
    public function getPeriodEndsAt($startDate): Carbon
    {
        return $this->calculatePeriodEnd($startDate, $this->plan->billing_period, $this->plan->interval);
    }

    /**
     * Calculate trial period end date.
     */
    public function getTrialPeriodEndsAt($startDate): ?Carbon
    {
        if (! $this->plan->trial_period) {
            return null;
        }

        $settings = get_batch_settings(['system.timezone']);
        $timezone = $settings['system.timezone'] ?? config('app.timezone');

        return Carbon::parse($startDate, $timezone)->addDays($this->plan->trial_period);
    }

    /**
     * Get auto billing date (usually some days before expiration).
     */
    public function getAutoBillingDate(): Carbon
    {
        $settings = get_batch_settings(['system.timezone']);
        $timezone = $settings['system.timezone'] ?? config('app.timezone');

        return Carbon::parse($this->current_period_ends_at, $timezone)->addDays(1);
    }

    /**
     * Check if current date is within billing period.
     */
    public function isBillingPeriod(): bool
    {
        if (! $this->current_period_ends_at) {
            return false;
        }

        $settings = get_batch_settings(['system.timezone']);
        $timezone = $settings['system.timezone'] ?? config('app.timezone');

        $billingDate = $this->getAutoBillingDate();

        return Carbon::now($timezone)->gte($billingDate);
    }

    /**
     * Calculate the end date based on billing_period and interval.
     */
    protected function calculatePeriodEnd($startDate, $billing_period, $interval): Carbon
    {
        $settings = get_batch_settings(['system.timezone']);
        $timezone = $settings['system.timezone'] ?? config('app.timezone');

        $date = Carbon::parse($startDate, $timezone);

        return match ($billing_period) {
            'monthly' => $date->addMonths((int) $interval),
            'yearly' => $date->addYears((int) $interval),
            default => throw new Exception("Unsupported billing_period: {$billing_period}"),
        };
    }

    /**
     * Check if subscription has recurring billing enabled.
     */
    public function isRecurring(): bool
    {
        return (bool) $this->is_recurring;
    }

    /**
     * Disable recurring billing.
     *
     * @throws Exception
     */
    public function disableRecurring(): self
    {
        if (! in_array($this->status, [self::STATUS_ACTIVE, self::STATUS_ENDED])) {
            throw new Exception('Only active or ended subscriptions can have recurring billing disabled.');
        }

        $this->is_recurring = false;

        // Optionally pause if currently active
        if ($this->isActive()) {
            $this->status = self::STATUS_PAUSED;
        }

        $this->save();

        $this->addLog('recurring_disabled', [
            'plan' => $this->plan->name,
        ]);

        return $this;
    }

    /**
     * Enable recurring billing.
     *
     * @throws Exception
     */
    public function enableRecurring(): self
    {
        if (! $this->status = self::STATUS_ACTIVE) {
            throw new Exception('Only paused or ended subscriptions can have recurring billing enabled.');
        }

        $this->is_recurring = true;
        if ($this->status = self::STATUS_PAUSED || $this->current_period_ends_at < now()) {
            $this->status = self::STATUS_ACTIVE;
        }
        $this->save();

        $this->addLog('recurring_enabled', [
            'plan' => $this->plan->name,
        ]);

        return $this;
    }

    /**
     * Get unpaid new subscription invoice.
     *
     * @throws Exception
     */
    public function getUnpaidInitInvoice(): ?Invoice
    {

        if (! $this->isNew()) {
            throw new Exception('Method getUnpaidInitInvoice() only for NEW subscription');
        }

        return $this->invoices()
            ->where('type', InvoiceNewSubscription::TYPE_NEW_SUBSCRIPTION)
            ->where('status', Invoice::STATUS_NEW)
            ->first();
    }

    /**
     * Get unpaid change plan invoice.
     *
     * @throws Exception
     */
    public function getUnpaidChangePlanInvoice(): ?Invoice
    {
        if (! $this->isActive() && ! $this->isPause()) {
            throw new Exception('Method getUnpaidChangePlanInvoice() only for ACTIVE subscription');
        }

        return $this->invoices()
            ->where('type', InvoiceChangePlan::TYPE_CHANGE_PLAN)
            ->where('status', Invoice::STATUS_NEW)
            ->first();
    }

    /**
     * Get unpaid renewal invoice.
     *
     * @throws Exception
     */
    public function getUnpaidRenewInvoice()
    {
        return $this->invoices()
            ->where('type', InvoiceRenewSubscription::TYPE_RENEW_SUBSCRIPTION)
            ->where('status', Invoice::STATUS_NEW)
            ->first();
    }

    /**
     * Create initial subscription invoice.
     */
    public function createInitInvoice($plan): Invoice
    {
        $this->plan = $plan;
        $date = $this->current_period_ends_at ?? $this->trial_ends_at;
        $endDate = $date?->format('F j, Y');

        // Create the invoice first
        $invoiceData = [
            'tenant_id' => $this->tenant_id,
            'subscription_id' => $this->id,
            'type' => InvoiceNewSubscription::TYPE_NEW_SUBSCRIPTION,
            'status' => Invoice::STATUS_NEW,
            'title' => 'New Subscription',
            'description' => "Subscription to {$this->plan->name} plan until {$endDate}",
            'currency_id' => $this->plan->currency_id,
        ];

        $invoice = InvoiceNewSubscription::create($invoiceData);

        // Add invoice item
        $itemData = [
            'title' => $this->plan->name,
            'description' => "Subscription to {$this->plan->name} plan",
            'amount' => $this->plan->price,
            'quantity' => 1,
        ];

        $invoice->items()->create($itemData);

        // Apply taxes
        $invoice->applyTaxes();

        // Set the invoice status based on subscription status AFTER creation
        if ($this->status === self::STATUS_TRIAL) {
            $invoice->status = Invoice::STATUS_PAID;
            $invoice->save();
            $invoice->createInvoiceNumber();
        }

        return $invoice;
    }

    /**
     * Create renewal invoice.
     */
    public function createRenewInvoice(): ?Invoice
    {
        // Check if already has unpaid renewal invoice
        if ($this->getUnpaidRenewInvoice()) {
            return null;
        }

        // Calculate next period
        $nextPeriodEnd = $this->getPeriodEndsAt($this->current_period_ends_at);

        $invoice = InvoiceRenewSubscription::create([
            'tenant_id' => $this->tenant_id,
            'subscription_id' => $this->id,
            'type' => InvoiceRenewSubscription::TYPE_RENEW_SUBSCRIPTION,
            'status' => Invoice::STATUS_NEW,
            'title' => 'Subscription Renewal',
            'description' => "Renewal of {$this->plan->name} plan until {$nextPeriodEnd->format('Y-m-d H:i:s')}",
            'currency_id' => $this->plan->currency_id,
        ]);

        // Add invoice item
        $invoice->items()->create([
            'title' => $this->plan->name,
            'description' => "Renewal of {$this->plan->name} plan",
            'amount' => $this->plan->price,
            'quantity' => 1,
        ]);

        // Apply taxes
        $invoice->applyTaxes();

        return $invoice;
    }

    /**
     * Change subscription plan.
     */
    public function changePlan(Plan $newPlan): Invoice
    {
        if (! $this->isActive() && ! $this->isPause()) {
            throw new Exception('Only active subscriptions can change plans.');
        }

        // Check if already has unpaid change plan invoice
        if ($this->getUnpaidChangePlanInvoice()) {
            throw new Exception('Subscription already has a pending plan change invoice.');
        }

        // Calculate proration if needed
        $proration = $this->calculatePlanChangeProration($newPlan);

        // Ensure we have a valid currency_id
        $currencyId = $newPlan->currency_id;

        // If the plan doesn't have a valid currency_id, use the default one from the database
        if (! $currencyId) {
            $defaultCurrency = \App\Models\Currency::getDefault();
            if ($defaultCurrency) {
                $currencyId = $defaultCurrency->id;
            } else {
                throw new Exception('No valid currency found for invoice creation.');
            }
        }

        // Create change plan invoice
        $invoice = InvoiceChangePlan::create([
            'tenant_id' => $this->tenant_id,
            'subscription_id' => $this->id,
            'type' => InvoiceChangePlan::TYPE_CHANGE_PLAN,
            'status' => Invoice::STATUS_NEW,
            'title' => 'Plan Change',
            'description' => "Change from {$this->plan->name} to {$newPlan->name} plan",
            'currency_id' => $currencyId,
        ]);

        // Set the target plan ID
        if ($invoice instanceof InvoiceChangePlan) {
            $invoice->setPlanId($newPlan->id);
        }

        // Add invoice item for the new plan
        $invoice->items()->create([
            'title' => $newPlan->name,
            'description' => "Change to {$newPlan->name} plan",
            'amount' => $proration['new_plan_price'],
            'quantity' => 1,
        ]);

        // Add proration credit if applicable
        if ($proration['credit_amount'] != 0) {
            $invoice->items()->create([
                'title' => 'Proration Credit',
                'description' => "Credit for unused portion of {$this->plan->name} plan",
                'amount' => -$proration['credit_amount'],
                'quantity' => 1,
            ]);
        }

        // Apply taxes
        $invoice->applyTaxes();

        return $invoice;
    }

    /**
     * Calculate proration for plan change.
     */
    protected function calculatePlanChangeProration(Plan $newPlan, bool $overrideExisting = false): array
    {
        // Only prorate if current plan and new plan have same currency
        if ($this->plan->currency_id != $newPlan->currency_id) {
            return [
                'new_plan_price' => $newPlan->price,
                'credit_amount' => 0,
            ];
        }

        $now = Carbon::now();

        // Get total days in the billing period
        $periodStart = $this->created_at;
        $totalDays = (int) $periodStart->diffInDays($this->current_period_ends_at);

        // Calculate remaining days from now until period end
        $daysRemaining = (int) $now->diffInDays($this->current_period_ends_at);
        $invoice = $this->invoices()
            ->where('tenant_id', $this->tenant_id)
            ->orderBy('id', 'desc')
            ->first();

        $invoiceItem = $invoice?->items()
            ->where('invoice_id', $invoice->id)
            ->orderBy('id', 'desc')
            ->first();

        $total_price = $invoiceItem['amount'] + $invoice->total_tax_amount;
        // Calculate daily rate for current plan
        $dailyRate = $total_price / $totalDays;

        // Calculate credit for unused days
        $creditAmount = round($dailyRate * $daysRemaining, 2);

        // New plan price remains unchanged
        $newPlanPrice = $newPlan->price;

        if (! $overrideExisting) {
            $balance = TenantCreditBalance::addCredit(
                $this->tenant_id,
                $newPlan->currency_id,
                $creditAmount,
                "Proration credit for plan change from {$this->plan->name} to {$newPlan->name}",
                $invoice?->id,
                [
                    'source' => 'plan_change_proration',
                    'old_plan_id' => $this->plan_id,
                    'new_plan_id' => $newPlan->id,
                ]
            );
        }

        return [
            'new_plan_price' => $newPlanPrice,
            'credit_amount' => $balance->balance ?? 0,
        ];
    }

    /**
     * Update subscription to new plan after payment.
     */
    public function applyPlanChange(Plan $newPlan): self
    {
        $oldPlanId = $this->plan_id;
        $oldPlanName = $this->plan->name;

        // Update subscription
        $this->plan_id = $newPlan->id;
        $this->current_period_ends_at = $this->getPeriodEndsAt(Carbon::now());
        $this->save();

        // Sync feature counts instead of resetting
        app(FeatureService::class)->syncAllFeatureCounts($this->tenant_id);

        // Add subscription log
        $this->addLog('plan_changed', [
            'from_plan_id' => $oldPlanId,
            'from_plan' => $oldPlanName,
            'to_plan_id' => $newPlan->id,
            'to_plan' => $newPlan->name,
        ]);

        // Fire event
        event(new SubscriptionPlanChanged($this, $newPlan));

        return $this;
    }

    /**
     * Upgrade subscription to a higher-tier plan.
     *
     * @param  Plan  $newPlan  Higher-tier plan
     * @param  bool  $overrideExisting  Whether to override any existing pending plan change invoice
     *
     * @throws Exception
     */
    public function upgradePlan(Plan $newPlan, bool $overrideExisting = false): Invoice
    {
        if (! $this->isActive() && ! $this->isPause()) {
            throw new Exception('Only active subscriptions can be upgraded.');
        }

        if ($newPlan->price <= $this->plan->price) {
            throw new Exception('New plan must have a higher price to be considered an upgrade.');
        }

        // Check if already has unpaid change plan invoice
        $pendingInvoice = $this->getUnpaidChangePlanInvoice();
        if ($pendingInvoice) {
            if ($overrideExisting) {
                // Cancel and delete the existing pending invoice
                $pendingInvoice->status = Invoice::STATUS_CANCELLED;
                $pendingInvoice->cancelled_at = now();
                $pendingInvoice->save();

                // Add log entry for cancellation
                $this->addLog('plan_change_cancelled', [
                    'invoice_id' => $pendingInvoice->id,
                    'reason' => 'Replaced by new plan change request',
                ]);
            } else {
                throw new Exception('Subscription already has a pending plan change invoice.');
            }
        }

        // Calculate proration if needed
        $proration = $this->calculatePlanChangeProration($newPlan, $overrideExisting);

        // Ensure we have a valid currency_id
        $currencyId = $newPlan->currency_id;

        // If the plan doesn't have a valid currency_id, use the default one from the database
        if (! $currencyId) {
            $defaultCurrency = \App\Models\Currency::getDefault();
            if ($defaultCurrency) {
                $currencyId = $defaultCurrency->id;
            } else {
                throw new Exception('No valid currency found for invoice creation.');
            }
        }

        // Create upgrade invoice
        $invoice = InvoiceChangePlan::create([
            'tenant_id' => $this->tenant_id,
            'subscription_id' => $this->id,
            'type' => InvoiceChangePlan::TYPE_CHANGE_PLAN,
            'status' => Invoice::STATUS_NEW,
            'title' => 'Plan Upgrade',
            'description' => "Upgrade from {$this->plan->name} to {$newPlan->name} plan",
            'currency_id' => $currencyId,
        ]);

        // Set the target plan ID and action type
        if ($invoice instanceof InvoiceChangePlan) {
            $invoice->setPlanId($newPlan->id)
                ->setAction(InvoiceChangePlan::ACTION_UPGRADE);
        }

        // Add invoice item for the new plan
        $invoice->items()->create([
            'title' => $newPlan->name,
            'description' => "Upgrade to {$newPlan->name} plan",
            'amount' => $proration['new_plan_price'],
            'quantity' => 1,
        ]);

        $tax = $invoice->applyTaxes();
        $totalTax = collect($tax)->sum('amount');
        $totalNewPlanPrice = $proration['new_plan_price'] + $totalTax;

        if ($totalNewPlanPrice <= $proration['credit_amount']) {
            TenantCreditBalance::deductCredit(
                $this->tenant_id,
                $totalNewPlanPrice,
                'Credit applied for plan downgrade'.get_base_currency()->symbol.' '.$totalNewPlanPrice,
                $invoice->id
            );
            $invoice->markAsPaid();
            event(new InvoicePaid($invoice));
            $this->applyPlanUpgrade($newPlan);
        }

        return $invoice;
    }

    /**
     * Downgrade subscription to a lower-tier plan.
     *
     * @param  Plan  $newPlan  Lower-tier plan
     * @param  bool  $overrideExisting  Whether to override any existing pending plan change invoice
     *
     * @throws Exception
     */
    public function downgradePlan(Plan $newPlan, bool $overrideExisting = false): Invoice
    {
        if (! $this->isActive() && ! $this->isPause()) {
            throw new Exception('Only active subscriptions can be downgraded.');
        }

        if ($newPlan->price >= $this->plan->price) {
            throw new Exception('New plan must have a lower price to be considered a downgrade.');
        }

        // Check if already has unpaid change plan invoice
        $pendingInvoice = $this->getUnpaidChangePlanInvoice();
        if ($pendingInvoice) {
            if ($overrideExisting) {
                // Cancel and delete the existing pending invoice
                $pendingInvoice->status = Invoice::STATUS_CANCELLED;
                $pendingInvoice->cancelled_at = now();
                $pendingInvoice->save();

                // Add log entry for cancellation
                $this->addLog('plan_change_cancelled', [
                    'invoice_id' => $pendingInvoice->id,
                    'reason' => 'Replaced by new plan change request',
                ]);
            } else {
                throw new Exception('Subscription already has a pending plan change invoice.');
            }
        }

        // Calculate proration if needed
        $proration = $this->calculatePlanChangeProration($newPlan, $overrideExisting);

        // Ensure we have a valid currency_id
        $currencyId = $newPlan->currency_id;

        // If the plan doesn't have a valid currency_id, use the default one from the database
        if (! $currencyId) {
            $defaultCurrency = \App\Models\Currency::getDefault();
            if ($defaultCurrency) {
                $currencyId = $defaultCurrency->id;
            } else {
                throw new Exception('No valid currency found for invoice creation.');
            }
        }

        // Create downgrade invoice with PAID status
        $invoice = InvoiceChangePlan::create([
            'tenant_id' => $this->tenant_id,
            'subscription_id' => $this->id,
            'type' => InvoiceChangePlan::TYPE_CHANGE_PLAN,
            'status' => Invoice::STATUS_NEW,
            'title' => 'Plan Downgrade',
            'description' => "Downgrade from {$this->plan->name} to {$newPlan->name} plan",
            'currency_id' => $currencyId,
        ]);

        // Set the target plan ID and action type
        if ($invoice instanceof InvoiceChangePlan) {
            $invoice->setPlanId($newPlan->id)
                ->setAction(InvoiceChangePlan::ACTION_DOWNGRADE);
        }

        // Add invoice item for the new plan
        $invoice->items()->create([
            'title' => $newPlan->name,
            'description' => "Downgrade to {$newPlan->name} plan",
            'amount' => $proration['new_plan_price'],
            'quantity' => 1,
        ]);

        // Apply taxes
        $tax = $invoice->applyTaxes();
        $totalTax = collect($tax)->sum('amount');
        $totalNewPlanPrice = $proration['new_plan_price'] + $totalTax;

        if ($totalNewPlanPrice <= $proration['credit_amount']) {
            TenantCreditBalance::deductCredit(
                $this->tenant_id,
                $totalNewPlanPrice,
                'Credit applied for plan downgrade'.get_base_currency()->symbol.' '.$totalNewPlanPrice,
                $invoice->id
            );
            $totalNewPlanPrice = 0;
            $invoice->markAsPaid();
            event(new InvoicePaid($invoice));
            $this->applyPlanDowngrade($newPlan);
        }

        return $invoice;
    }

    /**
     * Apply plan upgrade after payment.
     */
    public function applyPlanUpgrade(Plan $newPlan): self
    {
        $oldPlanId = $this->plan_id;
        $oldPlanName = $this->plan->name;

        // Update subscription
        $this->plan_id = $newPlan->id;
        $this->current_period_ends_at = $this->getPeriodEndsAt(Carbon::now());
        $this->save();

        // Sync feature counts instead of resetting
        app(FeatureService::class)->syncAllFeatureCounts($this->tenant_id);

        // Add subscription log
        $this->addLog('plan_upgraded', [
            'from_plan_id' => $oldPlanId,
            'from_plan' => $oldPlanName,
            'to_plan_id' => $newPlan->id,
            'to_plan' => $newPlan->name,
        ]);

        // Fire event
        event(new SubscriptionUpgraded($this, $oldPlanId));

        return $this;
    }

    /**
     * Apply plan downgrade after payment.
     */
    public function applyPlanDowngrade(Plan $newPlan): self
    {
        if (! $this->isActive() && ! $this->isPause()) {
            throw new Exception('Only active subscriptions can be downgraded.');
        }

        $oldPlanId = $this->plan_id;
        $oldPlanName = $this->plan->name;

        // Update subscription details
        $this->plan_id = $newPlan->id;
        $this->current_period_ends_at = $this->getPeriodEndsAt(Carbon::now());
        $this->save();

        // Sync feature counts instead of resetting
        app(FeatureService::class)->syncAllFeatureCounts($this->tenant_id);

        // Add subscription log for the downgrade
        $this->addLog('plan_downgraded', [
            'from_plan_id' => $oldPlanId,
            'from_plan' => $oldPlanName,
            'to_plan_id' => $newPlan->id,
            'to_plan' => $newPlan->name,
            'period_ends_at' => $this->current_period_ends_at->format('Y-m-d H:i:s'),
        ]);

        // Fire event
        event(new SubscriptionDowngraded($this, $oldPlanId));

        return $this;
    }

    /**
     * Check if subscription is expired.
     */
    public function isExpired(): bool
    {
        if (! $this->current_period_ends_at) {
            return false;
        }

        $settings = get_batch_settings(['system.timezone']);
        $timezone = $settings['system.timezone'] ?? config('app.timezone');

        return Carbon::now($timezone)->gt($this->current_period_ends_at);
    }

    /**
     * End subscription if expired.
     */
    public function endIfExpired(): bool
    {
        if ($this->isActive() && $this->isExpired()) {
            $this->end();

            return true;
        }

        return false;
    }

    /**
     * Check if subscription is approaching expiration.
     */
    public function isExpiring(int $days = 3): bool
    {
        if (! $this->current_period_ends_at) {
            return false;
        }

        $settings = get_batch_settings(['system.timezone']);
        $timezone = $settings['system.timezone'] ?? config('app.timezone');

        $expiryThreshold = Carbon::now($timezone)->addDays($days);

        return Carbon::now($timezone)->lt($this->current_period_ends_at) && $expiryThreshold->gte($this->current_period_ends_at);
    }

    /**
     * Check and create renewal invoice if needed.
     */
    public function checkAndCreateRenewInvoice(): ?Invoice
    {
        // Only create renewal invoices for active subscriptions with recurring enabled and status not paused
        if ($this->isActive() || $this->isPause() || ! $this->isRecurring()) {
            return null;
        }

        // Check if already has unpaid renewal invoice
        if ($this->getUnpaidRenewInvoice()) {
            return null;
        }

        return $this->createRenewInvoice();
    }

    /**
     * Renew the subscription.
     */
    public function renew(): self
    {
        // Calculate next period
        $this->status = self::STATUS_ACTIVE; // Set status to NEW for renewal
        $this->current_period_ends_at = $this->getPeriodEndsAt($this->current_period_ends_at);
        $this->save();

        // Add subscription log
        $this->addLog('renewed', [
            'plan' => $this->plan->name,
            'end_date' => $this->current_period_ends_at->format('Y-m-d H:i:s'),
        ]);

        // Fire event
        event(new SubscriptionRenewed($this));

        return $this;
    }

    /**
     * Add subscription log.
     */
    public function addLog(string $type, array $data = [], ?int $transactionId = null): SubscriptionLog
    {
        $log = new SubscriptionLog([
            'subscription_id' => $this->id,
            'type' => $type,
            'data' => json_encode($data),
        ]);

        if ($transactionId) {
            $log->transaction_id = $transactionId;
        }

        $log->save();

        return $log;
    }

    /**
     * Get subscription logs.
     */
    public function getLogs()
    {
        return $this->subscriptionLogs()
            ->orderBy('created_at', 'desc')
            ->get();
    }

    /**
     * Create a new subscription.
     */
    public static function createNewSubscription(Tenant $tenant, Plan $plan): self
    {
        // Find existing subscription by tenant_id
        $subscription = self::where('tenant_id', $tenant->id)->first();

        if ($subscription) {
            // dd($subscription);
            // Update existing subscription
            $oldPlanId = $subscription->plan_id;
            $subscription->plan_id = $plan->id;
            $subscription->status = self::STATUS_NEW;
            $subscription->is_recurring = true;
            $subscription->trial_starts_at = null;
            $subscription->trial_ends_at = null;
            $subscription->cancelled_at = null; // Reset cancelled_at when updating

            // dd($existingInvoice);
            $existingInvoice = $subscription->getUnpaidInitInvoice();
            if ($existingInvoice && $oldPlanId !== $plan->id) {
                $subscription->updateExistingInvoice($existingInvoice, $plan);
            }

            $existsRenewaPlan = $subscription->getUnpaidRenewInvoice();
            if ($existsRenewaPlan && $oldPlanId !== $plan->id) {
                $subscription->updateExistingInvoice($existsRenewaPlan, $plan);
            }
        } else {
            // Create new subscription
            $subscription = new self([
                'tenant_id' => $tenant->id,
                'plan_id' => $plan->id,
                'status' => self::STATUS_NEW,
                'is_recurring' => true,
            ]);
        }

        $subscription->current_period_ends_at = $subscription->getPeriodEndsAt(Carbon::now());
        // Save (create or update)
        $subscription->save();

        // Log creation or update action
        $subscription->addLog($subscription->wasRecentlyCreated ? 'created' : 'updated', [
            'plan' => $plan->name,
            'price' => $plan->price,
        ]);

        // Create initial invoice only for new subscriptions
        if (! $subscription->getUnpaidInitInvoice() && ! $subscription->getUnpaidRenewInvoice()) {
            $subscription->createInitInvoice($plan);
        }

        event(new SubscriptionCreated($subscription));

        return $subscription;
    }

    /**
     * Update existing unpaid invoice with new plan details.
     */
    public function updateExistingInvoice(Invoice $invoice, Plan $newPlan): Invoice
    {
        // Only update if invoice is in NEW status (unpaid)
        if ($invoice->status !== Invoice::STATUS_NEW) {
            // If invoice is not updatable, create a new one
            return $this->createInitInvoice($newPlan);
        }

        // Update invoice details
        $date = $this->current_period_ends_at ?? $this->trial_ends_at;
        $endDate = $date?->format('F j, Y');

        $invoice->update([
            'title' => 'New Subscription',
            'description' => "Subscription to {$newPlan->name} plan until {$endDate}",
            'currency_id' => $newPlan->currency_id,
        ]);

        // Clear existing invoice items
        $invoice->items()->delete();

        // Add new invoice item with updated plan
        $invoice->items()->create([
            'title' => $newPlan->name,
            'description' => "Subscription to {$newPlan->name} plan",
            'amount' => $newPlan->price,
            'quantity' => 1,
        ]);

        // Clear and reapply taxes for new amount
        $invoice->taxes()->delete();
        $invoice->applyTaxes();

        // Log the plan change
        $this->addLog('invoice_updated', [
            'old_plan' => $this->plan->name ?? 'Unknown',
            'new_plan' => $newPlan->name,
            'invoice_id' => $invoice->id,
        ]);

        return $invoice;
    }

    protected static function booted()
    {
        static::creating(function ($subscription) {
            do_action('subscription.before_create', $subscription);
        });

        // Reset feature usage when subscription changes
        static::created(function ($subscription) {
            do_action('subscription.after_create', $subscription);
            app(FeatureService::class)->resetUsage($subscription->tenant_id);
        });

        static::updating(function ($subscription) {
            do_action('subscription.before_update', $subscription);

            $statusChanged = $subscription->isDirty('status');
            $planChanged = $subscription->isDirty('plan_id');

            if ($statusChanged) {
                $oldStatus = $subscription->getOriginal('status');
                $newStatus = $subscription->status;

                // Fire specific hooks for status changes
                if ($oldStatus !== self::STATUS_CANCELLED && $newStatus === self::STATUS_CANCELLED) {
                    do_action('subscription.before_cancel', $subscription);
                } elseif ($oldStatus !== self::STATUS_ACTIVE && $newStatus === self::STATUS_ACTIVE) {
                    do_action('subscription.before_renew', $subscription);
                }
            }

            if ($planChanged) {
                $oldPlan = $subscription->getOriginal('plan_id');
                $newPlan = $subscription->plan_id;

                // Determine if it's an upgrade or downgrade
                $oldPlanModel = Plan::find($oldPlan);
                $newPlanModel = Plan::find($newPlan);

                if ($oldPlanModel && $newPlanModel) {
                    if ($newPlanModel->price > $oldPlanModel->price) {
                        do_action('subscription.before_upgrade', $subscription);
                    } elseif ($newPlanModel->price < $oldPlanModel->price) {
                        do_action('subscription.before_downgrade', $subscription);
                    }
                }
            }
        });

        // Reset usage when subscription status or plan changes
        static::updated(function ($subscription) {
            do_action('subscription.after_update', $subscription);

            $statusChanged = $subscription->isDirty('status');
            $planChanged = $subscription->isDirty('plan_id');

            if ($statusChanged) {
                $oldStatus = $subscription->getOriginal('status');
                $newStatus = $subscription->status;

                // Fire specific hooks for status changes
                if ($oldStatus !== self::STATUS_CANCELLED && $newStatus === self::STATUS_CANCELLED) {
                    do_action('subscription.after_cancel', $subscription);
                } elseif ($oldStatus !== self::STATUS_ACTIVE && $newStatus === self::STATUS_ACTIVE) {
                    do_action('subscription.after_renew', $subscription);
                }

                // Skip reset if new status is 'paused'
                if ($newStatus === 'paused') {
                    return;
                }

                // Only reset usage on subscription termination, not plan changes
                if (in_array($newStatus, ['cancelled', 'ended', 'terminated'])) {
                    app(FeatureService::class)->resetUsage($subscription->tenant_id);
                }
            }

            // Sync actual counts when plan changes (don't reset to 0)
            if ($planChanged) {
                $oldPlan = $subscription->getOriginal('plan_id');
                $newPlan = $subscription->plan_id;

                // Determine if it's an upgrade or downgrade
                $oldPlanModel = Plan::find($oldPlan);
                $newPlanModel = Plan::find($newPlan);

                if ($oldPlanModel && $newPlanModel) {
                    if ($newPlanModel->price > $oldPlanModel->price) {
                        do_action('subscription.after_upgrade', $subscription);
                    } elseif ($newPlanModel->price < $oldPlanModel->price) {
                        do_action('subscription.after_downgrade', $subscription);
                    }
                }

                app(FeatureService::class)->syncAllFeatureCounts($subscription->tenant_id);
            }
        });

        // Also reset when subscription ends/cancels/expires
        static::deleting(function ($subscription) {
            do_action('subscription.before_delete', $subscription);
            app(FeatureService::class)->resetUsage($subscription->tenant_id);
        });

        static::deleted(function ($subscription) {
            do_action('subscription.after_delete', $subscription);
        });
    }
}
