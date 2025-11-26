<?php

namespace Corbital\LaravelEmails\Http\Controllers;

use App\Http\Controllers\Controller;
use Corbital\LaravelEmails\Settings\EmailSettings;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class EmailSettingsController extends Controller
{
    /**
     * Display email settings page.
     */
    public function index(EmailSettings $settings): View
    {
        // Get available mail mailers
        $mailers = ['smtp', 'mailgun', 'postmark', 'ses', 'sendmail', 'log', 'array'];

        // Get available queue connections
        $queueConnections = array_keys(config('queue.connections', ['sync' => 'Sync', 'database' => 'Database']));

        return view('laravel-emails::settings.index', [
            'settings' => $settings,
            'mailers' => $mailers,
            'queueConnections' => $queueConnections,
        ]);
    }

    /**
     * Test SMTP connection with provided credentials.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function testSmtpConnection(Request $request)
    {
        $credentials = $request->validate([
            'mail_mailer' => 'required|string',
            'mail_host' => 'required|string',
            'mail_port' => 'required|integer',
            'mail_username' => 'nullable|string',
            'mail_password' => 'nullable|string',
            'mail_encryption' => 'nullable|string',
            'test_email' => 'nullable|email',
        ]);

        try {
            // Configure temporary mail settings for the test
            config([
                'mail.default' => $credentials['mail_mailer'],
                'mail.mailers.smtp.host' => $credentials['mail_host'],
                'mail.mailers.smtp.port' => $credentials['mail_port'],
                'mail.mailers.smtp.username' => $credentials['mail_username'],
                'mail.mailers.smtp.password' => $credentials['mail_password'],
                'mail.mailers.smtp.encryption' => $credentials['mail_encryption'],
            ]);

            // Test the connection - Laravel 9+ compatible
            $mailer = app('mailer');

            if (isset($credentials['test_email']) && ! empty($credentials['test_email'])) {
                // If a test email is provided, try sending an actual test email
                Mail::raw('This is a test email from your application to verify email settings are working correctly.', function ($message) use ($credentials) {
                    $message->to($credentials['test_email'])
                        ->subject('SMTP Settings Test Email');
                });

                return response()->json([
                    'success' => true,
                    'message' => 'SMTP connection successful! A test email has been sent to '.$credentials['test_email'],
                ]);
            } else {
                // Just verify connection without sending email
                // For newer Laravel versions (9+), we need to use the transport directly
                if (method_exists($mailer, 'getSymfonyTransport')) {
                    // Laravel 9+ approach
                    $transport = $mailer->getSymfonyTransport();
                    if (method_exists($transport, 'start')) {
                        $transport->start();
                    }
                } elseif (method_exists($mailer, 'getSwiftMailer')) {
                    // Legacy approach for older Laravel
                    $mailer->getSwiftMailer()->getTransport()->start();
                }

                return response()->json([
                    'success' => true,
                    'message' => 'SMTP connection successful! (No email was sent)',
                ]);
            }
        } catch (\Exception $e) {
            $errorMessage = $e->getMessage();

            // Extract user-friendly error message
            $friendlyMessage = $this->parseSmtpError($errorMessage);

            return response()->json([
                'success' => false,
                'message' => $friendlyMessage,
                'details' => $errorMessage, // Keep full details for advanced users
            ]);
        }
    }

    /**
     * Parse SMTP error messages to provide user-friendly versions.
     */
    private function parseSmtpError(string $error): string
    {
        // Authentication errors
        if (str_contains($error, 'Username and Password not accepted')) {
            return 'Authentication failed: The email username or password is incorrect.';
        }

        // Connection errors
        if (str_contains($error, 'Connection could not be established')) {
            return 'Connection failed: Could not establish a connection to the mail server.';
        }

        // SSL/TLS errors
        if (str_contains($error, 'SSL')) {
            return 'SSL/TLS Error: There was an issue with the secure connection. Try a different encryption setting.';
        }

        // Timeout issues
        if (str_contains($error, 'timed out')) {
            return 'Connection timed out: The mail server took too long to respond.';
        }

        // Default message for other errors
        return 'SMTP Error: There was a problem connecting to the mail server. Please check your settings.';
    }

    /**
     * Save email settings.
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function saveSettings(Request $request, EmailSettings $settings)
    {
        try {
            $validated = $request->validate([
                'sender_name' => 'required|string|max:255',
                'sender_email' => 'required|email|max:255',
                'default_layout_template' => 'nullable|string|max:255',
                'email_signature' => 'nullable|string',
                'max_email_retries' => 'integer|min:1|max:10',
                'queue_connection' => 'string',
                'queue_name' => 'string',
                'log_retention_days' => 'integer|min:1',

                'mail_mailer' => 'required|string',
                'mail_host' => 'required|string',
                'mail_port' => 'required|integer',
                'mail_username' => 'nullable|string',
                'mail_password' => 'nullable|string',
                'mail_encryption' => 'nullable|string',
            ]);            // Handle boolean fields that might not be present in request
            $booleanFields = [
                'queue_emails', 'enable_scheduling',
            ];

            foreach ($booleanFields as $field) {
                $settings->{$field} = $request->has($field);
            }

            // Handle regular fields
            foreach ($validated as $key => $value) {
                if (! in_array($key, $booleanFields)) {
                    // Don't update password if empty (keep existing)
                    if ($key === 'mail_password' && empty($value)) {
                        continue;
                    }

                    $settings->{$key} = $value;
                }
            }

            $settings->save();

            // Dispatch event that settings changed
            event(new \Corbital\LaravelEmails\Events\EmailConfigurationChanged($validated));

            return redirect()->back()->with('success', 'Email settings saved successfully!');
        } catch (ValidationException $e) {
            // Get all the validation errors
            $errors = $e->errors();

            // Or get the validator instance
            $validator = $e->validator;

            // Do something with the errors
            return response()->json(['errors' => $errors], 422);
        }

    }
}
