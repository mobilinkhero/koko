<?php

namespace App\Events;

use Illuminate\Foundation\Events\Dispatchable;

class PaymentSettingsExtending
{
    use Dispatchable;

    public array $extensions = [];

    public function addExtension(string $key, mixed $defaultValue): void
    {
        $this->extensions[$key] = $defaultValue;
    }

    public function getExtensions(): array
    {
        return $this->extensions;
    }
}
