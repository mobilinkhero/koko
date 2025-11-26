<?php

namespace Corbital\LaravelEmails\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class EmailConfigurationChanged
{
    use Dispatchable, SerializesModels;

    /**
     * The settings that were changed.
     */
    public array $settings;

    /**
     * Create a new event instance.
     */
    public function __construct(array $settings)
    {
        $this->settings = $settings;
    }
}
