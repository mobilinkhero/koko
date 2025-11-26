<?php

namespace Corbital\LaravelEmails\Services;

use Corbital\LaravelEmails\Exceptions\EmailException;
use Corbital\LaravelEmails\Mail\TemplatedEmail;
use Corbital\LaravelEmails\Models\EmailTemplate;
use Exception;
use Illuminate\Support\Facades\Mail;

class CustomEmailService
{
    /**
     * The email recipients.
     */
    protected array $to = [];

    /**
     * The email cc recipients.
     */
    protected array $cc = [];

    /**
     * The email bcc recipients.
     */
    protected array $bcc = [];

    /**
     * The email reply-to address.
     */
    protected ?string $replyTo = null;

    /**
     * The email from address.
     */
    protected ?array $from = null;

    /**
     * The email subject.
     */
    protected string $subject = '';

    protected ?string $htmlContent = null;

    protected ?string $textContent = null;

    /**
     * The template identifier to use.
     */
    protected ?string $templateIdentifier = null;

    /**
     * The template data.
     */
    protected array $data = [];

    /**
     * The email attachments.
     */
    protected array $attachments = [];

    /**
     * Set the recipients of the email.
     */
    public function to($to): self
    {
        $this->to = is_array($to) ? $to : [$to];

        return $this;
    }

    /**
     * Set the cc recipients of the email.
     */
    public function cc($cc): self
    {
        $this->cc = is_array($cc) ? $cc : [$cc];

        return $this;
    }

    /**
     * Set the bcc recipients of the email.
     */
    public function bcc($bcc): self
    {
        $this->bcc = is_array($bcc) ? $bcc : [$bcc];

        return $this;
    }

    /**
     * Set the reply-to address of the email.
     */
    public function replyTo(string $replyTo): self
    {
        $this->replyTo = $replyTo;

        return $this;
    }

    /**
     * Set the from address for the email.
     */
    public function from(string $email, ?string $name = null): self
    {
        $this->from = [
            'email' => $email,
            'name' => $name,
        ];

        return $this;
    }

    /**
     * Set the subject of the email.
     */
    public function subject(string $subject): self
    {
        $this->subject = $subject;

        return $this;
    }

    /**
     * Use an email template for the content.
     */
    public function template($identifier, array $data = []): self
    {
        $this->templateIdentifier = $identifier;
        $this->data = $data;

        return $this;
    }

    /**
     * Attach a file to the email.
     */
    public function attach(string $path, array $options = []): self
    {
        $this->attachments[] = [
            'path' => $path,
            'options' => $options,
        ];

        return $this;
    }

    /**
     * Attach multiple files to the email.
     */
    public function attachments(array $attachments): self
    {
        foreach ($attachments as $attachment) {
            if (is_string($attachment)) {
                $this->attach($attachment);
            } elseif (is_array($attachment) && isset($attachment['path'])) {
                $this->attach(
                    $attachment['path'],
                    $attachment['options'] ?? []
                );
            }
        }

        return $this;
    }

    /**
     * Send the email.
     *
     * @throws EmailException
     */
    public function send(): bool
    {
        try {
            // Validate required fields
            if (empty($this->to)) {
                throw new EmailException('No recipient specified');
            }

            if (empty($this->subject)) {
                throw new EmailException('No email subject specified');
            }

            $processedSubject = $this->subject;
            $content = null;

            // Handle template-based or direct content email
            if ($this->htmlContent !== null) {
                // Process variables in direct content if any
                $content = $this->processVariables($this->htmlContent, $this->data);
            } elseif ($this->templateIdentifier !== null) {
                // Get the template
                $template = $this->findTemplate($this->templateIdentifier);
                if (! $template) {
                    throw new EmailException("Template not found: {$this->templateIdentifier}");
                }

                // Process the template content
                $content = $this->processVariables($template->content, $this->data);
                // Process the subject if template has one
                if ($template->subject) {
                    $processedSubject = $this->processVariables($template->subject, $this->data);
                }
            } else {
                throw new EmailException('No content or template specified');
            }

            // Create mail message
            $mailMessage = new TemplatedEmail($processedSubject, $content, $this->data);

            // Add attachments
            foreach ($this->attachments as $attachment) {
                $mailMessage->attach($attachment['path'], $attachment['options']);
            }

            // Send the email
            $mailBuilder = Mail::to($this->to);

            if (! empty($this->cc)) {
                $mailBuilder->cc($this->cc);
            }

            if (! empty($this->bcc)) {
                $mailBuilder->bcc($this->bcc);
            }

            if (! empty($this->replyTo)) {
                $mailMessage->replyTo($this->replyTo);
            }

            if (! empty($this->from)) {
                $mailMessage->from($this->from['email'], $this->from['name']);
            }

            $mailBuilder->send($mailMessage);

            // Reset state for the next email
            $this->resetState();

            return true;

        } catch (Exception $e) {
            // Log the error
            app_log('Failed to send email: '.$e->getMessage(), 'error', $e, [
                'template' => $this->templateIdentifier,
                'recipient' => $this->to,
                'error' => $e->getMessage(),
            ]);

            // Reset the state for the next email
            $this->resetState();

            // Throw the exception if configured to do so
            if (config('laravel-emails.throw_exceptions', false)) {
                throw $e;
            }

            return false;
        }
    }

    /**
     * Set the HTML content of the email directly.
     *
     * @return $this
     */
    public function content(string $content, array $data = [])
    {
        $this->htmlContent = $content;
        $this->data = $data;

        return $this;
    }

    /**
     * Find a template by identifier (slug or name).
     */
    protected function findTemplate(string $identifier): ?EmailTemplate
    {
        // First try finding by slug (most common identifier)
        $template = EmailTemplate::where('slug', $identifier)->first();

        // If not found by slug, try by name
        if (! $template) {
            $template = EmailTemplate::where('name', $identifier)->first();
        }

        return $template;
    }

    /**
     * Process variables in a string.
     */
    protected function processVariables(string $content, array $variables): string
    {
        // Add default global variables
        $allVariables = array_merge(
            config('laravel-emails.default_variables', []),
            $variables
        );

        // Replace variables in the format {{ variable }}
        return preg_replace_callback('/{{\s*([a-zA-Z0-9_.]+)\s*}}/', function ($matches) use ($allVariables) {
            $key = $matches[1];

            return $allVariables[$key] ?? '';
        }, $content);
    }

    /**
     * Reset the service state for the next email.
     */
    protected function resetState(): void
    {
        $this->to = [];
        $this->cc = [];
        $this->bcc = [];
        $this->replyTo = null;
        $this->from = null;
        $this->subject = '';
        $this->templateIdentifier = null;
        $this->data = [];
        $this->attachments = [];
    }
}
