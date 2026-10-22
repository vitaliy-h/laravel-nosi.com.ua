<?php

namespace App\Modules\Shop\Queries\Product;

use Illuminate\Support\Collection;

/**
 * Products marked «Хіт» — the home page «Топ продажів» tab.
 */
readonly class GetHitProductsQuery
{
    public function __construct(
        private BuildProductsQuery $buildProductsQuery,
    ) {}

    public function handle(int $limit = 12): Collection
    {
        return $this->buildProductsQuery->handle()
            ->where('is_hit', true)
            ->orderByDesc('rank')
            ->orderByDesc('reviews_count')
            ->limit($limit)
            ->get();
    }
}
