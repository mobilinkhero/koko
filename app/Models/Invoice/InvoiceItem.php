<?php

namespace App\Models\Invoice;

use App\Models\BaseModel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

/**
 * @property int $id
 * @property int $invoice_id
 * @property int|null $item_id
 * @property string|null $item_type
 * @property string $title
 * @property string|null $description
 * @property numeric $amount
 * @property int $quantity
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Invoice\Invoice $invoice
 * @property-read Model|\Eloquent|null $item
 *
 * @method static \Illuminate\Database\Eloquent\Builder<static>|InvoiceItem newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|InvoiceItem newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|InvoiceItem query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|InvoiceItem whereAmount($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|InvoiceItem whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|InvoiceItem whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|InvoiceItem whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|InvoiceItem whereInvoiceId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|InvoiceItem whereItemId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|InvoiceItem whereItemType($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|InvoiceItem whereQuantity($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|InvoiceItem whereTitle($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|InvoiceItem whereUpdatedAt($value)
 *
 * @mixin \Eloquent
 */
class InvoiceItem extends BaseModel
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'invoice_id',
        'item_id',
        'item_type',
        'title',
        'description',
        'amount',
        'quantity',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'amount' => 'decimal:2',
        'quantity' => 'integer',
    ];

    /**
     * Get the invoice that owns the item.
     */
    public function invoice(): BelongsTo
    {
        return $this->belongsTo(Invoice::class);
    }

    /**
     * Get the polymorphic relation.
     */
    public function item(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * Calculate subtotal (price * quantity).
     */
    public function subTotal(): float
    {
        return $this->amount * $this->quantity;
    }

    /**
     * Calculate tax amount based on applied taxes.
     */
    public function getTax(): float
    {
        $taxRate = $this->invoice->taxes()->sum('rate') / 100;

        return $this->subTotal() * $taxRate;
    }

    /**
     * Calculate total with tax.
     */
    public function total(): float
    {
        return $this->subTotal() + $this->getTax();
    }
}
