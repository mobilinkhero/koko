<?php

namespace Corbital\LaravelEmails\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class TemplatedEmail extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * The HTML content of the email.
     *
     * @var string|null
     */
    protected $htmlContent;

    /**
     * The template data.
     *
     * @var array
     */
    protected $templateData = [];

    /**
     * Create a new message instance.
     *
     * @param  string  $subject  The email subject
     * @param  string|null  $content  The direct HTML content (if not using template)
     * @param  array  $data  The template data
     */
    public function __construct(string $subject, ?string $content = null, array $data = [])
    {
        $this->subject($subject);
        $this->htmlContent = $content;
        $this->templateData = $data;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->html($this->htmlContent);
    }
}
