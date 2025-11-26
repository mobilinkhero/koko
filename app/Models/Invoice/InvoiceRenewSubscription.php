<?php

namespace App\Models\Invoice;

use App\Events\SubscriptionRenewed;
use App\Models\Subscription;
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
 * @method static \Illuminate\Database\Eloquent\Builder<static>|InvoiceRenewSubscription changePlan()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|InvoiceRenewSubscription newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|InvoiceRenewSubscription newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|InvoiceRenewSubscription newSubscription()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|InvoiceRenewSubscription paid()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|InvoiceRenewSubscription query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|InvoiceRenewSubscription renewSubscription()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|InvoiceRenewSubscription unpaid()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|InvoiceRenewSubscription whereCancelledAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|InvoiceRenewSubscription whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|InvoiceRenewSubscription whereCurrencyId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|InvoiceRenewSubscription whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|InvoiceRenewSubscription whereDueDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|InvoiceRenewSubscription whereFee($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|InvoiceRenewSubscription whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|InvoiceRenewSubscription whereInvoiceNumber($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|InvoiceRenewSubscription whereMetadata($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|InvoiceRenewSubscription whereNoPaymentRequiredWhenFree($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|InvoiceRenewSubscription wherePaidAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|InvoiceRenewSubscription whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|InvoiceRenewSubscription whereSubscriptionId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|InvoiceRenewSubscription whereTaxPercent($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|InvoiceRenewSubscription whereTenantId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|InvoiceRenewSubscription whereTitle($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|InvoiceRenewSubscription whereType($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|InvoiceRenewSubscription whereUpdatedAt($value)
 *
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Invoice\InvoiceTax> $taxes
 * @property-read int|null $taxes_count
 *
 * @method static \Illuminate\Database\Eloquent\Builder<static>|InvoiceRenewSubscription whereTotalTaxAmount($value)
 *
 * @mixin \Eloquent
 */
class InvoiceRenewSubscription extends Invoice
{
    public const TYPE_RENEW_SUBSCRIPTION = 'renew_subscription';

    protected $table = 'invoices';

    /**
     * Process the invoice after payment.
     */
    public function afterPaymentProcessed(): void
    {
        // Renew the subscription
        $this->subscription->renew();

        SubscriptionCache::clearCache($this->subscription->tenant_id);

        // Send renewal confirmation email
        event(new SubscriptionRenewed($this->subscription));
    }

    // Override the items relationship to explicitly set the foreign key and table
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
}
