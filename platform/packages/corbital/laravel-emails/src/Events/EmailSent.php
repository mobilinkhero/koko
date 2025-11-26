<?php

namespace Corbital\LaravelEmails\Events;

use Corbital\LaravelEmails\Models\EmailLog;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class EmailSent
{
    use Dispatchable, SerializesModels;

    /**
     * The email log instance.
     */
    public EmailLog $log;

    /**
     * Create a new event instance.
     */
    public function __construct(EmailLog $log)
    {
        $this->log = $log;
    }
}
