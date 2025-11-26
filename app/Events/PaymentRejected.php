<?php

namespace App\Events;

use App\Models\Transaction;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class PaymentRejected
{
    use Dispatchable, SerializesModels;

    public $transaction;

    public $reason;

    /**
     * Create a new event instance.
     */
    public function __construct(Transaction $transaction, string $reason)
    {
        $this->transaction = $transaction;
        $this->reason = $reason;
    }
}
