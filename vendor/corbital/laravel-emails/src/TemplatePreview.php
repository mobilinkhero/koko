<?php

namespace Corbital\LaravelEmails;

use Corbital\LaravelEmails\Mail\TemplateMail;
use Corbital\LaravelEmails\Models\EmailTemplate;
use Illuminate\Support\Facades\Mail;

class TemplatePreview
{
    /**
     * Preview an email template
     *
     * @param  string  $slug  The template slug
     * @param  array  $data  The sample data to use
     * @param  bool  $renderInLayout  Whether to render in the full layout or just return the content
     * @return string The rendered HTML
     */
    public static function render($slug, array $data = [], $renderInLayout = true)
    {
        // Get the template
        $template = EmailTemplate::where('slug', $slug)->first();
        if (! $template) {
            throw new \Exception("Template with slug '{$slug}' not found");
        }

        // Create the mailable using our TemplateMail class
        $mail = new TemplateMail($slug, $data);

        // If we want just the content without the layout
        if (! $renderInLayout) {
            return $mail->body;
        }

        // Otherwise return the fully rendered template
        return $mail->render();
    }

    /**
     * Preview multiple templates at once
     *
     * @param  array  $templates  Array of template slugs
     * @param  array  $data  Sample data to use for all templates
     * @return array Array of rendered templates keyed by slug
     */
    public static function renderMultiple(array $templates, array $data = [])
    {
        $results = [];

        foreach ($templates as $slug) {
            try {
                $results[$slug] = self::render($slug, $data);
            } catch (\Exception $e) {
                $results[$slug] = 'Error: '.$e->getMessage();
            }
        }

        return $results;
    }

    /**
     * Get a preview of how a test email would look
     *
     * @param  string  $slug  The template slug
     * @param  array  $data  Sample data to use
     * @return TemplateMail
     */
    public static function getTestMail($slug, array $data = [])
    {
        return new TemplateMail($slug, $data);
    }

    /**
     * Send a test email
     *
     * @param  string  $slug  The template slug
     * @param  string  $toEmail  Email address to send test to
     * @param  array  $data  Sample data to use
     * @return bool
     */
    public static function sendTest($slug, $toEmail, array $data = [])
    {
        try {
            $mail = new TemplateMail($slug, $data);
            Mail::to($toEmail)->send($mail);

            return true;
        } catch (\Exception $e) {

            return false;
        }
    }
}
