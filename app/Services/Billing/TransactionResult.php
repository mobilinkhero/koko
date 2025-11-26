<?php

namespace App\Services\Billing;

/**
 * Transaction Result Value Object
 *
 * Represents the outcome of a payment transaction verification or processing
 * operation in the WhatsApp SaaS billing system. Provides a standardized
 * way to communicate transaction status and associated messages.
 *
 * Key Features:
 * - Standardized transaction status constants
 * - Status checking convenience methods
 * - Optional message handling for details
 * - Immutable value object design
 * - Type-safe status representation
 *
 * Transaction States:
 * - PENDING: Transaction requires manual review or processing
 * - DONE: Transaction completed successfully
 * - FAILED: Transaction failed or was rejected
 *
 * This class is typically returned by payment gateway verification methods
 * and billing operations to provide consistent status information across
 * different payment processing systems.
 *
 * Usage Example:
 * ```php
 * // From payment gateway verification
 * $result = $gateway->verify($transaction);
 *
 * if ($result->isDone()) {
 *     // Process successful payment
 *     $invoice->markAsPaid();
 * } elseif ($result->isPending()) {
 *     // Wait for manual approval
 *     $transaction->setStatus('pending');
 * } else {
 *     // Handle failed payment
 *     logger()->error('Payment failed: ' . $result->getMessage());
 * }
 * ```
 *
 * @author WhatsApp SaaS Team
 *
 * @version 1.0.0
 *
 * @since 1.0.0
 * @see PaymentGatewayInterface::verify() For usage context
 * @see Transaction For transaction model
 * @see Invoice For invoice processing
 */
class TransactionResult
{
    /**
     * Transaction is pending manual review or processing.
     *
     * Used for offline payments, bank transfers, or any payment
     * method that requires administrative verification before
     * the transaction can be considered complete.
     */
    public const RESULT_PENDING = 'pending';

    /**
     * Transaction completed successfully.
     *
     * Indicates the payment was processed successfully and
     * the associated invoice or subscription can be activated.
     */
    public const RESULT_DONE = 'done';

    /**
     * Transaction failed or was rejected.
     *
     * Indicates the payment could not be processed due to
     * insufficient funds, invalid card, or other error conditions.
     */
    public const RESULT_FAILED = 'failed';

    /**
     * The current status of the transaction.
     *
     * Must be one of the RESULT_* constants defined in this class.
     * Represents the final outcome of the transaction processing.
     *
     * @var string Transaction status constant
     */
    protected $status;

    /**
     * Optional message providing additional details.
     *
     * Contains human-readable information about the transaction
     * result, such as error details, approval notes, or
     * processing information.
     *
     * @var string Transaction message or details
     */
    protected $message;

    /**
     * Create a new transaction result instance.
     *
     * Initializes the result with a status and optional message.
     * The status must be one of the predefined constants.
     *
     * @param  string  $status  The transaction status (use RESULT_* constants)
     * @param  string  $message  Optional message with additional details
     *
     * @example
     * ```php
     * // Successful transaction
     * $result = new TransactionResult(TransactionResult::RESULT_DONE);
     *
     * // Failed transaction with details
     * $result = new TransactionResult(
     *     TransactionResult::RESULT_FAILED,
     *     'Insufficient funds'
     * );
     *
     * // Pending transaction
     * $result = new TransactionResult(
     *     TransactionResult::RESULT_PENDING,
     *     'Awaiting bank transfer verification'
     * );
     * ```
     */
    public function __construct(string $status, string $message = '')
    {
        $this->status = $status;
        $this->message = $message;
    }

    /**
     * Determine if the transaction is completed successfully.
     *
     * Checks if the transaction status indicates successful completion.
     * Use this method to determine if payment processing should proceed
     * with invoice fulfillment or subscription activation.
     *
     * @return bool True if transaction is complete, false otherwise
     *
     * @example
     * ```php
     * if ($result->isDone()) {
     *     // Activate subscription
     *     $subscription->activate();
     *
     *     // Send confirmation email
     *     Mail::to($customer)->send(new PaymentConfirmation($invoice));
     * }
     * ```
     */
    public function isDone(): bool
    {
        return $this->status == self::RESULT_DONE;
    }

    /**
     * Determine if the transaction is pending review.
     *
     * Checks if the transaction requires manual processing or
     * administrative approval before it can be considered complete.
     * Common for offline payments and bank transfers.
     *
     * @return bool True if transaction is pending, false otherwise
     *
     * @example
     * ```php
     * if ($result->isPending()) {
     *     // Notify admin for manual review
     *     Notification::send($admins, new PendingPaymentReview($transaction));
     *
     *     // Update customer about pending status
     *     $customer->notify(new PaymentPendingNotification($invoice));
     * }
     * ```
     */
    public function isPending(): bool
    {
        return $this->status == self::RESULT_PENDING;
    }

    /**
     * Determine if the transaction failed.
     *
     * Checks if the transaction was rejected or could not be processed.
     * Use this to handle payment failures and retry logic.
     *
     * @return bool True if transaction failed, false otherwise
     *
     * @example
     * ```php
     * if ($result->isFailed()) {
     *     // Log failure for analysis
     *
     *     // Offer retry or alternative payment method
     *     return redirect()->route('payment.retry')->with('error', $result->getMessage());
     * }
     * ```
     */
    public function isFailed(): bool
    {
        return $this->status == self::RESULT_FAILED;
    }

    /**
     * Get the transaction message.
     *
     * Returns additional information about the transaction result,
     * such as error details, processing notes, or success confirmations.
     *
     * @return string The transaction message
     *
     * @example
     * ```php
     * $message = $result->getMessage();
     * if (!empty($message)) {
     *     // Display message to user
     *     session()->flash('payment_info', $message);
     * }
     * ```
     */
    public function getMessage(): string
    {
        return $this->message;
    }

    /**
     * Get the transaction status.
     *
     * Returns the raw status string, which will be one of the
     * RESULT_* constants. Useful for logging and debugging.
     *
     * @return string The transaction status constant
     *
     * @example
     * ```php
     * $status = $result->getStatus();
     * ```
     */
    public function getStatus(): string
    {
        return $this->status;
    }
}
