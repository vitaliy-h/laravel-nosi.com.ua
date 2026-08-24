<?php

namespace App\Modules\Shop\Actions;

use App\Modules\Shop\Models\Product;
use App\Modules\Shop\Models\ProductReview;

class RecalculateProductRatingAction
{
    /**
     * Refresh the denormalized `rating` / `reviews_count` columns of a product
     * from its visible reviews.
     *
     * Written through the base query builder on purpose: the aggregates are
     * derived data, so they must not bump the product's `updated_at` (sitemap)
     * nor fire model events.
     */
    public function handle(Product $product): void
    {
        $aggregate = ProductReview::query()
            ->visible()
            ->where('product_id', $product->id)
            ->selectRaw('AVG(rating) AS average, COUNT(*) AS total')
            ->first();

        Product::whereKey($product->id)->toBase()->update([
            'rating' => round((float) $aggregate->average, 2),
            'reviews_count' => (int) $aggregate->total,
        ]);
    }
}
