<?php

namespace App\Helpers;

use Corbital\LaravelEmails\Models\EmailTemplate;

class EmailTemplateHelper
{
    /**
     * Ensure the test email template exists
     */
    public static function ensureTestTemplateExists(): EmailTemplate
    {
        $testTemplate = EmailTemplate::where('slug', 'smtp-test')->first();

        if (! $testTemplate) {
            $testTemplate = EmailTemplate::create([
                'name' => 'SMTP Test Email',
                'slug' => 'smtp-test',
                'subject' => 'SMTP Test from '.config('app.name'),
                'message' => self::getTestEmailHtml(),
                'merge_fields_groups' => json_encode(['app_name', 'app_url', 'recipient_email']),
                'is_active' => true,
            ]);
        }

        return $testTemplate;
    }

    /**
     * Get HTML content for test email
     */
    private static function getTestEmailHtml(): string
    {
        return '
        <div style="font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto; padding: 20px; border: 1px solid #eee; border-radius: 5px;">
            <h2 style="color: #333;">SMTP Configuration Test</h2>
            <p>Hello,</p>
            <p>This is a test email sent from <strong>{{ app_name }}</strong> to verify that your SMTP settings have been configured correctly.</p>
            <p>This email was sent to: <strong>{{ recipient_email }}</strong></p>
            <p>If you have received this email, your SMTP setup is functioning properly.</p>
            <hr style="border: none; border-top: 1px solid #eee; margin: 20px 0;">
            <p style="color: #777; font-size: 12px;">This is an automated message from {{ app_name }}. Please do not reply to this email.</p>
            <p style="text-align: center;"><a href="{{ app_url }}" style="color: #3490dc; text-decoration: none;">{{ app_url }}</a></p>
        </div>';
    }
}
