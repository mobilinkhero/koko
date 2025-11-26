<?php

namespace App\Services;

use App\Models\Tax;
use Illuminate\Support\Collection;

/**
 * Invoice Tax Calculator Service
 *
 * This service handles tax calculations for invoices based on selected or default tax rates.
 */
class InvoiceTaxCalculator
{
    /**
     * Calculate taxes for an invoice amount
     *
     * @param  float  $amount  The invoice amount to calculate taxes for
     * @param  Collection|null  $taxes  Optional collection of Tax models to use for calculation
     * @return array Array with calculated tax details and total
     */
    public static function calculateTaxes(float $amount, ?Collection $taxes = null): array
    {
        // If no taxes provided, use default taxes from settings
        if ($taxes === null) {
            $taxes = get_default_taxes();
        }

        $taxDetails = [];
        $totalTaxAmount = 0;

        foreach ($taxes as $tax) {
            $taxAmount = $amount * ($tax->rate / 100);
            $taxDetails[] = [
                'id' => $tax->id,
                'name' => $tax->name,
                'rate' => $tax->rate,
                'amount' => $taxAmount,
            ];
            $totalTaxAmount += $taxAmount;
        }

        return [
            'details' => $taxDetails,
            'total_tax_amount' => $totalTaxAmount,
            'total_with_tax' => $amount + $totalTaxAmount,
        ];
    }

    /**
     * Format tax details for display
     *
     * @param  array  $taxDetails  Tax details array from calculateTaxes()
     * @return string Formatted tax string
     */
    public static function formatTaxDetails(array $taxDetails): string
    {
        if (empty($taxDetails['details'])) {
            return 'No Tax';
        }

        $formatted = [];
        foreach ($taxDetails['details'] as $tax) {
            $formatted[] = sprintf(
                '%s (%s%%): %s',
                $tax['name'],
                number_format($tax['rate'], 2),
                number_format($tax['amount'], 2)
            );
        }

        return implode(', ', $formatted);
    }
}
