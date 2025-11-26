<?php

namespace App\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class TransactionCreated
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $transactionId;

    public $invoiceId;

    /**
     * Create a new event instance.
     */
    public function __construct($transactionId, $invoiceId)
    {
        $this->transactionId = $transactionId;
        $this->invoiceId = $invoiceId;
    }
}
