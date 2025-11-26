<?php

namespace App\Services;

use App\Models\Transaction;
use Carbon\Carbon;

/**
 * Central service to extract standardized payment information from transaction metadata
 * across all payment gateways (PayPal, Stripe, Razorpay, Offline, etc.)
 */
class PaymentDetailsExtractor
{
    /**
     * Extract standardized payment details from any transaction
     */
    public static function extract(Transaction $transaction): array
    {
        $metadata = $transaction->metadata ?? [];

        // Ensure metadata is always an array
        if (! is_array($metadata)) {
            if (is_string($metadata)) {
                // Try to decode JSON string
                $decoded = json_decode($metadata, true);
                $metadata = is_array($decoded) ? $decoded : [];
            } else {
                $metadata = [];
            }
        }

        $type = $transaction->type;

        return [
            'payment_reference' => self::extractPaymentReference($type, $metadata),
            'payment_date' => self::extractPaymentDate($type, $metadata, $transaction),
            'payment_method' => self::extractPaymentMethod($type, $metadata),
            'payment_details' => self::extractPaymentDetails($type, $metadata),
            'gateway_status' => self::extractGatewayStatus($type, $metadata),
            'amount_received' => self::extractAmountReceived($type, $metadata, $transaction),
            'transaction_id' => self::extractTransactionId($type, $metadata),
            'additional_details' => self::extractAdditionalDetails($type, $metadata),
        ];
    }

    /**
     * Extract payment reference/ID based on gateway type
     */
    private static function extractPaymentReference(string $type, array $metadata): ?string
    {
        switch ($type) {
            case 'paypal':
                return $metadata['paypal_order_id'] ?? $metadata['paypal_capture_id'] ?? $metadata['paypal_payment_id'] ?? null;

            case 'stripe':
                return $metadata['stripe_payment_intent_id'] ?? $metadata['stripe_charge_id'] ?? $metadata['stripe_session_id'] ?? null;

            case 'razorpay':
                return $metadata['razorpay_payment_id'] ?? $metadata['razorpay_order_id'] ?? null;

            case 'offline':
                return $metadata['payment_reference'] ?? $metadata['reference'] ?? null;

            case 'bank':
            case 'wire':
                return $metadata['reference_number'] ?? $metadata['transaction_reference'] ?? null;

            default:
                return $metadata['payment_reference'] ?? $metadata['transaction_id'] ?? $metadata['reference'] ?? null;
        }
    }

    /**
     * Extract payment date based on gateway type
     */
    private static function extractPaymentDate(string $type, array $metadata, Transaction $transaction): ?Carbon
    {
        $dateString = null;

        switch ($type) {
            case 'paypal':
                $dateString = $metadata['captured_at'] ?? $metadata['webhook_processed_at'] ?? null;
                break;

            case 'stripe':
                $dateString = $metadata['webhook_processed_at'] ?? null;
                break;

            case 'razorpay':
                $dateString = $metadata['verified_at'] ?? $metadata['confirmed_at'] ?? null;
                break;

            case 'offline':
                $dateString = $metadata['payment_date'] ?? null;
                break;

            default:
                $dateString = $metadata['payment_date'] ?? $metadata['processed_at'] ?? null;
        }

        if ($dateString) {
            try {
                return Carbon::parse($dateString);
            } catch (\Exception $e) {
                // If parsing fails, fall back to transaction updated_at if it's successful
                return $transaction->status === 'success' ? $transaction->updated_at : null;
            }
        }

        // Fall back to transaction updated_at for successful transactions
        return $transaction->status === 'success' ? $transaction->updated_at : null;
    }

