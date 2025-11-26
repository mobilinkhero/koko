<?php

namespace Corbital\LaravelEmails\Services;

use Corbital\LaravelEmails\Exceptions\EmailException;
use Corbital\LaravelEmails\Mail\TemplatedEmail;
use Corbital\LaravelEmails\Models\EmailTemplate;
use Corbital\LaravelEmails\Settings\EmailSettings;
use Exception;
use Illuminate\Support\Facades\Mail;

class EmailService
{
    /**
     * The email recipients.
     *
     * @var array|string
     */
    protected $to = [];

    /**
     * The email cc recipients.
     *
     * @var array|string
     */
    protected $cc = [];

    /**
     * The email bcc recipients.
     *
     * @var array|string
     */
    protected $bcc = [];

    /**
     * The email reply-to address.
     *
     * @var string|null
     */
    protected $replyTo = null;

    /**
     * The email from address.
     *
     * @var array|null
     */
    protected $from = null;

    /**
     * The email subject.
     *
     * @var string|null
     */
    protected $subject = null;

    /**
     * The template identifier to use.
     *
     * @var string|int|null
     */
    protected $templateIdentifier = null;

    /**
     * The HTML content for the email.
     *
     * @var string|null
     */
    protected $htmlContent = null;

    /**
     * The template data.
     *
     * @var array
     */
    protected $data = [];

    /**
     * The email attachments.
     *
     * @var array
     */
    protected $attachments = [];

    /**
     * Whether to queue the email.
     *
     * @var bool
     */
    protected $queue = true;

    /**
     * Delay for sending the email.
     *
     * @var \DateTimeInterface|\DateInterval|int|null
     */
    protected $delay = null;

    /**
     * The priority level of the email.
     *
     * @var int|null
     */
    protected $priority = null;

    /**
     * Whether this is a test email.
     *
     * @var bool
     */
    protected $isTest = false;

    /**
     * The Email Settings.
     *
     * @var EmailSettings
     */
    protected $settings;

    /**
     * The Template Renderer.
     *
     * @var TemplateRenderer
     */
    protected $templateRenderer;

    /**
     * The Email Tracking Service.
     *
     * @var EmailTrackingService
     */
    protected $trackingService;

    /**
     * Create a new EmailService instance.
     */
    public function __construct(
        ?EmailSettings $settings = null,
        ?TemplateRenderer $templateRenderer = null,
        ?EmailTrackingService $trackingService = null
    ) {
        $this->settings = $settings ?? app(EmailSettings::class);
        $this->templateRenderer = $templateRenderer ?? app(TemplateRenderer::class);
        $this->trackingService = $trackingService ?? app(EmailTrackingService::class);

        if ($this->settings) {
            $this->queue = $this->settings->queue_emails ?? true;
        }
    }

    /**
     * Set the recipients of the email.
     *
     * @param  string|array  $to
     * @return $this
     */
    public function to($to)
    {
        $this->to = $to;

        return $this;
    }

    /**
     * Set the cc recipients of the email.
     *
     * @param  string|array  $cc
     * @return $this
     */
    public function cc($cc)
    {
        $this->cc = $cc;

        return $this;
    }

    /**
     * Set the bcc recipients of the email.
     *
     * @param  string|array  $bcc
     * @return $this
     */
    public function bcc($bcc)
    {
        $this->bcc = $bcc;

        return $this;
    }

    /**
     * Set the reply-to address of the email.
     *
     * @return $this
     */
    public function replyTo(string $replyTo)
    {
        $this->replyTo = $replyTo;

        return $this;
    }

    /**
     * Set the from address of the email.
     *
     * @return $this
     */
    public function from(string $email, ?string $name = null)
    {
        $this->from = compact('email', 'name');

        return $this;
    }

    /**
     * Set the subject of the email.
     *
     * @return $this
     */
    public function subject(string $subject)
    {
        $this->subject = $subject;

        return $this;
    }

    /**
     * Set the template to use for the email.
     *
     * @param  string|int  $templateIdentifier  The template ID, slug, or name
     * @param  array  $data  Data for the template variables
     * @return $this
     */
    public function template($templateIdentifier, array $data = [])
    {
        $this->templateIdentifier = $templateIdentifier;

        if (! empty($data)) {
            $this->with($data);
        }

        return $this;
    }

