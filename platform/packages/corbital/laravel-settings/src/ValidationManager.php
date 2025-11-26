<?php

namespace Corbital\Settings;

use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class ValidationManager
{
    /**
     * Validate a setting value against the validation rules.
     *
     * @param  string  $group  The settings group
     * @param  string  $key  The setting key
     * @param  mixed  $value  The setting value
     *
     * @throws ValidationException
     */
    public function validate(string $group, string $key, mixed $value): bool
    {
        $rules = $this->getValidationRules($group, $key);

        if (empty($rules)) {
            return true;
        }

        $validator = Validator::make([$key => $value], [$key => $rules]);

        if ($validator->fails()) {
            throw ValidationException::withMessages([
                $key => $validator->errors()->get($key),
            ]);
        }

        return true;
    }

    /**
     * Get validation rules for a specific setting.
     *
     * @param  string  $group  The settings group
     * @param  string  $key  The setting key
     */
    protected function getValidationRules(string $group, string $key): array|string
    {
        $settingsClasses = app('settings')->getSettingsClasses();

        if (! isset($settingsClasses[$group])) {
            return [];
        }

        $class = $settingsClasses[$group];

        // Check if the class has validation rules
        if (! method_exists($class, 'validationRules')) {
            return [];
        }

        $rules = app($class)::validationRules();

        return $rules[$key] ?? [];
    }

    /**
     * Validate multiple settings at once.
     *
     * @param  string  $group  The settings group
     * @param  array  $settings  Array of key/value pairs
     *
     * @throws ValidationException
     */
    public function validateBatch(string $group, array $settings): bool
    {
        $settingsClasses = app('settings')->getSettingsClasses();

        if (! isset($settingsClasses[$group])) {
            return true;
        }

        $class = $settingsClasses[$group];

        // Check if the class has validation rules
        if (! method_exists($class, 'validationRules')) {
            return true;
        }

        $rules = app($class)::validationRules();

        // Only validate the settings that have rules
        $dataToValidate = [];
        $rulesToApply = [];

        foreach ($settings as $key => $value) {
            if (isset($rules[$key])) {
                $dataToValidate[$key] = $value;
                $rulesToApply[$key] = $rules[$key];
            }
        }

        if (empty($rulesToApply)) {
            return true;
        }

        $validator = Validator::make($dataToValidate, $rulesToApply);

        if ($validator->fails()) {
            throw ValidationException::withMessages($validator->errors()->toArray());
        }

        return true;
    }
}
