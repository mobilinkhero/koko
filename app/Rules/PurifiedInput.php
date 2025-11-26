<?php

namespace App\Rules;

use Closure;
use Exception;
use HTMLPurifier;
use HTMLPurifier_Config;
use Illuminate\Contracts\Validation\ValidationRule;

class PurifiedInput implements ValidationRule
{
    protected $message;

    public function __construct($message = null)
    {
        $this->message = $message ?: t('attack_injection_risk');
    }

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        try {
            if (is_numeric($value)) {
                return;
            }

            if (is_array($value)) {
                foreach ($value as $key => $val) {
                    $this->validateArrayItem($attribute.'.'.$key, $val, $fail);
                }

                return;
            }

            if (! is_string($value)) {
                return;
            }

            // Check for SQL Injection
            if ($this->containsSqlInjection($value)) {
                $fail(str_replace(':attribute', $attribute, $this->message));

                return;
            }

            // Check for JSON Injection
            if ($this->containsJsonInjection($value)) {
                $fail(str_replace(':attribute', $attribute, $this->message));

                return;
            }

            // Sanitize input
            $cleanedValue = $this->purifyInput($value);

            if ($value !== $cleanedValue) {
                $fail(str_replace(':attribute', $attribute, $this->message));
            }
        } catch (Exception $e) {
            app_log('Validation error in PurifiedInput: '.$e->getMessage(), 'error', $e, [
                'attribute' => $attribute,
                'value' => $value,
            ]);

            $fail(t('validation_failed_due_unexpected_error'));
        }
    }

    /**
     * Recursively validate array items.
     */
    private function validateArrayItem(string $attribute, mixed $value, Closure $fail): void
    {
        if (is_array($value)) {
            foreach ($value as $key => $val) {
                $this->validateArrayItem($attribute.'.'.$key, $val, $fail);
            }

            return;
        }

        $this->validate($attribute, $value, $fail);
    }

    /**
     * Purify input using HTMLPurifier.
     */
    private function purifyInput(string $value): string
    {

        try {
            if (strip_tags($value) !== $value) {
                $value = strip_tags($value);
            }

            // Convert invalid entities to &amp; to avoid purifier errors
            $value = preg_replace('/&(?!(?:[a-zA-Z]{2,6}|#\d{2,4});)/', '&amp;', $value);

            $config = HTMLPurifier_Config::createDefault();
            $config->set('HTML.Allowed', '');             // No HTML tags allowed
            $config->set('Core.EscapeInvalidTags', true); // Optional

            $purifier = new HTMLPurifier($config);
            $clean = $purifier->purify($value);

            // Decode &amp; back to & for final output
            $clean = str_replace('&amp;', '&', $clean);

            return $clean;
        } catch (Exception $e) {
            app_log('Error in HTML purification: '.$e->getMessage(), 'error', $e, [
                'value' => $value,
            ]);

            return $value;
        }
    }

    /**
     * Check for common SQL injection patterns.
     */
    private function containsSqlInjection(string $value): bool
    {
        try {
            // Allow valid hex color codes
            if (preg_match('/^#?[0-9A-Fa-f]{3,6}$/', $value)) {
                return false;
            }

            // Allow simple words, numbers, spaces, and safe characters (&, and)
            if (preg_match('/^[a-zA-Z0-9\s,&.\'"\-()!?]+$/', $value)) {
                return false;
            }

            // Common SQL injection patterns
            $dangerousPatterns = [
                '/\b(SELECT|INSERT|UPDATE|DELETE|DROP|ALTER|TRUNCATE|REPLACE|EXEC|UNION|WHERE|HAVING|ORDER BY|GROUP BY|LIMIT|JOIN|OUTER|INNER|LEFT|RIGHT|CROSS|NATURAL)\b/i',
                '/(--|\#|\/\*|\*\/|;)/',
                '/(\bUNION\b.*\bSELECT\b)/i',
                '/(sleep\((\s*)?\d+(\s*)?\))/i',
                '/(benchmark\((\s*)?\d+,\s*(.*)\))/i',
                '/(\bor\s+1\s*=\s*1\b)/i',
                '/(@@|information_schema|pg_sleep|hex|load_file|xp_cmdshell|bypass|declare)/i',
            ];

            foreach ($dangerousPatterns as $pattern) {
                if (preg_match($pattern, $value)) {
                    return true;
                }
            }
        } catch (\Exception $e) {
            app_log('SQL Injection detection failed: '.$e->getMessage(), 'error', $e, [
                'value' => $value,
            ]);

            return true;
        }

        return false;
    }

    /**
     * Check for potential JSON injection patterns.
     */
    private function containsJsonInjection(string $value): bool
    {
        try {
            $patterns = [
                '/(?<!@)\{[^}]*?\}|\[.*?\]/s',        // Detects JSON-like structures but allows @{name}
                '/("|\')\s*:\s*("|\')(?!\s*&)/',      // Detects key-value pairs, except safe "&"
                '/<script\b[^>]*>(.*?)<\/script>/is', // Detects inline scripts
                '/\\\\"/',                            // Detects excessive escaping
                '/(\\\u[0-9a-fA-F]{4})/',             // Detects Unicode escapes
            ];

            foreach ($patterns as $pattern) {
                if (preg_match($pattern, $value)) {
                    return true;
                }
            }
        } catch (\Exception $e) {
            app_log('JSON Injection detection failed: '.$e->getMessage(), 'error', $e, [
                'value' => $value,
            ]);
        }

        return false;
    }
}
