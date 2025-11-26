<?php

namespace Corbital\LaravelEmails\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class EmailTemplateDeleted
{
    use Dispatchable, SerializesModels;

    /**
     * The email template instance.
     *
     * @var \Corbital\LaravelEmails\Models\EmailTemplate
     */
    public $template;

    /**
     * Create a new event instance.
     *
     * @param  \Corbital\LaravelEmails\Models\EmailTemplate  $template
     */
    public function __construct($template)
    {
        $this->template = $template;
    }
}
