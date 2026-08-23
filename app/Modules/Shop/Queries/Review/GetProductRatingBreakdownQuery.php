<?php

namespace App\Modules\Shop\Queries\Review;

use App\Modules\Shop\Models\Product;
use App\Modules\Shop\Models\Review;

class GetProductRatingBreakdownQuery
{
    /**
     * Number of visible reviews per star, always keyed 5 down to 1 so the
     * progress bars can be rendered without extra guards.
     *
     * @return array<int, int>
     */
    public function handle(Product $product): array
    {
        $counts = Review::query()
            ->visible()
            ->where('product_id', $product->id)
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
