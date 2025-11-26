<?php

namespace App\Events\Language;

use App\Models\Language;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class LanguageEvent
{
    use Dispatchable;
    use SerializesModels;

    public const ACTION_CREATED = 'created';

    public const ACTION_UPDATED = 'updated';

    public const ACTION_DELETED = 'deleted';

    public function __construct(
        public string $action,
        public ?Language $language = null,
        public ?string $languageCode = null,
        public ?string $fromLanguage = null,
        public ?string $toLanguage = null,
        public bool $persisted = false,
        public ?int $userId = null
    ) {
        // Auto-set language code if language model is provided
        if ($this->language && ! $this->languageCode) {
            $this->languageCode = $this->language->code;
        }
    }

    /**
     * Create a language created event
     */
    public static function created(Language $language): self
    {
        return new self(
            action: self::ACTION_CREATED,
            language: $language
        );
    }

    /**
     * Create a language updated event
     */
    public static function updated(Language $language): self
    {
        return new self(
            action: self::ACTION_UPDATED,
            language: $language
        );
    }

    /**
     * Create a language deleted event
     */
    public static function deleted(Language $language): self
    {
        return new self(
            action: self::ACTION_DELETED,
            language: $language
        );
    }

    /**
     * Check if this is a create action
     */
    public function isCreated(): bool
    {
        return $this->action === self::ACTION_CREATED;
    }

    /**
     * Check if this is an update action
     */
    public function isUpdated(): bool
    {
        return $this->action === self::ACTION_UPDATED;
    }

    /**
     * Check if this is a delete action
     */
    public function isDeleted(): bool
    {
        return $this->action === self::ACTION_DELETED;
    }

    /**
     * Get the affected language code
     */
    public function getLanguageCode(): ?string
    {
        return $this->toLanguage ?? $this->languageCode;
    }
}
