<?php

namespace Corbital\LaravelEmails\Events;

use Corbital\LaravelEmails\Models\EmailLog;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class EmailFailed
{
    use Dispatchable, SerializesModels;

    /**
     * The email log.
     *
     * @var string|EmailLog
     */
    public $log;

    /**
     * The error message.
     *
     * @var string|null
     */
    public $error;

    /**
     * Create a new event instance.
     *
     * @param  string|array|EmailLog  $log
     */
    public function __construct($log, ?string $error = null)
    {
        $this->log = $log;
        $this->error = $error;
    }
}
