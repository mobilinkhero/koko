<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;

/**
 * @property int $id
 * @property string $name
 * @property string $code
 * @property string $symbol
 * @property string|null $format
 * @property numeric|null $exchange_rate
 * @property bool $is_default
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 *
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Currency default()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Currency newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Currency newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Currency query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Currency whereCode($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Currency whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Currency whereExchangeRate($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Currency whereFormat($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Currency whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Currency whereIsDefault($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Currency whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Currency whereSymbol($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Currency whereUpdatedAt($value)
 *
 * @mixin \Eloquent
 */
class Currency extends BaseModel
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'code',
        'symbol',
        'format',
        'exchange_rate',
        'is_default',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'exchange_rate' => 'decimal:6',
        'is_default' => 'boolean',
    ];

    /**
     * Format a given amount according to the currency format.
     *
     * @param  float  $amount
     */
    public function format($amount): string
    {
        $this->symbol = get_base_currency()->symbol;
        // Format the amount with proper decimal places
        $formattedAmount = number_format($amount, 2);

        // Check the format type
        if ($this->format === 'after_amount') {
            // If format is 'after_amount', place symbol after the amount
            return $formattedAmount.''.$this->symbol;
        } elseif ($this->format === 'before_amount' || $this->format === null) {
            // If format is 'before_amount' or not set, place symbol before the amount
            return $this->symbol.''.$formattedAmount;
        } elseif (strpos($this->format, ':amount') !== false) {
            // If format contains :amount placeholder, use that
            return str_replace(':amount', $formattedAmount, $this->format);
        } else {
            // Default fallback: symbol before amount
            return $this->symbol.''.$formattedAmount;
        }
    }

    /**
     * Scope a query to only include the default currency.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeDefault($query)
    {
        return $query->where('is_default', true);
    }

    /**
     * Get the default currency.
     *
     * @return self|null
     */
    public static function getDefault()
    {
        return static::default()->first();
    }

    /**
     * Convert an amount from this currency to another currency.
     *
     * @param  float  $amount
     */
    public function convert($amount, Currency $targetCurrency): float
    {
        if ($this->id === $targetCurrency->id) {
            return $amount;
        }

        // Convert amount to base currency first (assuming exchange rates are relative to a base currency)
        $baseAmount = $amount / $this->exchange_rate;

        // Then convert to target currency
        return $baseAmount * $targetCurrency->exchange_rate;
    }

    /**
     * Convert amount to default currency.
     */
    public function convertToDefault(float $amount): float
    {
        // If this is already the default currency, return as-is
        if ($this->is_default) {
            return $amount;
        }

        return $amount / $this->exchange_rate;
    }

    /**
     * Convert amount from default currency.
     */
    public function convertFromDefault(float $amount): float
    {
        // If this is already the default currency, return as-is
        if ($this->is_default) {
            return $amount;
        }

        return $amount * $this->exchange_rate;
    }
}