    /**
     * Set direct HTML content for the email.
     *
     * @param  string  $content  HTML content
     * @param  array  $data  Data for variable substitution
     * @return $this
     */
    public function content(string $content, array $data = [])
    {
        $this->htmlContent = $content;

        if (! empty($data)) {
            $this->with($data);
        }

        return $this;
    }

    /**
     * Set the data for template variables.
     *
     * @return $this
     */
    public function with(array $data)
    {
        $this->data = array_merge($this->data, $data);

        return $this;
    }

    /**
     * Add an attachment to the email.
     *
     * @return $this
     */
    public function attach(string $path, array $options = [])
    {
        $this->attachments[] = [
            'path' => $path,
            'options' => $options,
        ];

        return $this;
    }

    /**
     * Add multiple attachments to the email.
     *
     * @return $this
     */
    public function attachments(array $attachments)
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
     * Set whether the email should be queued.
     *
     * @return $this
     */
    public function queue(bool $queue = true)
    {
        $this->queue = $queue;

        return $this;
    }

    /**
     * Schedule the email to be sent later.
     *
     * @param  \DateTimeInterface|\DateInterval|int  $delay
     * @return $this
     */
    public function later($delay)
    {
        $this->delay = $delay;

        return $this;
    }

    /**
     * Set the priority of the email.
     *
     * @return $this
     */
    public function priority(int $level)
    {
        $this->priority = $level;

        return $this;
    }

    /**
     * Mark this as a test email.
     *
     * @param  bool  $isTest  Whether this is a test email
     * @return $this
     */
    public function test(bool $isTest = true)
    {
        $this->isTest = $isTest;

        return $this;
    }

    /**
     * Create a test email for previewing a specific template
     *
     * @param  string  $templateSlug  The slug of the template to test
     * @return $this
     */
    public function testTemplate(string $templateSlug)
    {
        $this->isTest = true;
        $this->templateIdentifier = $templateSlug;

        // If no recipient is set for the test, use a default test email
        if (empty($this->to)) {
            $this->to = ['test@example.com'];
        }

        return $this;
    }

    /**
     * Send the email.
     *
     * @return bool|string True on success, error message on failure
     */
    public function send()
    {
        try {
            if (empty($this->to)) {
                throw new EmailException('No recipient specified');
            }

            // Get the template if a template identifier was provided
            $template = null;
            if ($this->templateIdentifier !== null) {
                $template = $this->findTemplate($this->templateIdentifier);

                if (! $template) {
                    throw new EmailException("Template not found: {$this->templateIdentifier}");
                }
            } elseif ($this->htmlContent === null) {
                throw new EmailException('No content or template specified');
            }

            // Create the email log
            $log = $this->createEmailLog($template);

            // Build the mailable
            $mailable = $this->buildMailable($template, $log);

            // Check if we should queue or schedule the email
            if ($this->delay !== null) {
                return $this->scheduleEmail($mailable, $log);
            } elseif ($this->queue) {
                return $this->queueEmail($mailable, $log);
            } else {
                return $this->sendNow($mailable, $log);
            }
        } catch (Exception $e) {
            // Log the error
            app_log('Failed to send email: ', 'error', $e, [
                'template' => $this->templateIdentifier,
                'recipient' => $this->to,
                'error' => $e->getMessage(),
            ]);

            // Fire event with error info
            event(new EmailFailed([
                'template' => $this->templateIdentifier,
                'to' => $this->to,
                'subject' => $this->subject,
                'error' => $e->getMessage(),
            ], $e->getMessage()));

            // Check if we should throw the exception
            if (config('laravel-emails.throw_exceptions', false)) {
                throw $e;
            }

            // Clear the state for the next email
            $this->resetState();

            return $e->getMessage();
        }
    }

    /**
     * Find a template by identifier (id, slug, or name).
     *
     * @param  string|int  $identifier
     * @return EmailTemplate|null
     */
    protected function findTemplate($identifier)
    {
        $query = EmailTemplate::query();

        // If the identifier is numeric, try to find by ID first
        if (is_numeric($identifier)) {
            $template = $query->where('id', $identifier)->first();
            if ($template) {
                return $template;
            }
        }

        // Try to find by slug or name
        return $query->where('slug', $identifier)
            ->orWhere('name', $identifier)
            ->first();
    }

