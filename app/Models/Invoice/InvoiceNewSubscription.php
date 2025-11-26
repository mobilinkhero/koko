<?php

namespace App\Models\Invoice;

use App\Services\SubscriptionCache;
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
 * @method static \Illuminate\Database\Eloquent\Builder<static>|InvoiceNewSubscription changePlan()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|InvoiceNewSubscription newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|InvoiceNewSubscription newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|InvoiceNewSubscription newSubscription()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|InvoiceNewSubscription paid()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|InvoiceNewSubscription query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|InvoiceNewSubscription renewSubscription()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|InvoiceNewSubscription unpaid()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|InvoiceNewSubscription whereCancelledAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|InvoiceNewSubscription whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|InvoiceNewSubscription whereCurrencyId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|InvoiceNewSubscription whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|InvoiceNewSubscription whereDueDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|InvoiceNewSubscription whereFee($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|InvoiceNewSubscription whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|InvoiceNewSubscription whereInvoiceNumber($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|InvoiceNewSubscription whereMetadata($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|InvoiceNewSubscription whereNoPaymentRequiredWhenFree($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|InvoiceNewSubscription wherePaidAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|InvoiceNewSubscription whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|InvoiceNewSubscription whereSubscriptionId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|InvoiceNewSubscription whereTaxPercent($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|InvoiceNewSubscription whereTenantId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|InvoiceNewSubscription whereTitle($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|InvoiceNewSubscription whereType($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|InvoiceNewSubscription whereUpdatedAt($value)
 *
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Invoice\InvoiceTax> $taxes
 * @property-read int|null $taxes_count
 *
 * @method static \Illuminate\Database\Eloquent\Builder<static>|InvoiceNewSubscription whereTotalTaxAmount($value)
 *
 * @mixin \Eloquent
 */
class InvoiceNewSubscription extends Invoice
{
    public const TYPE_NEW_SUBSCRIPTION = 'new_subscription';

    protected $table = 'invoices';

    // Override the items relationship to explicitly set the foreign key and table
    public function items(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(InvoiceItem::class, 'invoice_id');
    }

    /**
     * Process the invoice after payment.
     */
    public function afterPaymentProcessed(): void
    {
        // Activate the subscription
        $this->subscription->activate();
        SubscriptionCache::clearCache($this->subscription->tenant_id);
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
}
