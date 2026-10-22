<?php

namespace App\Modules\Shop\Queries\ProductReview;

use App\Modules\Shop\Models\ProductReview;
use Illuminate\Support\Collection;

/**
 * Published reviews across the whole shop, newest first — the home page block.
 * The product comes along for its title, photo and URL.
 */
class GetLatestReviewsQuery
{
    public function handle(int $limit = 10): Collection
    {
        return ProductReview::visible()
            ->whereHas('product', fn ($query) => $query->visible())
            ->with(['product.category', 'user'])
            ->latest()
            ->limit($limit)
            ->get();
    }
}
