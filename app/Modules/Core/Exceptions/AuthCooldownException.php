<?php

namespace App\Modules\Core\Exceptions;

use Exception;
use Illuminate\Validation\ValidationException;

/**
 * Thrown when a sign-in attempt arrives while the login is still on cooldown
 * after a failed attempt.
 */
class AuthCooldownException extends Exception
{
    public function __construct(public readonly int $seconds)
    {
        parent::__construct(t('auth.cooldown', ['seconds' => $seconds]));
    }

    /**
     * Same rejection expressed as a validation error on the given form field,
     * so web, API and the admin panel all answer in their usual format.
     */
    public function asValidationException(string $field, ?string $message = null): ValidationException
    {
        return ValidationException::withMessages([
            $field => [$message ?? $this->getMessage()],
        ]);
    }
}
