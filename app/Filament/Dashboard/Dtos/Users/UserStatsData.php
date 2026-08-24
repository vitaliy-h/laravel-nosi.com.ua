<?php

namespace App\Filament\Dashboard\Dtos\Users;

use Spatie\LaravelData\Data;

class UserStatsData extends Data
{
    /**
     * @param  array<int, float>  $registrationsPerDay  sparkline for the New users card
     */
    public function __construct(
        public readonly int $usersTotal,
        public readonly int $newUsers,
        public readonly int $previousNewUsers,
        public readonly array $registrationsPerDay,
        /** Null when the subscribers module is disabled. */
        public readonly ?int $subscribersTotal,
        public readonly ?int $newSubscribers,
        public readonly ?int $failedSubscribers,
    ) {}
}
