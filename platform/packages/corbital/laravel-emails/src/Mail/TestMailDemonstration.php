<?php

namespace Corbital\LaravelEmails\Mail;

/**
 * TestMailDemonstration.php
 *
 * This is a demonstration file showing how to implement a test mail functionality
 * based on the TestMail.php file you shared. This is not meant to be included in your
 * package directly, but rather shows a pattern you can apply to your existing classes.
 */

use Corbital\LaravelEmails\Models\EmailTemplate;
use Corbital\LaravelEmails\Services\MergeFieldsService;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class TestMailDemonstration extends Mailable
{
    use Queueable;
    use SerializesModels;

    public $template;

    public $subject;

    public $body;

    /**
     * Create a new message instance specifically for testing an email template.
     *
     * This constructor is similar to the TestMail.php you shared. It takes a
     * template slug, retrieves the template, and parses its content.
     */
    public function __construct($slug)
    {
        // Retrieve the template using a helper function or direct query
        $this->template = EmailTemplate::where('slug', $slug)->firstOrFail();

        // Parse the content of the template
        $this->subject = $this->parseContent($this->template->merge_fields_groups ?? [], $this->template->subject);
        $this->body = $this->parseContent($this->template->merge_fields_groups ?? [], $this->template->content);
    }

    /**
     * Parse the content with merge fields from different groups.
     *
     * This method mimics the functionality in TestMail.php, using the MergeFieldsService
     * from your package which already has similar functionality.
     */
    protected function parseContent($groups, $content, $data = [])
    {
        return app(MergeFieldsService::class)->parseTemplates(
            $groups,
            $content,
            $data
        );
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: $this->subject,
        );
    }

    /**
     * Get the message content definition.
     *
     * This method uses the Content class like in TestMail.php, setting up
     * a view with the processed content.
     */
    public function content(): Content
    {
        return new Content(
            view: 'laravel-emails::email',
            with: [
                'title' => $this->template->subject,
                'body' => $this->body,
                'layout' => $this->template->layout ?? null,
                'template' => $this->template,
            ]
        );
    }

    /**
     * Get the attachments for the message.
     */
    public function attachments(): array
    {
        return [];
    }
}
