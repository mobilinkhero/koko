<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TenantCreditBalance extends Model
{
    protected $fillable = [
        'tenant_id',
        'balance',
        'currency_id',
    ];

    protected $casts = [
        'balance' => 'decimal:2',
    ];

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function currency(): BelongsTo
    {
        return $this->belongsTo(Currency::class);
    }

    public static function getOrCreateBalance(int $tenantId, $currencyId = null): self
    {
        return static::firstOrCreate([
            'tenant_id' => $tenantId,
        ], [
            'balance' => 0,
            'currency_id' => $currencyId ?? get_base_currency()->id,
        ]);
    }

    /**
     * Add credit with detailed tracking
     */
    public static function addCredit($tenant_id, $currencyId, float $amount, string $description, ?int $invoiceId = null, ?array $metadata = null)
    {
        $balance = static::getOrCreateBalance($tenant_id, $currencyId);
        // Update balance
        $oldBalance = $balance->balance;
        $balance->increment('balance', $amount);
        $balance = static::getOrCreateBalance($tenant_id, $currencyId);

        // Create transaction record
        CreditTransaction::create([
            'tenant_id' => $tenant_id,
            'type' => 'credit',
            'amount' => $amount,
            'currency_id' => $balance->currency_id,
            'description' => $description,
            'invoice_id' => $invoiceId,
            'metadata' => array_merge([
                'old_balance' => $oldBalance,
                'new_balance' => $balance->balance,
                'created_by' => 'system',
                'source' => 'downgrade_proration',
            ], $metadata ?? []),
        ]);

        payment_log('Credit added to tenant balance', 'info', [
            'tenant_id' => $tenant_id,
            'amount' => $amount,
            'old_balance' => $oldBalance,
            'new_balance' => $balance->balance,
            'description' => $description,
        ]);

        return $balance;
    }

    public static function deductCredit($tenant_id, float $amount, string $description, $invoiceId = null)
    {
        $balance = static::getOrCreateBalance($tenant_id);
        if ($balance->balance < $amount) {
            return false;
        }

        $balance->decrement('balance', $amount);
        $balance = static::getOrCreateBalance($tenant_id);

        CreditTransaction::create([
            'tenant_id' => $tenant_id,
            'type' => 'debit',
            'amount' => $amount,
            'currency_id' => $balance->currency_id,
            'description' => $description,
            'invoice_id' => $invoiceId,
        ]);

        return $balance;
    }
}
