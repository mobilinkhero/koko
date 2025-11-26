<?php

namespace Corbital\Settings\Traits;

use Illuminate\Support\Facades\Crypt;

trait EncryptsSettings
{
    /**
     * Settings that should be encrypted.
     */
    protected array $encrypted = [];

    /**
     * Boot the trait.
     */
    public static function bootEncryptsSettings()
    {
        static::saving(function ($model) {
            $model->encryptSettings();
        });

        static::retrieved(function ($model) {
            $model->decryptSettings();
        });
    }

    /**
     * Encrypt settings that should be encrypted.
     */
    protected function encryptSettings(): void
    {
        foreach ($this->encrypted as $key) {
            if (property_exists($this, $key) && $this->$key !== null && ! $this->isEncrypted($this->$key)) {
                $this->$key = $this->encrypt($this->$key);
            }
        }
    }

    /**
     * Decrypt settings that are encrypted.
     */
    protected function decryptSettings(): void
    {
        foreach ($this->encrypted as $key) {
            if (property_exists($this, $key) && $this->$key !== null && $this->isEncrypted($this->$key)) {
                $this->$key = $this->decrypt($this->$key);
            }
        }
    }

    /**
     * Encrypt a value.
     */
    protected function encrypt(mixed $value): string
    {
        // For arrays and objects, serialize first
        if (is_array($value) || is_object($value)) {
            $value = serialize($value);
        }

        return 'encrypted:'.Crypt::encrypt($value);
    }

    /**
     * Decrypt a value.
     */
    protected function decrypt(string $value): mixed
    {
        $decrypted = Crypt::decrypt(substr($value, 10));

        // If the decrypted value is a serialized string, unserialize it
        if (is_string($decrypted) && $this->isSerialized($decrypted)) {
            return unserialize($decrypted);
        }

        return $decrypted;
    }

    /**
     * Determine if a value is encrypted.
     */
    protected function isEncrypted(mixed $value): bool
    {
        return is_string($value) && str_starts_with($value, 'encrypted:');
    }

    /**
     * Determine if a string is serialized.
     */
    protected function isSerialized(string $value): bool
    {
        return $value === 'b:0;' || (@unserialize($value) !== false);
    }
}
