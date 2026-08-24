<?php

namespace App\Filament\Dashboard\Dtos\Shop;

use Spatie\LaravelData\Data;

class ProductReviewStatsData extends Data
{
    /**
     * @param  array<int, int>  $breakdown  visible reviews per star, keyed 5 → 1
     */
    public function __construct(
        public readonly float $averageRating,
        public readonly int $newReviews,
        public readonly int $pendingModeration,
        public readonly array $breakdown,
    ) {}

    /**
     * Bar heights for Stat::chart(), 5 stars first.
     *
     * @return array<int, float>
     */
    public function chart(): array
    {
        return array_map(static fn (int $count): float => (float) $count, array_values($this->breakdown));
    }
}
