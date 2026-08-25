<?php

namespace App\Filament\Dashboard\Dtos\System;

use Spatie\LaravelData\Data;

class SystemStatsData extends Data
{
    public function __construct(
        public readonly string $environment,
        public readonly bool $debug,
        public readonly string $phpVersion,
        public readonly string $laravelVersion,
        public readonly string $filamentVersion,
        public readonly int $mailSent,
        public readonly int $mailFailed,
        // null when the sms module is off — the card is dropped instead of
        // reporting delivery health for a channel that is not in use.
        public readonly ?int $smsSent,
        public readonly ?int $smsFailed,
    ) {}

    /**
     * Debug mode outside local is a misconfiguration worth shouting about.
     */
    public function isDebugRisky(): bool
    {
        return $this->debug && $this->environment !== 'local';
    }
}