    /**
     * Extract payment method based on gateway type
     */
    private static function extractPaymentMethod(string $type, array $metadata): ?string
    {
        switch ($type) {
            case 'paypal':
                return 'PayPal';

            case 'stripe':
                return $metadata['payment_method_type'] ?? 'Stripe';

            case 'razorpay':
                return $metadata['method'] ?? 'Razorpay';

            case 'offline':
                return 'Bank Transfer';

            case 'bank':
            case 'wire':
                return 'Bank Transfer';

            case 'crypto':
                return $metadata['currency'] ?? 'Cryptocurrency';

            default:
                return ucfirst($type);
        }
    }

    /**
     * Extract additional payment details based on gateway type
     */
    private static function extractPaymentDetails(string $type, array $metadata): ?string
    {
        switch ($type) {
            case 'paypal':
                $details = [];
                if (! empty($metadata['paypal_capture_id'])) {
                    $details[] = "Capture ID: {$metadata['paypal_capture_id']}";
                }
                if (! empty($metadata['paypal_status'])) {
                    $details[] = "Status: {$metadata['paypal_status']}";
                }
                if (! empty($metadata['currency'])) {
                    $details[] = "Currency: {$metadata['currency']}";
                }

                return ! empty($details) ? implode("\n", $details) : null;

            case 'stripe':
                $details = [];
                if (! empty($metadata['stripe_status'])) {
                    $details[] = "Status: {$metadata['stripe_status']}";
                }
                if (! empty($metadata['amount_received'])) {
                    $details[] = "Amount: {$metadata['amount_received']}";
                }

                return ! empty($details) ? implode("\n", $details) : null;

            case 'razorpay':
                $details = [];
                if (! empty($metadata['razorpay_status'])) {
                    $details[] = "Status: {$metadata['razorpay_status']}";
                }
                if (! empty($metadata['verification_status'])) {
                    $details[] = "Verification: {$metadata['verification_status']}";
                }

                return ! empty($details) ? implode("\n", $details) : null;

            case 'offline':
                return $metadata['payment_details'] ?? null;

            default:
                return $metadata['payment_details'] ?? $metadata['notes'] ?? null;
        }
    }

    /**
     * Extract gateway-specific status
     */
    private static function extractGatewayStatus(string $type, array $metadata): ?string
    {
        switch ($type) {
            case 'paypal':
                return $metadata['paypal_status'] ?? null;

            case 'stripe':
                return $metadata['stripe_status'] ?? null;

            case 'razorpay':
                return $metadata['razorpay_status'] ?? null;

            case 'offline':
                return null; // Offline doesn't have gateway status

            default:
                return $metadata['status'] ?? null;
        }
    }

    /**
     * Extract amount received from gateway
     */
    private static function extractAmountReceived(string $type, array $metadata, Transaction $transaction): ?string
    {
        $amount = null;

        switch ($type) {
            case 'paypal':
                $amount = $metadata['amount_received'] ?? null;
                if ($amount && ! empty($metadata['currency'])) {
                    return "{$amount} {$metadata['currency']}";
                }
                break;

            case 'stripe':
                $amount = $metadata['amount_received'] ?? null;
                break;

            case 'razorpay':
                $amount = $metadata['amount_verified'] ?? null;
                if ($amount) {
                    // Razorpay stores amount in paise, convert to rupees
                    return number_format($amount / 100, 2).' INR';
                }
                break;
        }

        // Fall back to transaction amount
        return $amount ?? $transaction->amount;
    }

    /**
     * Extract gateway transaction ID
     */
    private static function extractTransactionId(string $type, array $metadata): ?string
    {
        switch ($type) {
            case 'paypal':
                return $metadata['paypal_capture_id'] ?? $metadata['paypal_order_id'] ?? null;

            case 'stripe':
                return $metadata['stripe_charge_id'] ?? $metadata['stripe_payment_intent_id'] ?? null;

            case 'razorpay':
                return $metadata['razorpay_payment_id'] ?? null;

            case 'offline':
                return $metadata['payment_reference'] ?? null;

            default:
                return $metadata['transaction_id'] ?? null;
        }
    }

