<?php

namespace App\Modules\Shop\Queries\Product;

use App\Modules\Shop\Dtos\ProductSearchDto;
use App\Modules\Shop\Enums\PerPageOptionEnum;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

class SearchProductsQuery
{
    public function __construct(
        private readonly BuildProductsQuery $buildProductsQuery,
    ) {}

    /**
     * Search products by category/filters (paginated shop listing), or
     * pull a random limited set (best sellers, featured products widgets).
     */
    public function handle(ProductSearchDto $criteria): Collection|LengthAwarePaginator
    {
        $query = $this->buildProductsQuery->handle($criteria->category, $criteria->filter, with: $criteria->with);

        if ($criteria->random) {
            $query->inRandomOrder();
        } else {
            $this->applySorting($query, $criteria->sort);
        }

        if ($criteria->paginate) {
            return $query->paginate($this->resolvePerPage())->withQueryString();
        }

        if ($criteria->limit) {
            $query->limit($criteria->limit);
        }

        return $query->get();
    }

    /**
     * Apply selected sorting to the products query.
     * Falls back to default shop sorting when no valid sort option is provided.
     */
    private function applySorting(Builder $query, ?string $sort): void
    {
        match ($sort) {
            'price_asc' => $query->orderBy('price', 'asc'),
            'price_desc' => $query->orderBy('price', 'desc'),
            'newest' => $query->orderBy('id', 'desc'),
            'title_asc' => $query->orderBy('title', 'asc'),
            'title_desc' => $query->orderBy('title', 'desc'),
            default => $query->orderBy('id', 'desc'),
        };
    }

    /**
     * Number of products displayed per page.
     */
    private function resolvePerPage(): int
    {
        $count = (int) request('c', PerPageOptionEnum::default()->value);

        return in_array($count, PerPageOptionEnum::values(), true)
            ? $count
            : PerPageOptionEnum::default()->value;
    }
}
