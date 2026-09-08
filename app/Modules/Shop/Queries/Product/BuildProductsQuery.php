<?php

namespace App\Modules\Shop\Queries\Product;

use App\Modules\Shop\Dtos\Filters\BaseFilterData;
use App\Modules\Shop\Dtos\Filters\ShopFilterData;
use App\Modules\Shop\Models\Category;
use App\Modules\Shop\Models\Product;

class BuildProductsQuery
{
    /**
     * Build the base product query.
     * Applies category restriction and all selected filters except the excluded one.
     */
    public function handle(?Category $category = null, ?ShopFilterData $filter = null, ?string $except = null, array $with = ['brand', 'category'])
    {
        $query = Product::visible()
            ->with($with);
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
                ->reject(fn (BaseFilterData $item) => $item->key === $except)
                ->each(fn (BaseFilterData $item) => $item->apply($query));
        }

        return $query;
    }
}
