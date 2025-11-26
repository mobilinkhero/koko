<?php

namespace App\DTOs;

/**
 * Data Transfer Object for WhatsApp Messages
 */
class WhatsAppMessage
{
    /**
     * @var string The recipient phone number
     */
    public string $to;

    /**
     * @var string The message type (text, template, image, etc.)
     */
    public string $type;

    /**
     * @var array The message content data
     */
    public array $content;

    /**
     * @var array|null Additional metadata for the message
     */
    public ?array $metadata = null;

    /**
     * @var string|null Campaign ID if applicable
     */
    public ?string $campaignId = null;

    /**
     * @var string|null Template ID if applicable
     */
    public ?string $templateId = null;

    /**
     * Create a new WhatsApp message instance
     *
     * @param  string  $to  The recipient phone number
     * @param  string  $type  The message type
     * @param  array  $content  The message content
     * @param  array|null  $metadata  Additional metadata
     * @param  string|null  $campaignId  Campaign ID
     * @param  string|null  $templateId  Template ID
     */
    public function __construct(
        string $to,
        string $type,
        array $content,
        ?array $metadata = null,
        ?string $campaignId = null,
        ?string $templateId = null
    ) {
        $this->to = $to;
        $this->type = $type;
        $this->content = $content;
        $this->metadata = $metadata;
        $this->campaignId = $campaignId;
        $this->templateId = $templateId;
    }

    /**
     * Convert the message to an array
     */
    public function toArray(): array
    {
        return [
            'to' => $this->to,
            'type' => $this->type,
            'content' => $this->content,
            'metadata' => $this->metadata,
            'campaign_id' => $this->campaignId,
            'template_id' => $this->templateId,
        ];
    }
}
