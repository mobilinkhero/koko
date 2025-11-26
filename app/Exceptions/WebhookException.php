<?php

namespace App\Exceptions;

use Exception;

class WebhookException extends Exception
{
    /**
     * Create a new webhook exception instance.
     *
     * @return void
     */
    public function __construct(string $message = 'Webhook validation failed', int $code = 0, ?\Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}
