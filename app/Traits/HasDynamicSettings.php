<?php

namespace App\Traits;

use App\Events\PaymentSettingsExtending;
use Illuminate\Support\Str;
use Spatie\LaravelSettings\SettingsRepositories\SettingsRepository;

trait HasDynamicSettings
{
    protected array $dynamicSettings = [];

    protected bool $dynamicSettingsLoaded = false;

    protected array $dynamicPropertyCache = [];

    /**
     * Get the settings group name based on the class name.
     */
    protected function getSettingsGroup(): string
    {
        $className = class_basename(static::class);

        return Str::kebab(str_replace('Settings', '', $className));
    }

    /**
     * Load dynamic settings from the configuration.
     */
    protected function loadDynamicSettings(): void
    {
        if ($this->dynamicSettingsLoaded) {
            return;
        }

        $event = new PaymentSettingsExtending;
        event($event);

        $this->dynamicSettings = $event->getExtensions();
        $this->dynamicSettingsLoaded = true;
    }

    /**
     * Check if a property is dynamic
     */
    protected function isDynamicProperty(string $name): bool
    {
        $this->loadDynamicSettings();

        return array_key_exists($name, $this->dynamicSettings);
    }

    /**
     * Get setting value from repository or use default
     */
    protected function getFromRepository(string $group, string $key, mixed $defaultValue = null): mixed
    {
        try {
            $repository = app(SettingsRepository::class);

            // Check if the setting exists in the repository
            if ($repository->checkIfPropertyExists($group, $key)) {
                return $repository->getPropertyPayload($group, $key);
            }

            return $defaultValue;
        } catch (\Exception $e) {
            return $defaultValue;
        }
    }

    /**
     * Set dynamic property in repository
     */
    protected function setInRepository(string $group, string $key, mixed $value): void
    {
        try {
            $repository = app(SettingsRepository::class);

            // Check if property exists, if so update it, otherwise create it
            if ($repository->checkIfPropertyExists($group, $key)) {
                // Update existing property
                $repository->updatePropertiesPayload($group, [$key => $value]);
            } else {
                // Create new property
                $repository->createProperty($group, $key, $value);
            }
        } catch (\Exception $e) {
            // Handle error - could log or throw depending on your needs
            throw new \RuntimeException("Failed to save setting {$group}.{$key}: ".$e->getMessage());
        }
    }

    /**
     * Handle dynamic property access
     */
    public function __get($name)
    {
        if ($this->isDynamicProperty($name)) {
            // Use cache to avoid repeated database calls within the same request
            if (! array_key_exists($name, $this->dynamicPropertyCache)) {
                $defaultValue = $this->dynamicSettings[$name];
                $this->dynamicPropertyCache[$name] = $this->getFromRepository(
                    $this->getSettingsGroup(),
                    $name,
                    $defaultValue
                );
            }

            return $this->dynamicPropertyCache[$name];
        }

        // Fall back to parent behavior for static properties
        return parent::__get($name);
    }

    /**
     * Handle dynamic property setting
     */
    public function __set($name, $value)
    {
        if ($this->isDynamicProperty($name)) {
            // Update cache
            $this->dynamicPropertyCache[$name] = $value;

            // Immediately save to repository
            $this->setInRepository($this->getSettingsGroup(), $name, $value);

            return;
        }

        // Fall back to parent behavior for static properties
        parent::__set($name, $value);
    }

    /**
     * Check if property exists (both static and dynamic)
     */
    public function __isset($name): bool
    {
        if ($this->isDynamicProperty($name)) {
            return true;
        }

        return parent::__isset($name);
    }

    /**
     * Override save method to handle any cached dynamic properties
     */
    public function save(): self
    {
        // Save static properties first
        $result = parent::save();

        // Ensure any cached dynamic properties are saved
        // (though they should already be saved via __set)
        $this->saveDynamicCache();

        return $result;
    }

    /**
     * Save any cached dynamic properties
     */
    protected function saveDynamicCache(): void
    {
        if (empty($this->dynamicPropertyCache)) {
            return;
        }

        $group = $this->getSettingsGroup();

        foreach ($this->dynamicPropertyCache as $key => $value) {
            if ($this->isDynamicProperty($key)) {
                $this->setInRepository($group, $key, $value);
            }
        }
    }

    /**
     * Get all dynamic property keys
     */
    public function getDynamicPropertyKeys(): array
    {
        $this->loadDynamicSettings();

        return array_keys($this->dynamicSettings);
    }

    /**
     * Get all dynamic properties with their current values
     */
    public function getDynamicProperties(): array
    {
        $this->loadDynamicSettings();
        $result = [];

        foreach ($this->dynamicSettings as $key => $defaultValue) {
            $result[$key] = $this->$key; // This will trigger __get
        }

        return $result;
    }

    /**
     * Reset dynamic settings cache (useful for testing)
     */
    public function resetDynamicCache(): void
    {
        $this->dynamicSettingsLoaded = false;
        $this->dynamicSettings = [];
        $this->dynamicPropertyCache = [];
    }
}
