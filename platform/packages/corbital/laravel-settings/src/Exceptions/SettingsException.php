<?php

namespace Corbital\Settings\Exceptions;

use Exception;

class SettingsException extends Exception
{
    /**
     * Creates an exception for when a setting class is not found.
     */
    public static function classNotFound(string $group): self
    {
        return new self("Settings class for group '{$group}' not found.");
    }

    /**
     * Creates an exception for when a setting property is not found.
     */
    public static function propertyNotFound(string $group, string $property): self
    {
        return new self("Setting property '{$property}' not found in group '{$group}'.");
    }

    /**
     * Creates an exception for when the settings key format is invalid.
     */
    public static function invalidKeyFormat(string $key): self
    {
        return new self("Invalid settings key format: '{$key}'. Expected format: 'group.key'.");
    }
}