    /**
     * Build the mailable instance.
     *
     * @return \Illuminate\Mail\Mailable
     */
    protected function buildMailable(?EmailTemplate $template, EmailLog $log)
    {
        // Default data from config
        $defaultData = config('laravel-emails.default_variables', []);
        $mergedData = array_merge($defaultData, $this->data);

        // Create the mailable
        if ($template) {
            // Render content with variables
            $content = $this->templateRenderer->render($template, $mergedData);

            // Apply tracking if enabled
            if ($this->settings->track_opens) {
                $content = $this->trackingService->addTrackingPixel($content, $log);
            }

            if ($this->settings->track_clicks) {
                $content = $this->trackingService->addLinkTracking($content, $log);
            }

            $mailable = new TemplatedEmail($content, $mergedData);

            // Set subject if not provided, using template subject
            if (! $this->subject && $template) {
                $subject = $this->templateRenderer->renderSubject($template, $mergedData);
                $mailable->subject($subject);
            }
        } else {
            // Direct content without template
            $content = $this->htmlContent;

            // Process variables in direct content
            if (! empty($mergedData)) {
                $content = $this->templateRenderer->render($content, $mergedData);
            }

            // Apply tracking if enabled
            if ($this->settings->track_opens) {
                $content = $this->trackingService->addTrackingPixel($content, $log);
            }

            if ($this->settings->track_clicks) {
                $content = $this->trackingService->addLinkTracking($content, $log);
            }

            $mailable = new TemplatedEmail($content, $mergedData);
        }

        // Set recipients
        $mailable->to($this->to);

        // Set subject if provided
        if ($this->subject) {
            $mailable->subject($this->subject);
        }

        // Set additional mail options
        if (! empty($this->cc)) {
            $mailable->cc($this->cc);
        }

        if (! empty($this->bcc)) {
            $mailable->bcc($this->bcc);
        }

        if ($this->replyTo) {
            $mailable->replyTo($this->replyTo);
        }

        if ($this->from) {
            $mailable->from($this->from['email'], $this->from['name']);
        }

        // Add attachments
        foreach ($this->attachments as $attachment) {
            $mailable->attach($attachment['path'], $attachment['options']);
        }

        // Set priority if specified
        if ($this->priority !== null) {
            $mailable->priority($this->priority);
        }

        return $mailable;
    }

    /**
     * Create an email log entry.
     *
     * @return EmailLog
     */
    protected function createEmailLog(?EmailTemplate $template)
    {
        // Get sender info from config or settings
        $from = $this->from ? $this->from['email'] : $this->getSenderEmail();

        // Get subject
        $subject = $this->subject;
        if (! $subject && $template) {
            $subject = $this->templateRenderer->renderSubject($template, $this->data);
        }

        // Format recipients
        $to = is_array($this->to) ? implode(', ', $this->to) : $this->to;
        $cc = is_array($this->cc) ? implode(', ', $this->cc) : $this->cc;
        $bcc = is_array($this->bcc) ? implode(', ', $this->bcc) : $this->bcc;

        // Create log entry
        return EmailLog::create([
            'email_template_id' => $template ? $template->id : null,
            'subject' => $subject,
            'from' => $from,
            'to' => $to,
            'cc' => $cc ?: null,
            'bcc' => $bcc ?: null,
            'reply_to' => $this->replyTo,
            'data' => ! empty($this->data) ? $this->data : null,
            'status' => 'pending',
            'is_test' => $this->isTest,
        ]);
    }

    /**
     * Send the email immediately.
     *
     * @param  \Illuminate\Mail\Mailable  $mailable
     * @return bool
     */
    protected function sendNow($mailable, EmailLog $log)
    {
        // Send the email
        Mail::send($mailable);

        // Update the log
        $log->update([
            'status' => 'sent',
            'sent_at' => now(),
        ]);

        // Fire event
        event(new EmailSent($log));

        // Clear the state for the next email
        $this->resetState();

        return true;
    }

