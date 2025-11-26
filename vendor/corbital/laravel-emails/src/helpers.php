<?php

use Corbital\LaravelEmails\Facades\Email;
use Corbital\LaravelEmails\Models\EmailTemplate;

if (! function_exists('send_email')) {
    /**
     * Send an email using the email service.
     *
     * @param  string|array  $to  The recipient(s)
     * @param  string  $subject  The email subject
     * @param  string|null  $templateId  The template ID (optional)
     * @param  array  $data  The template data (optional)
     * @param  string|null  $content  The HTML content if not using a template (optional)
     * @param  array  $options  Additional options (cc, bcc, from, replyTo, attachments, queue, delay)
     * @return bool|string
     */
    function send_email($to, string $subject, ?string $templateId = null, array $data = [], ?string $content = null, array $options = [])
    {
        $email = Email::to($to)->subject($subject);

        // Set optional parameters
        if (isset($options['cc'])) {
            $email->cc($options['cc']);
        }

        if (isset($options['bcc'])) {
            $email->bcc($options['bcc']);
        }

        if (isset($options['from'])) {
            if (is_array($options['from'])) {
                $email->from($options['from']['email'], $options['from']['name'] ?? null);
            } else {
                $email->from($options['from']);
            }
        }

        if (isset($options['replyTo'])) {
            $email->replyTo($options['replyTo']);
        }

        if (! empty($options['attachments'])) {
            $email->attachments($options['attachments']);
        }

        if (isset($options['queue']) && is_bool($options['queue'])) {
            $email->queue($options['queue']);
        }

        if (! empty($options['delay'])) {
            $email->later($options['delay']);
        }

        if (! empty($options['priority'])) {
            $email->priority($options['priority']);
        }

        if (isset($options['test']) && $options['test']) {
            $email->test(true);
        }

        // Set content using template or direct HTML
        if ($templateId) {
            $email->template($templateId, $data);
        } elseif ($content) {
            $email->content($content, $data);
        } else {
            throw new \InvalidArgumentException('Either templateId or content must be provided');
        }

        return $email->send();
    }
}

if (! function_exists('get_email_template')) {
    /**
     * Get an email template by ID or slug.
     *
     * @return EmailTemplate|null
     */
    function get_email_template(string $templateId)
    {
        return Email::getTemplate($templateId);
    }
}

if (! function_exists('get_all_email_templates')) {
    /**
     * Get all email templates.
     *
     * @param  array  $filters  Optional filters (e.g. ['active' => true, 'category' => 'system'])
     * @return \Illuminate\Database\Eloquent\Collection
     */
    function get_all_email_templates(array $filters = [])
    {
        $query = EmailTemplate::query();

        if (isset($filters['active'])) {
            $query->where('is_active', $filters['active']);
        }

        if (isset($filters['system'])) {
            $query->where('is_system', $filters['system']);
        }

        if (isset($filters['category'])) {
            $query->where('category', $filters['category']);
        }

        return $query->get();
    }
}

if (! function_exists('preview_email_template')) {
    /**
     * Preview an email template with data.
     *
     * @return string
     */
    function preview_email_template(string $templateId, array $data = [])
    {
        return Email::renderPreview($templateId, $data);
    }
}

if (! function_exists('schedule_email')) {
    /**
     * Schedule an email to be sent later.
     *
     * @param  string|array  $to  The recipient(s)
     * @param  string  $subject  The email subject
     * @param  string  $templateId  The template ID
     * @param  array  $data  The template data
     * @param  \DateTimeInterface|\DateInterval|int  $delay  When to send the email
     * @param  array  $options  Additional options (cc, bcc, from, replyTo, attachments)
     * @return bool|string
     */
    function schedule_email($to, string $subject, string $templateId, array $data, $delay, array $options = [])
    {
        $options['delay'] = $delay;

        return send_email($to, $subject, $templateId, $data, null, $options);
    }
}

if (! function_exists('create_email_template')) {
    /**
     * Create a new email template.
     *
     * @param  string  $name  Template name
     * @param  string  $subject  Email subject
     * @param  string  $content  HTML content
     * @param  array  $options  Additional options (slug, description, variables, category, is_active)
     * @return EmailTemplate
     */
    function create_email_template(string $name, string $subject, string $content, array $options = [])
    {
        $data = [
            'name' => $name,
            'subject' => $subject,
            'content' => $content,
            'is_active' => $options['is_active'] ?? true,
            'slug' => $options['slug'] ?? \Illuminate\Support\Str::slug($name),
            'description' => $options['description'] ?? null,
            'variables' => $options['variables'] ?? [],
            'category' => $options['category'] ?? null,
            'is_system' => $options['is_system'] ?? false,
        ];

        return EmailTemplate::create($data);
    }
}

if (! function_exists('update_email_template')) {
    /**
     * Update an existing email template.
     *
     * @param  string|int  $templateId  The template ID or slug
     * @param  array  $data  The data to update
     * @return EmailTemplate|null
     */
    function update_email_template($templateId, array $data)
    {
        $template = get_email_template($templateId);

        if ($template) {
            $template->update($data);
        }

        return $template;
    }
}

if (! function_exists('delete_email_template')) {
    /**
     * Delete an email template.
     *
     * @param  string|int  $templateId  The template ID or slug
     * @return bool
     */
    function delete_email_template($templateId)
    {
        $template = get_email_template($templateId);

        if ($template && ! $template->is_system) {
            return $template->delete();
        }

        return false;
    }
}

if (! function_exists('preview_email_template')) {
    /**
     * Preview an email template with sample data
     *
     * @param  string  $slug  The template slug
     * @param  array  $data  Sample data to use
     * @param  bool  $renderInLayout  Whether to render in full layout
     * @return string The rendered HTML
     */
    function preview_email_template($slug, array $data = [], $renderInLayout = true)
    {
        return \Corbital\LaravelEmails\TemplatePreview::render($slug, $data, $renderInLayout);
    }
}

if (! function_exists('test_email_template')) {
    /**
     * Send a test email
     *
     * @param  string  $slug  The template slug
     * @param  string  $toEmail  Email address to send test to
     * @param  array  $data  Sample data to use
     * @return bool
     */
    function test_email_template($slug, $toEmail, array $data = [])
    {
        return \Corbital\LaravelEmails\TemplatePreview::sendTest($slug, $toEmail, $data);
    }
}

if (! function_exists('get_email_tempalte_groups')) {
    /**
     * Render an email template with dynamic data.
     *
     * @param  string  $slug  The template slug
     * @return array parsed subject
     */
    function get_email_tempalte_groups($slug, $table = null)
    {
        if ($table) {
            $template = EmailTemplate::fromTable($table)->where('slug', $slug)->first();
        } else {
            $template = EmailTemplate::where('slug', $slug)->first();
        }

        if (! $template) {
            throw new \Exception("Email template with slug '{$slug}' not found.");
        }

        return $template->merge_fields_groups;
    }
}

if (! function_exists('get_email_subject')) {
    /**
     * Render an email template with dynamic data.
     *
     * @param  string  $slug  The template slug
     * @param  array  $data  Custom data to be used in the template
     * @return string parsed subject
     */
    function get_email_subject($slug, array $data = [], $table = null)
    {
        if ($table) {
            $template = EmailTemplate::fromTable($table)->where(['slug' => $slug, 'tenant_id' => $data['tenantId']])->first();
        } else {
            $template = EmailTemplate::where('slug', $slug)->first();
        }

        if (! $template) {
            throw new \Exception("Email template with slug '{$slug}' not found.");
        }

        // Render the template with all the data
        $html = $template->renderSubject($data);

        return $html;
    }
}
