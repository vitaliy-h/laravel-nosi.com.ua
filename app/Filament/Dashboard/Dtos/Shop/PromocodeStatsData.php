<?php

namespace App\Filament\Dashboard\Dtos\Shop;

use Spatie\LaravelData\Data;

class PromocodeStatsData extends Data
{
    public function __construct(
        public readonly int $activeCount,
        public readonly int $expiredCount,
        public readonly int $usesInPeriod,
        public readonly float $discountGiven,
        /** Order total of the same period, the base for the discount share. */
        public readonly float $revenueInPeriod,
        public readonly ?string $topCode,
        public readonly int $topCodeUses,
    ) {}

    /**
     * Share of the period turnover that was given away as a discount.
     */
    public function discountShare(): float
    {
        if ($this->revenueInPeriod <= 0.0) {
            return 0.0;
        }

        return round($this->discountGiven / $this->revenueInPeriod * 100, 1);
    }
}