    /**
     * Queue the email for sending.
     *
     * @param  \Illuminate\Mail\Mailable  $mailable
     * @return bool
     */
    protected function queueEmail($mailable, EmailLog $log)
    {
        // Set queue connection and name from config
        $connection = config('laravel-emails.queue.connection');
        $queue = config('laravel-emails.queue.default_queue');

        // Create and dispatch the SendEmailJob with the log ID
        dispatch(new \Corbital\LaravelEmails\Jobs\SendEmailJob($mailable, $log->id))
            ->onConnection($connection)
            ->onQueue($queue);

        // Update the log
        $log->update([
            'status' => 'queued',
        ]);

        // Clear the state for the next email
        $this->resetState();

        return true;
    }

    /**
     * Schedule the email for later delivery.
     *
     * @param  \Illuminate\Mail\Mailable  $mailable
     * @return bool
     */
    protected function scheduleEmail($mailable, EmailLog $log)
    {
        // Set queue connection and name from config
        $connection = config('laravel-emails.queue.connection');
        $queue = config('laravel-emails.queue.default_queue');

        // Calculate the scheduled time
        $scheduledAt = $this->calculateScheduledTime($this->delay);

        // Create and dispatch the SendEmailJob with delay and log ID
        dispatch(new \Corbital\LaravelEmails\Jobs\SendEmailJob($mailable, $log->id))
            ->delay($this->delay)
            ->onConnection($connection)
            ->onQueue($queue);

        // Update the log
        $log->update([
            'status' => 'scheduled',
            'scheduled_at' => $scheduledAt,
        ]);

        // Fire event
        event(new EmailScheduled($log));

        // Clear the state for the next email
        $this->resetState();

        return true;
    }

    /**
     * Calculate the scheduled time from a delay value.
     *
     * @param  \DateTimeInterface|\DateInterval|int  $delay
     * @return \Carbon\Carbon
     */
    protected function calculateScheduledTime($delay)
    {
        if ($delay instanceof \DateTimeInterface) {
            return \Carbon\Carbon::instance($delay);
        }

        if ($delay instanceof \DateInterval) {
            return now()->add($delay);
        }

        return now()->addSeconds($delay);
    }

    /**
     * Get the sender email from settings or config.
     *
     * @return string
     */
    protected function getSenderEmail()
    {
        try {
            $settings = app(EmailSettings::class);
            $senderEmail = $settings->sender_email ?? null;

            // If settings doesn't have a value, use config
            if (empty($senderEmail)) {
                $senderEmail = config('mail.from.address');
            }

            // If still empty, use a default
            if (empty($senderEmail)) {
                $senderEmail = 'noreply@example.com';
            }

            return $senderEmail;
        } catch (\Exception $e) {
            // Fallback to config
            return config('mail.from.address', 'noreply@example.com');
        }
    }

    /**
     * Reset the state of the service for the next email.
     *
     * @return void
     */
    protected function resetState()
    {
        $this->to = [];
        $this->cc = [];
        $this->bcc = [];
        $this->replyTo = null;
        $this->from = null;
        $this->subject = null;
        $this->templateIdentifier = null;
        $this->htmlContent = null;
        $this->data = [];
        $this->attachments = [];
        $this->queue = true;
        $this->delay = null;
        $this->priority = null;
        $this->isTest = false;
    }

    /**
     * Get a template by ID or slug.
     *
     * @param  string|int  $templateIdentifier
     * @return \Corbital\LaravelEmails\Models\EmailTemplate|null
     */
    public function getTemplate($templateIdentifier)
    {
        return $this->findTemplate($templateIdentifier);
    }

    /**
     * Get all email templates.
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getAllTemplates()
    {
        return EmailTemplate::all();
    }

    /**
     * Render a preview of an email template with data.
     *
     * @param  string|int  $templateIdentifier
     * @return string
     */
    public function renderPreview($templateIdentifier, array $data = [])
    {
        $template = $this->findTemplate($templateIdentifier);

        if (! $template) {
            return "Template not found: {$templateIdentifier}";
        }

        // Merge default variables with provided data
        $defaultData = config('laravel-emails.default_variables', []);
        $mergedData = array_merge($defaultData, $data);

        return $this->templateRenderer->render($template, $mergedData);
    }

    // Getter methods
    public function getTo()
    {
        return $this->to;
    }

    public function getFrom()
    {
        return $this->from;
    }

    public function getSubject()
    {
        return $this->subject;
    }

    public function isTest()
    {
        return $this->isTest;
    }

    public function isQueueEnabled()
    {
        return $this->queue;
    }
}
