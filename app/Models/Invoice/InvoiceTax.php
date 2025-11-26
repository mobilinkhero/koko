<?php

namespace App\Models\Invoice;

use App\Models\BaseModel;
use App\Models\Tax;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id
 * @property int $invoice_id
 * @property int|null $tax_id
 * @property string $name
 * @property numeric $rate
 * @property numeric $amount
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Invoice\Invoice $invoice
 * @property-read Tax|null $tax
 *
 * @method static \Illuminate\Database\Eloquent\Builder<static>|InvoiceTax newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|InvoiceTax newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|InvoiceTax query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|InvoiceTax whereAmount($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|InvoiceTax whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|InvoiceTax whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|InvoiceTax whereInvoiceId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|InvoiceTax whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|InvoiceTax whereRate($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|InvoiceTax whereTaxId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|InvoiceTax whereUpdatedAt($value)
 *
 * @mixin \Eloquent
 */
class InvoiceTax extends BaseModel
{
    protected $fillable = [
        'invoice_id',
        'tax_id',
        'name',
        'rate',
        'amount',
    ];

    protected $casts = [
        'rate' => 'decimal:2',
        'amount' => 'decimal:2',
    ];

    /**
     * Get the invoice that owns the tax.
     */
    public function invoice(): BelongsTo
    {
        return $this->belongsTo(Invoice::class, 'invoice_id');
    }

    /**
     * Get the tax that this invoice tax is based on.
     */
    public function tax(): BelongsTo
    {
        return $this->belongsTo(Tax::class, 'tax_id');
    }

    /**
     * Format the tax rate as a percentage.
     */
    public function formattedRate(): string
    {
        return number_format($this->rate, 2).'%';
    }

    /**
     * Format the tax amount with the invoice's currency.
     */
    public function formattedAmount(): string
    {
        // Check if the invoice relationship is loaded to avoid lazy loading issues
        if (! $this->relationLoaded('invoice')) {
            // Use a fallback formatting if invoice isn't loaded
            $currency = app('currency.service')->getBaseCurrency();

            return $currency->symbol.number_format($this->amount, 2);
        }

        return $this->invoice->formatAmount($this->amount);
    }
}
