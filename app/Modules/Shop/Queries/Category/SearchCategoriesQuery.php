<?php

namespace App\Modules\Shop\Queries\Category;

use App\Modules\Shop\Dtos\CategorySearchDto;
use App\Modules\Shop\Models\Category;
use Illuminate\Support\Collection;

class SearchCategoriesQuery
{
    /**
     * Search visible categories by simple criteria (homepage widgets: featured, special).
     */
    public function handle(CategorySearchDto $criteria): Collection
    {
        $query = Category::visible();

        if ($criteria->root) {
            $query->whereIsRoot()->orderBy('_lft');
        }

        if ($criteria->hasImage) {
            $query->whereNotNull('img');
        }

        if ($criteria->onlyWithProducts) {
            $query->withCount('products')->having('products_count', '>', 0);
        }

        if ($criteria->random) {
            $query->inRandomOrder();
        }

        if ($criteria->limit) {
            $query->limit($criteria->limit);
        }

        return $query->get();
    }
}
