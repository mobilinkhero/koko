<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class ValidSubdomain implements ValidationRule
{
    /**
     * Run the validation rule.
     *
     * @param  \Closure(string): PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        // Get restricted subdomains from config
        $restricted = config('restrictions.subdomains', []);

        // Check if subdomain is in the restricted list
        if (in_array(strtolower($value), $restricted)) {
            $fail('The selected name cannot be used. Please choose another.');
        }
    }
}