    /**
     * Get a human-readable summary of the payment
     */
    public static function getSummary(Transaction $transaction): string
    {
        $details = self::extract($transaction);
        $type = ucfirst($transaction->type);
        $status = ucfirst($transaction->status);

        $summary = "{$type} payment - {$status}";

        if ($details['payment_reference']) {
            $summary .= " (Ref: {$details['payment_reference']})";
        }

        if ($details['amount_received']) {
            $summary .= " - {$details['amount_received']}";
        }

        return $summary;
    }

    /**
     * Extract additional details based on gateway type
     */
    private static function extractAdditionalDetails(string $type, array $metadata): array
    {
        switch ($type) {
            case 'paypal':
                $details = [];
                if (isset($metadata['paypal_payer_id'])) {
                    $details['Payer ID'] = $metadata['paypal_payer_id'];
                }
                if (isset($metadata['payment_source'])) {
                    $details['Payment Source'] = ucfirst($metadata['payment_source']);
                }
                if (isset($metadata['payment_status'])) {
                    $details['Payment Status'] = ucfirst($metadata['payment_status']);
                }

                return $details;

            case 'stripe':
                $details = [];
                if (isset($metadata['payment_method_type'])) {
                    $details['Payment Method'] = ucfirst($metadata['payment_method_type']);
                }
                if (isset($metadata['last4'])) {
                    $details['Card Last 4'] = $metadata['last4'];
                }
                if (isset($metadata['brand'])) {
                    $details['Card Brand'] = ucfirst($metadata['brand']);
                }

                return $details;

            case 'razorpay':
                $details = [];
                if (isset($metadata['razorpay_order_id'])) {
                    $details['Order ID'] = $metadata['razorpay_order_id'];
                }
                if (isset($metadata['verification_status'])) {
                    $details['Verification'] = ucfirst($metadata['verification_status']);
                }
                if (isset($metadata['razorpay_signature'])) {
                    $details['Signature'] = substr($metadata['razorpay_signature'], 0, 12).'...';
                }
                if (isset($metadata['confirmed_at'])) {
                    $details['Confirmed At'] = \Carbon\Carbon::parse($metadata['confirmed_at'])->format('M d, Y H:i');
                }

                return $details;

            case 'offline':
                $details = [];
                if (isset($metadata['payment_method'])) {
                    $details['Payment Method'] = $metadata['payment_method'];
                }
                if (isset($metadata['payment_reference'])) {
                    $details['Reference'] = $metadata['payment_reference'];
                }
                if (isset($metadata['payment_date'])) {
                    $details['Payment Date'] = \Carbon\Carbon::parse($metadata['payment_date'])->format('M d, Y');
                }
                if (isset($metadata['account_number'])) {
                    $details['Account Number'] = $metadata['account_number'];
                }
                if (isset($metadata['bank_name'])) {
                    $details['Bank Name'] = $metadata['bank_name'];
                }
                if (isset($metadata['swift_code'])) {
                    $details['SWIFT Code'] = $metadata['swift_code'];
                }

                return $details;

            case 'bank':
            case 'wire':
                $details = [];
                if (isset($metadata['bank_name'])) {
                    $details['Bank'] = $metadata['bank_name'];
                }
                if (isset($metadata['account_number'])) {
                    $details['Account'] = '***'.substr($metadata['account_number'], -4);
                }

                return $details;

            case 'crypto':
                $details = [];
                if (isset($metadata['wallet_address'])) {
                    $details['Wallet'] = substr($metadata['wallet_address'], 0, 8).'...';
                }
                if (isset($metadata['network'])) {
                    $details['Network'] = $metadata['network'];
                }

                return $details;

            default:
                // Return any available metadata that looks useful
                $details = [];
                foreach ($metadata as $key => $value) {
                    if (is_string($value) && ! in_array($key, ['created_at', 'updated_at', 'id'])) {
                        $details[ucfirst(str_replace('_', ' ', $key))] = $value;
                    }
                }

                return $details;
        }
    }
}
