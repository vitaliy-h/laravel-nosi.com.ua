<?php

namespace App\Modules\Shop\Queries\Product;

use App\Modules\Shop\Dtos\ProductSearchDto;
use App\Modules\Shop\Enums\PerPageOptionEnum;
use App\Modules\Shop\Enums\SortOptionEnum;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

readonly class SearchProductsQuery
{
    public function __construct(
        private BuildProductsQuery $buildProductsQuery,
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
     * Falls back to default shop sorting when no sort option is provided.
     */
    private function applySorting(Builder $query, ?SortOptionEnum $sort): void
    {
        [$column, $direction] = ($sort ?? SortOptionEnum::NEWEST)->orderBy();

        $query->orderBy($column, $direction);
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
