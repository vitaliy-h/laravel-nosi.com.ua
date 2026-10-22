<?php

namespace App\Modules\Shop\Queries\Product;

use Illuminate\Support\Collection;

/**
 * The newest products by date — the home page «Новинки» tab.
 */
readonly class GetNewProductsQuery
{
    public function __construct(
        private BuildProductsQuery $buildProductsQuery,
    ) {}

    public function handle(int $limit = 10): Collection
    {
        return $this->buildProductsQuery->handle()
            ->orderByDesc('created_at')
            ->orderByDesc('id')
            ->limit($limit)
            ->get();
    }
}
