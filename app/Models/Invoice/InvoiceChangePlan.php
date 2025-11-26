<?php

namespace App\Models\Invoice;

use App\Models\Plan;
use App\Services\SubscriptionCache;
use Exception;
use Illuminate\Database\Eloquent\Relations\HasMany;

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
 * @property-read \App\Models\Currency $currency
 * @property-read string $formatted_total
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Invoice\InvoiceItem> $items
 * @property-read int|null $items_count
 * @property-read \App\Models\Subscription|null $subscription
 * @property-read \App\Models\Tenant $tenant
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Transaction> $transactions
 * @property-read int|null $transactions_count
 *
 * @method static \Illuminate\Database\Eloquent\Builder<static>|InvoiceChangePlan changePlan()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|InvoiceChangePlan newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|InvoiceChangePlan newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|InvoiceChangePlan newSubscription()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|InvoiceChangePlan paid()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|InvoiceChangePlan query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|InvoiceChangePlan renewSubscription()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|InvoiceChangePlan unpaid()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|InvoiceChangePlan whereCancelledAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|InvoiceChangePlan whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|InvoiceChangePlan whereCurrencyId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|InvoiceChangePlan whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|InvoiceChangePlan whereDueDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|InvoiceChangePlan whereFee($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|InvoiceChangePlan whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|InvoiceChangePlan whereInvoiceNumber($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|InvoiceChangePlan whereMetadata($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|InvoiceChangePlan whereNoPaymentRequiredWhenFree($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|InvoiceChangePlan wherePaidAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|InvoiceChangePlan whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|InvoiceChangePlan whereSubscriptionId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|InvoiceChangePlan whereTaxPercent($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|InvoiceChangePlan whereTenantId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|InvoiceChangePlan whereTitle($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|InvoiceChangePlan whereType($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|InvoiceChangePlan whereUpdatedAt($value)
 *
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Invoice\InvoiceTax> $taxes
 * @property-read int|null $taxes_count
 *
 * @method static \Illuminate\Database\Eloquent\Builder<static>|InvoiceChangePlan whereTotalTaxAmount($value)
 *
 * @mixin \Eloquent
 */
class InvoiceChangePlan extends Invoice
{
    public const TYPE_CHANGE_PLAN = 'change_plan';

    public const ACTION_UPGRADE = 'upgrade';

    public const ACTION_DOWNGRADE = 'downgrade';

    public const ACTION_CHANGE = 'change'; // default action

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'invoices';

    protected $targetPlanId;

    protected $action;

    /**
     * Override the items relationship to explicitly set the foreign key
     */
    public function items(): HasMany
    {
        return $this->hasMany(InvoiceItem::class, 'invoice_id');
    }

    /**
     * Get the taxes applied to this invoice.
     *
     * Overriding the parent method to ensure we use the correct foreign key
     */
    public function taxes(): HasMany
    {
        return $this->hasMany(InvoiceTax::class, 'invoice_id');
    }

    /**
     * Set the target plan ID.
     */
    public function setPlanId($planId): self
    {
        $this->targetPlanId = $planId;

        // Also store in metadata for persistence
        $metadata = $this->metadata ? json_decode($this->metadata, true) : [];
        $metadata['target_plan_id'] = $planId;
        $this->metadata = json_encode($metadata);
        $this->save();

        return $this;
    }

    /**
     * Set the plan change action type.
     */
    public function setAction($action): self
    {
        $this->action = $action;

        return $this;
    }

    /**
     * Get the plan change action type.
     */
    public function getAction(): string
    {
        return $this->action ?? self::ACTION_CHANGE;
    }

    /**
     * Determine if this is an upgrade.
     */
    public function isUpgrade(): bool
    {
        return $this->getAction() === self::ACTION_UPGRADE;
    }

    /**
     * Determine if this is a downgrade.
     */
    public function isDowngrade(): bool
    {
        return $this->getAction() === self::ACTION_DOWNGRADE;
    }

    /**
     * Get the target plan.
     *
     * @throws \Exception
     */
    protected function getTargetPlan()
    {
        // The issue might be the target plan ID not being persisted
        // Check if it's stored in the database
        if (! $this->targetPlanId) {
            // Try to retrieve from metadata if exists
            $metadata = $this->metadata ? json_decode($this->metadata, true) : [];
            $planId = $metadata['target_plan_id'] ?? null;

            if (! $planId) {
                throw new Exception('Target plan ID is not set');
            }

            $this->targetPlanId = $planId;
        }

        $plan = Plan::find($this->targetPlanId);
        if (! $plan) {
            throw new Exception('Target plan not found');
        }

        return $plan;
    }

    /**
     * Process the invoice after payment.
     */
    public function afterPaymentProcessed(): void
    {
        try {
            // Get the target plan - Add error logging
            $newPlan = $this->getTargetPlan();

            // Determine the action based on price comparison if not explicitly set
            if (! $this->action) {
                if ($newPlan->price > $this->subscription->plan->price) {
                    $this->action = self::ACTION_UPGRADE;
                } elseif ($newPlan->price < $this->subscription->plan->price) {
                    $this->action = self::ACTION_DOWNGRADE;
                } else {
                    $this->action = self::ACTION_CHANGE;
                }
            }

            // Ensure subscription relationship is loaded
            if (! $this->relationLoaded('subscription')) {
                $this->load('subscription');
            }

            // Apply plan change based on action type
            if ($this->isUpgrade()) {
                $this->subscription->applyPlanUpgrade($newPlan);
            } elseif ($this->isDowngrade()) {
                $this->subscription->applyPlanDowngrade($newPlan);
            } else {
                $this->subscription->applyPlanChange($newPlan);
            }

            // Ensure changes are saved to database
            $this->subscription->save();
            SubscriptionCache::clearCache($this->subscription->tenant_id);
        } catch (Exception $e) {
            payment_log('Plan change error', 'error', [
                'tenant_id' => tenant_id(),
                'invoice_id' => $this->id,
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }
}
