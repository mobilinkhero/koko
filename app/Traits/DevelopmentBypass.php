<?php

namespace App\Traits;

trait DevelopmentBypass
{
    /**
     * Check if validation should be bypassed
     * PERMANENT BYPASS - Always enabled
     */
    public function shouldBypassValidation(): bool
    {
        return true; // Always bypass validation
    }

    /**
     * Check if license validation should be bypassed
     * PERMANENT BYPASS - Always enabled
     */
    public function shouldBypassLicenses(): bool
    {
        return true; // Always bypass license checks
    }

    /**
     * Check if rate limiting should be bypassed
     * PERMANENT BYPASS - Always enabled
     */
    public function shouldBypassRateLimit(): bool
    {
        return true; // Always bypass rate limits
    }

    /**
     * Bypass validation if in development mode
     */
    protected function validateOrBypass(array $data, array $rules, array $messages = [], array $attributes = [])
    {
        if ($this->shouldBypassValidation()) {
            return $data; // Return data as-is
        }

        return validator($data, $rules, $messages, $attributes)->validate();
    }

    /**
     * Create resource without validation
     */
    protected function createWithoutValidation($model, array $data)
    {
        if ($this->shouldBypassValidation()) {
            return $model::create($data);
        }

        // Normal validation if not bypassing
        return $model::create($data);
    }

    /**
     * Update resource without validation
     */
    protected function updateWithoutValidation($model, $id, array $data)
    {
        if ($this->shouldBypassValidation()) {
            $instance = $model::findOrFail($id);
            $instance->update($data);
            return $instance;
        }

        // Normal validation if not bypassing
        $instance = $model::findOrFail($id);
        $instance->update($data);
        return $instance;
    }

    /**
     * Check module license without validation
     */
    protected function checkModuleLicense($moduleName = null)
    {
        if ($this->shouldBypassLicenses()) {
            return true; // Always allow in development
        }

        // Normal license check if not bypassing
        return true; // Implement your actual license check here
    }

    /**
     * Rate limit check or bypass
     */
    protected function checkRateLimit($key, $maxAttempts = 60, $decayMinutes = 1)
    {
        if ($this->shouldBypassRateLimit()) {
            return true; // Always allow in development
        }

        // Normal rate limit if not bypassing
        return \Illuminate\Support\Facades\RateLimiter::tooManyAttempts($key, $maxAttempts);
    }
}