<?php

namespace Corbital\Settings\Traits;

use Corbital\Settings\Exceptions\SettingsException;
use Illuminate\Support\Str;

/**
 * Trait HasSettings
 *
 * Add this trait to a model to enable settings management for model instances.
 */
trait HasSettings
{
    /**
     * Get the model's settings.
     *
     * @throws SettingsException
     */
    public function getSettings(?string $key = null, mixed $default = null): mixed
    {
        $prefix = $this->getSettingsPrefix();

        if ($key) {
            return settings("{$prefix}.{$key}", null, $default);
        }

        return get_settings_by_group($prefix);
    }

    /**
     * Set the model's settings.
     *
     * @throws SettingsException
     */
    public function setSettings(string|array $key, mixed $value = null): bool
    {
        $prefix = $this->getSettingsPrefix();

        if (is_array($key)) {
            return set_settings_batch($prefix, $key);
        }

        return set_setting("{$prefix}.{$key}", $value);
    }

    /**
     * Get the settings prefix for the model.
     */
    protected function getSettingsPrefix(): string
    {
        if (method_exists($this, 'getSettingsGroupName')) {
            return $this->getSettingsGroupName();
        }

        $modelName = class_basename($this);

        return Str::kebab($modelName).'_'.$this->getKey();
    }
}
