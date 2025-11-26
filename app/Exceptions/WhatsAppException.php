<?php

namespace App\Exceptions;

use Exception;

class WhatsAppException extends Exception
{
    /**
     * Additional data related to the exception.
     *
     * @var array
     */
    protected $data = [];

    /**
     * Create a new WhatsApp exception instance.
     *
     * @param  string  $message  The exception message
     * @param  int  $code  The exception code
     * @param  \Exception  $previous  The previous exception
     * @param  array  $data  Additional contextual data
     */
    public function __construct(string $message = '', int $code = 0, ?Exception $previous = null, array $data = [])
    {
        parent::__construct($message, $code, $previous);
        $this->data = $data;

        // Log the exception automatically when it's created
        whatsapp_log($message, 'error', $data, $this);
    }

    /**
     * Get additional exception data.
     */
    public function getData(): array
    {
        return $this->data;
    }
}
