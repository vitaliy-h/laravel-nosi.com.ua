<?php

namespace App\Filament\Dashboard\Queries\Review;

use App\Filament\Dashboard\Dtos\DashboardPeriodData;
use App\Filament\Dashboard\Dtos\Review\ReviewStatsData;
use App\Modules\Review\Models\Review;

class GetReviewStatsQuery
{
    /**
     * Shop review figures. Only "new reviews" follows the period filter;
     * the rating and the moderation queue are a snapshot of right now.
     */
    public function handle(DashboardPeriodData $period): ReviewStatsData
    {
        return new ReviewStatsData(
            averageRating: round((float) Review::query()->visible()->avg('rating'), 2),
            newReviews: Review::query()
                ->whereBetween('created_at', [$period->from, $period->to])
                ->count(),
            pendingModeration: Review::query()->where('display', false)->count(),
            breakdown: $this->breakdown(),
        );
    }

    /**
     * Visible reviews per star, always keyed 5 down to 1 so the chart inside
     * the stat card can be rendered without extra guards.
     *
     * @return array<int, int>
     */
    private function breakdown(): array
    {
        $counts = Review::query()
            ->visible()
            ->selectRaw('rating, COUNT(*) AS total')
            ->groupBy('rating')
            ->pluck('total', 'rating');

        $breakdown = [];

        for ($stars = 5; $stars >= 1; $stars--) {
            $breakdown[$stars] = (int) ($counts[$stars] ?? 0);
        }

        return $breakdown;
    }
}
