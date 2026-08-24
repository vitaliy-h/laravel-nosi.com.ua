<?php

namespace App\Modules\Shop\Queries\ProductReview;

use App\Modules\Shop\Models\Product;
use App\Modules\Shop\Models\ProductReview;

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
        $counts = ProductReview::query()
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
