<?php

namespace Corbital\LaravelEmails\Mail;

use Corbital\LaravelEmails\Models\EmailTemplate;
use Corbital\LaravelEmails\Services\MergeFieldsService;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class TemplateMail extends Mailable
{
    use Queueable;
    use SerializesModels;

    public $template;

    public $subject;

    public $body;

    public $data;

    public $customSubject;

    public $viewPath;

    /**
     * Create a new message instance.
     *
     * @param  string  $slug  The slug of the email template to use
     * @param  array  $data  The data to be merged into the template
     * @param  string|null  $customSubject  Optional custom subject to override template subject
     * @param  string  $viewPath  Optional custom view path instead of default
     */
    public function __construct($slug, array $data = [], $customSubject = null, $viewPath = 'laravel-emails::email')
    {
        $this->template = EmailTemplate::where('slug', $slug)->firstOrFail();
        $this->data = $data;
        $this->customSubject = $customSubject;
        $this->viewPath = $viewPath;

        // Parse the content
        $this->parseContent();
    }

    /**
     * Parse the email content with merge fields
     */
    protected function parseContent()
    {
        $mergeFields = app(MergeFieldsService::class);

        // Parse subject - either use custom subject or template subject
        $rawSubject = $this->customSubject ?? $this->template->subject;
        $this->subject = $mergeFields->parse($rawSubject, $this->data);

        // Apply filter hook to subject
        $this->subject = apply_filters('email.subject', $this->subject, [
            'template' => $this->template,
            'data' => $this->data,
        ]);

        // Parse body content - if the template has merge_fields_groups, use the parseTemplates method
        if (isset($this->template->merge_fields_groups)) {
            $this->body = $mergeFields->parseTemplates(
                $this->template->merge_fields_groups,
                $this->template->content ?? '',
                $this->data
            );
        } else {
            // Use standard parsing
            $this->body = $mergeFields->parse($this->template->content ?? '', $this->data);
        }

        // Apply filter hook to content
        $this->body = apply_filters('email.content', $this->body, [
            'template' => $this->template,
            'data' => $this->data,
        ]);

        // Fire action hook after template is rendered
        do_action('email.template_rendered', $this->template, $this->data);
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
     */
    public function content(): Content
    {
        // Enhanced implementation similar to TestMail
        return new Content(
            view: $this->viewPath,
            with: [
                'title' => $this->subject,
                'body' => $this->body,
                'layout' => $this->template->layout ?? null,
                'template' => $this->template,
                'data' => $this->data,
            ]
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, \Illuminate\Mail\Mailables\Attachment>
     */
    public function attachments(): array
    {
        return [];
    }
}
