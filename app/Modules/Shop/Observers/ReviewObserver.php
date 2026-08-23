<?php

namespace App\Modules\Shop\Observers;

use App\Modules\Shop\Actions\RecalculateProductRatingAction;
use App\Modules\Shop\Models\Product;
use App\Modules\Shop\Models\Review;

readonly class ReviewObserver
{
    public function __construct(
        private RecalculateProductRatingAction $recalculateRating,
    ) {}

    public function created(Review $review): void
    {
        $this->refreshProductOf($review);
    }

    /**
     * Covers moderation: toggling `display` in the admin panel publishes the
     * review and the product aggregates follow.
     */
    public function updated(Review $review): void
    {
        $this->refreshProductOf($review);

        // Review moved to another product — the previous one needs a refresh too.
        $originalProductId = $review->getOriginal('product_id');

        if ($originalProductId && (int) $originalProductId !== (int) $review->product_id) {
            $original = Product::find($originalProductId);

            if ($original) {
                $this->recalculateRating->handle($original);
            }
        }
    }

    public function deleted(Review $review): void
    {
        $this->refreshProductOf($review);
    }

    private function refreshProductOf(Review $review): void
    {
        $product = $review->product;

        if ($product) {
            $this->recalculateRating->handle($product);
        }
    }
}
