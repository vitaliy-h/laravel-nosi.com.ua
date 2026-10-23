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
        return $this->query(['brand', 'category'])
            ->orderByDesc('rank')
            ->limit($limit)
            ->get();
    }

    /**
     * Whether there is anything on sale — the header «Розпродаж» item links to the
     * home page sale block, which is not rendered without products.
     */
    public function exists(): bool
    {
        return $this->query([])->exists();
    }

    private function query(array $with)
    {
        return $this->buildProductsQuery->handle(with: $with)
            ->whereNotNull('price2')
            ->where('price2', '>', 0)
            ->whereColumn('price2', '!=', 'price');
    }
}
