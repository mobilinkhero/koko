<?php

namespace Corbital\LaravelEmails\Commands;

use Corbital\LaravelEmails\Exceptions\EmailException;
use Corbital\LaravelEmails\Facades\Email;
use Corbital\LaravelEmails\Models\EmailTemplate;
use Corbital\LaravelEmails\Settings\EmailSettings;
use Illuminate\Console\Command;

class SendTestEmailCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'email:send-test
                            {--template= : The ID or slug of the template to use}
                            {--to= : Email address to send the test to}
                            {--subject= : Custom subject for the test email}
                            {--html= : Path to a custom HTML file to use instead of a template}
                            {--var=* : Variables to include in the format key:value}
                            {--from= : Sender email address (optional)}
                            {--from-name= : Sender name (optional)}
                            {--no-queue : Send immediately without queueing}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send a test email using a template or custom HTML';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->info('Preparing to send test email...');

        try {
            // Get required parameters
            $to = $this->option('to');

            if (! $to) {
                $to = $this->ask('Enter recipient email address');

                if (! $to) {
                    $this->error('Recipient email address is required');

                    return 1;
                }
            }

            // Get sender information from options or settings
            $from = $this->option('from');
            $fromName = $this->option('from-name');

            if (! $from) {
                try {
                    $settings = app(EmailSettings::class);
                    $from = $settings->sender_email ?? null;
                    $fromName = $settings->sender_name ?? null;
                } catch (\Exception $e) {
                    $this->warn('Email settings not found, using config defaults');
                    $from = config('mail.from.address');
                    $fromName = config('mail.from.name');
                }

                if (! $from) {
                    $from = $this->ask('Enter sender email address');
                    if (! $from) {
                        $this->error('Sender email address is required');

                        return 1;
                    }
                }
            }

            // Get the subject
            $subject = $this->option('subject');
            if (! $subject) {
                $subject = $this->ask('Enter email subject', 'Test Email');
            }

            // Get template if provided
            $templateId = $this->option('template');
            $htmlPath = $this->option('html');

            if (! $templateId && ! $htmlPath) {
                // Ask which method to use
                $method = $this->choice(
                    'How would you like to send this test email?',
                    ['Use an existing template', 'Use custom HTML content'],
                    0
                );

                if ($method === 'Use an existing template') {
                    // Get available templates
                    $templates = EmailTemplate::all();

                    if ($templates->isEmpty()) {
                        $this->error('No email templates found');

                        return 1;
                    }

                    $templateChoices = $templates->pluck('name', 'slug')->toArray();
                    $templateId = $this->choice('Select a template', $templateChoices);
                } else {
                    $htmlPath = $this->ask('Enter path to HTML file');

                    if (! $htmlPath || ! file_exists($htmlPath)) {
                        $this->error('HTML file not found');

                        return 1;
                    }
                }
            }

            // Process variables
            $variables = $this->processVariables();

            // Log what we're about to do
            $this->info('Sending test email:');
            $this->info("  From: $from".($fromName ? " ($fromName)" : ''));
            $this->info("  To: $to");
            $this->info("  Subject: $subject");

            // Initialize email with key properties
            $email = Email::to($to);

            if ($this->option('no-queue')) {
                $email->queue(false);
            }

            // CRITICAL: Explicitly set these properties - they weren't being set in your original command
            $email->subject($subject);
            $email->from($from, $fromName);
            $email->test(true);  // This was missing - isTest was false

            // Set content source (template or HTML)
            if ($templateId) {
                // Try to find the template
                $template = null;

                if (is_numeric($templateId)) {
                    $template = EmailTemplate::find($templateId);
                } else {
                    $template = EmailTemplate::where('slug', $templateId)
                        ->orWhere('name', $templateId)
                        ->first();
                }

                if (! $template) {
                    $this->error("Template not found: {$templateId}");

                    return 1;
                }

                $this->info("Using template: {$template->name} (ID: {$template->id})");

                // Use the template
                $email->template($template->slug, $variables);
            } elseif ($htmlPath) {
                if (! file_exists($htmlPath)) {
                    $this->error("HTML file not found: {$htmlPath}");

                    return 1;
                }

                $htmlContent = file_get_contents($htmlPath);
                $this->info('Using HTML content from file');

                // Use direct content
                $email->content($htmlContent, $variables);
            } else {
                $this->error('Either a template or HTML content must be provided');

                return 1;
            }

            // Send the email immediately (not queued)
            $email->queue(false);

            // Send the email
            $this->info('Sending email...');
            $result = $email->send();

            if ($result === true) {
                $this->info('Test email sent successfully!');

                return 0;
            } else {
                $this->error("Failed to send test email: {$result}");

                return 1;
            }
        } catch (EmailException $e) {
            $this->error("Email Error: {$e->getMessage()}");
            app_log('Email Exceprion', 'error', $e, [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return 1;
        } catch (\Exception $e) {
            $this->error("Unexpected error: {$e->getMessage()}");
            app_log('Unexpected Exception', 'error', $e, [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return 1;
        }
    }

    /**
     * Process variables from the command options.
     */
    protected function processVariables(): array
    {
        $variables = [];

        // Process --var options
        foreach ($this->option('var') as $var) {
            if (strpos($var, ':') !== false) {
                [$key, $value] = explode(':', $var, 2);
                $variables[trim($key)] = trim($value);
            }
        }

        // Add some default variables if not set
        if (! isset($variables['app_name'])) {
            $variables['app_name'] = config('app.name');
        }

        if (! isset($variables['name'])) {
            $variables['name'] = 'Test User';
        }

        return $variables;
    }
}
