<?php

namespace App\Exceptions;

use Exception;

class IncompletePaymentException extends Exception
{
    protected $paymentIntent;

    /**
     * Create a new exception instance.
     *
     * @param  mixed  $paymentIntent
     * @param  string  $message
     * @return void
     */
    public function __construct($paymentIntent, $message = 'The payment attempt requires additional action.')
    {
        parent::__construct($message);

        $this->paymentIntent = $paymentIntent;
    }

    /**
     * Get the Stripe PaymentIntent object.
     *
     * @return mixed
     */
    public function payment()
    {
        return $this->paymentIntent;
    }
}
