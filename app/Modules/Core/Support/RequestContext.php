<?php

namespace App\Modules\Core\Support;

/**
 * Reads the current request on behalf of the journals.
 *
 * Domain classes take request-scoped values as parameters (see
 * `.claude/backend.md`, «Request-scoped state»). The journals are the exception:
 * they are written from observers and services that run inside a request just as
 * often as from a queue or an artisan command, and threading an IP address
 * through every one of those call sites would say nothing about the domain. They
 * enrich themselves through this class instead, which degrades to `null` outside
 * a request rather than failing.
 *
 * It is one of the few classes allowed to touch `request()` — everything that is
 * not a journal takes the value as an argument.
 */
class RequestContext
{
    public static function ip(): ?string
    {
        return request()?->ip();
    }

    public static function userAgent(): ?string
    {
        return request()?->userAgent();
    }

    public static function userId(): ?int
    {
        return request()?->user()?->id;
    }
}
