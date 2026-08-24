<?php

namespace App\Modules\Shop\Queries\ProductReview;

use App\Modules\Shop\Models\Product;
use App\Modules\Shop\Models\ProductReview;
use Illuminate\Pagination\LengthAwarePaginator;

class GetReviewsByProductQuery
{
    /**
     * Visible reviews of a product, newest first.
     *
     * Paginated (rather than a plain collection) because the front-end
     * "Load more" button needs hasMorePages() to decide whether to render.
     */
    public function handle(Product $product, int $page = 1, int $perPage = 10): LengthAwarePaginator
    {
        return ProductReview::query()
            ->visible()
            ->where('product_id', $product->id)
            ->with('user')
            ->orderByDesc('created_at')
            ->orderByDesc('id')
            ->paginate(perPage: $perPage, page: $page);
    }
}
