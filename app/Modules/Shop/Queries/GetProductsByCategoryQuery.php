<?php

namespace App\Modules\Shop\Queries;

use App\Modules\Shop\Dtos\Filters\ShopFilterDto;
use App\Modules\Shop\Enums\PerPageOptionEnum;
use App\Modules\Shop\Models\Category;

class GetProductsByCategoryQuery
{
    /**
     * Number of products displayed per page.
     */
    private int $paginate;

    public function __construct(
        private readonly BuildProductsQuery $buildProductsQuery,
    ) {
        $count = (int) request('c', PerPageOptionEnum::default()->value);

        $this->paginate = in_array($count, PerPageOptionEnum::values(), true)
            ? $count
            : PerPageOptionEnum::default()->value;
    }

    /**
     * Get paginated products for the current category and selected filters.
     */
    public function handle(?Category $category = null, ?ShopFilterDto $filter = null)
    {
        $query = $this->buildProductsQuery->handle($category, $filter);

        $this->applySorting($query, request('sort'));

        return $query
            ->paginate($this->paginate)
            ->withQueryString();
    }

    /**
     * Apply selected sorting to the products query.
     * Falls back to default shop sorting when no valid sort option is provided.
     */
    private function applySorting($query, ?string $sort): void
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
}
