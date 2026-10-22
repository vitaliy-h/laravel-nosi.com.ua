<?php

namespace App\Modules\Shop\Queries\ProductReview;

use App\Modules\Shop\Models\Product;

/**
 * The shop-wide rating summary: total reviews and their average, built from
 * the per-product aggregates ProductReviewObserver keeps (`rating`,
 * `reviews_count`) so it is one cheap query over visible products.
 */
class GetShopRatingQuery
{
    /**
     * @return array{rating: float, count: int}
     */
    public function handle(): array
    {
        $row = Product::visible()
            ->where('reviews_count', '>', 0)
            ->selectRaw('SUM(reviews_count) as total, SUM(rating * reviews_count) as weighted')
            ->first();

        $count = (int) ($row?->total ?? 0);

        return [
            'rating' => $count > 0 ? round((float) $row->weighted / $count, 1) : 0.0,
            'count'  => $count,
        ];
    }
}
