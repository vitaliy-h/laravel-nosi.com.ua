<?php

namespace App\Modules\Shop\Queries\Product;

use Illuminate\Support\Collection;

/**
 * Products whose second price differs from the regular one — the home page
 * sale carousel. Same rule as ProductViewModel::$has_discount.
 */
readonly class GetDiscountedProductsQuery
{
    public function __construct(
        private BuildProductsQuery $buildProductsQuery,
    ) {}

    public function handle(int $limit = 12): Collection
    {
        return $this->buildProductsQuery->handle()
            ->whereNotNull('price2')
            ->where('price2', '>', 0)
            ->whereColumn('price2', '!=', 'price')
            ->orderByDesc('rank')
            ->limit($limit)
            ->get();
    }
}
