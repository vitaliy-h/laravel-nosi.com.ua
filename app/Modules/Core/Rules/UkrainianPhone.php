<?php

namespace App\Modules\Core\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

/**
 * A Ukrainian phone number: +380 and nine digits.
 *
 * Run normalize() on the input first (in prepareForValidation) so the customer
 * may type it as they like — 067 123 45 67, 380671234567, +38 (067) 123-45-67.
 */
class UkrainianPhone implements ValidationRule
{
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (!is_string($value) || !preg_match('/^\+380\d{9}$/', $value)) {
            $fail('validation.ua_phone')->translate();
        }
    }

    /**
     * Bring a typed number to +380XXXXXXXXX. Anything that does not look like a
     * Ukrainian number is returned as typed, so the rule rejects it.
     */
    public static function normalize(?string $phone): ?string
    {
        if ($phone === null || trim($phone) === '') {
            return $phone;
        }

        $digits = preg_replace('/\D/', '', $phone);

        return match (true) {
            strlen($digits) === 12 && str_starts_with($digits, '380') => '+' . $digits,
            strlen($digits) === 11 && str_starts_with($digits, '80')  => '+3' . $digits,
            strlen($digits) === 10 && str_starts_with($digits, '0')   => '+38' . $digits,
            default                                                   => $phone,
        };
    }
}
