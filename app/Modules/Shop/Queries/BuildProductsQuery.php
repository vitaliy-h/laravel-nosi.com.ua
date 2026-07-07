<?php

namespace App\Modules\Shop\Queries;

use App\Modules\Shop\Dtos\Filters\BaseFilterDto;
use App\Modules\Shop\Dtos\Filters\ShopFilterDto;
use App\Modules\Shop\Models\Category;
use App\Modules\Shop\Models\Product;

class BuildProductsQuery
{
    /**
     * Build the base product query.
     * Applies category restriction and all selected filters except the excluded one.
     */
    public function handle(?Category $category = null, ?ShopFilterDto $filter = null, ?string $except = null)
    {
        $query = Product::visible()
            ->with('brand', 'category');
        /**
         * Restrict products to the current category and all its descendants.
         */
        if ($category) {
            $categoryIds = Category::whereDescendantOrSelf($category)
                ->pluck('id');

            $query->whereIn('category_id', $categoryIds);
        }
        /**
         * Apply all active filters except the one currently being recalculated.
         */
        if ($filter) {
            $filter->toApplicable()
                ->reject(fn (BaseFilterDto $item) => $item->key === $except)
                ->each(fn (BaseFilterDto $item) => $item->apply($query));
        }

        return $query;
    }
}
