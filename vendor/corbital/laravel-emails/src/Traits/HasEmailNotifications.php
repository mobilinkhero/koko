<?php

namespace Corbital\LaravelEmails\Traits;

use Corbital\LaravelEmails\Facades\Email;

trait HasEmailNotifications
{
    /**
     * Send an email notification to this model.
     *
     * @param  string  $templateId  The email template ID
     * @param  array  $data  Additional data to include in the email
     * @param  array  $options  Additional options (priority, queue, etc.)
     * @return bool|string
     */
    public function sendEmailNotification(string $templateId, array $data = [], array $options = [])
    {
        if (! $this->hasEmailAddress()) {
            return 'No email address available for this model';
        }

        // Merge model data with provided data
        $mergedData = array_merge($this->getNotificationData(), $data);

        // Build the email
        $email = Email::to($this->getEmailAddress())
            ->template($templateId, $mergedData);

        // Add optional parameters
        if (isset($options['subject'])) {
            $email->subject($options['subject']);
        }

        if (isset($options['cc'])) {
            $email->cc($options['cc']);
        }

        if (isset($options['bcc'])) {
            $email->bcc($options['bcc']);
        }

        if (isset($options['replyTo'])) {
            $email->replyTo($options['replyTo']);
        }

        if (isset($options['attachments'])) {
            $email->attachments($options['attachments']);
        }

        if (isset($options['queue']) && $options['queue'] === false) {
            $email->queue(false);
        }

        if (isset($options['delay'])) {
            $email->later($options['delay']);
        }

        if (isset($options['priority'])) {
            $email->priority($options['priority']);
        }

        // Send the email
        return $email->send();
    }

    /**
     * Get the email address for the notification.
     *
     * @return string|null
     */
    public function getEmailAddress()
    {
        return $this->email ?? $this->email_address ?? null;
    }

    /**
     * Check if this model has an email address.
     *
     * @return bool
     */
    public function hasEmailAddress()
    {
        return ! empty($this->getEmailAddress());
    }

    /**
     * Get additional data to include in the notification.
     *
     * @return array
     */
    public function getNotificationData()
    {
        return [
            'id' => $this->id,
            'model' => class_basename($this),
        ];
    }

    /**
     * Schedule an email notification.
     *
     * @param  string  $templateId  The email template ID
     * @param  \DateTime|\DateTimeInterface|string  $scheduledAt  When to send the notification
     * @param  array  $data  Additional data to include in the email
     * @param  array  $options  Additional options
     * @return bool|string
     */
    public function scheduleEmailNotification(string $templateId, $scheduledAt, array $data = [], array $options = [])
    {
        if (! $this->hasEmailAddress()) {
            return 'No email address available for this model';
        }

        // Build the options with the delay
        $options['delay'] = $scheduledAt;

        // Use the regular notification method with the delay option
        return $this->sendEmailNotification($templateId, $data, $options);
    }
}
