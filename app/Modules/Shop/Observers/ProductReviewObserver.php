<?php

namespace App\Modules\Shop\Observers;

use App\Modules\Shop\Actions\RecalculateProductRatingAction;
use App\Modules\Shop\Models\Product;
use App\Modules\Shop\Models\ProductReview;

readonly class ProductReviewObserver
{
    public function __construct(
        private RecalculateProductRatingAction $recalculateRating,
    ) {}

    public function created(ProductReview $review): void
    {
        $this->refreshProduct($review->product_id);
    }

    /**
     * Covers moderation: toggling `display` in the admin panel publishes the
     * review and the product aggregates follow.
     */
    public function updated(ProductReview $review): void
    {
        $this->refreshProduct($review->product_id);

        // Review moved to another product — the previous one needs a refresh too.
        $originalProductId = $review->getOriginal('product_id');

        if ($originalProductId && (int) $originalProductId !== (int) $review->product_id) {
            $this->refreshProduct((int) $originalProductId);
        }
    }

    public function deleted(ProductReview $review): void
    {
        $this->refreshProduct($review->product_id);
    }

    /**
     * Resolved by id rather than through `$review->product`: once the relation
     * has been loaded, Eloquent keeps serving the product the review pointed at
     * *before* `product_id` changed, so the new product would never be refreshed.
     */
    private function refreshProduct(?int $productId): void
    {
        if (! $productId) {
            return;
        }

        $product = Product::find($productId);

        if ($product) {
            $this->recalculateRating->handle($product);
        }
    }
}
