<?php

namespace App\Models;

use App\Models\Invoice\Invoice;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CreditTransaction extends BaseModel
{
    protected $fillable = [
        'tenant_id',
        'type',
        'amount',
        'currency_id',
        'description',
        'metadata',
        'invoice_id',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'metadata' => 'array',
    ];

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function currency(): BelongsTo
    {
        return $this->belongsTo(Currency::class);
    }

    public function invoice(): BelongsTo
    {
        return $this->belongsTo(Invoice::class);
    }
}
