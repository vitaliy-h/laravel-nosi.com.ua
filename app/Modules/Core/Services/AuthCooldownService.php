<?php

namespace App\Modules\Core\Services;

use App\Modules\Core\Exceptions\AuthCooldownException;
use Illuminate\Support\Facades\RateLimiter;

/**
 * Short cooldown after a failed sign-in: the next attempt for the same
 * login + IP pair is rejected for `auth_log.cooldown_seconds` seconds.
 *
 * The key includes the IP so a stranger cannot lock somebody else's account
 * out, and works for logins that do not exist as users at all.
 */
class AuthCooldownService
{
    public function assertNotBlocked(string $login): void
    {
        if (!$this->enabled()) {
            return;
        }

        $key = $this->key($login);

        if (!RateLimiter::tooManyAttempts($key, 1)) {
            return;
        }

        throw new AuthCooldownException(
            max(RateLimiter::availableIn($key), 1)
        );
    }

    public function hit(string $login): void
    {
        if (!$this->enabled()) {
            return;
        }

        RateLimiter::hit($this->key($login), $this->seconds());
    }

    public function clear(string $login): void
    {
        RateLimiter::clear($this->key($login));
    }

    public function key(string $login, ?string $ip = null): string
    {
        $ip ??= request()?->ip();

        return 'auth-cooldown:' . sha1(mb_strtolower(trim($login)) . '|' . $ip);
    }

    private function seconds(): int
    {
        return (int) config('auth_log.cooldown_seconds', 5);
    }

    private function enabled(): bool
    {
        return $this->seconds() > 0;
    }
}
