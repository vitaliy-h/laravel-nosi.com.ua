<?php

namespace App\Modules\Shop\Queries\ProductReview;

use App\Modules\Core\Dtos\DashboardPeriodData;
use App\Modules\Shop\Dtos\ProductReviewStatsData;
use App\Modules\Shop\Models\ProductReview;

class GetProductReviewStatsQuery
{
    /**
     * Shop-wide review figures. Only "new reviews" follows the period filter;
     * the rating and the moderation queue are a snapshot of right now.
     */
    public function handle(DashboardPeriodData $period): ProductReviewStatsData
    {
        return new ProductReviewStatsData(
            averageRating: round((float) ProductReview::query()->visible()->avg('rating'), 2),
            newReviews: ProductReview::query()
                ->whereBetween('created_at', [$period->from, $period->to])
                ->count(),
            pendingModeration: ProductReview::query()->where('display', false)->count(),
            breakdown: $this->breakdown(),
        );
    }

    /**
     * Visible reviews per star, always keyed 5 down to 1 so the chart inside
     * the stat card can be rendered without extra guards — same shape as
     * GetProductRatingBreakdownQuery, but for the whole shop.
     *
     * @return array<int, int>
     */
    private function breakdown(): array
    {
        $counts = ProductReview::query()
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
