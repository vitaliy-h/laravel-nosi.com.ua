<?php

namespace App\Modules\Shop\Queries\Category;

use App\Modules\Core\Dtos\BreadcrumbData;
use App\Modules\Shop\Models\Category;
use Illuminate\Support\Collection;

class GetCategoryBreadcrumbsQuery
{
    /**
     * Build breadcrumbs for the current category.
     */
    public function handle(Category $category): Collection
    {
        $categoryBreadcrumbs = Category::visible()
            ->where('_lft', '<=', $category->_lft)
            ->where('_rgt', '>=', $category->_rgt)
            ->orderBy('_lft')
            ->get()
            ->map(fn (Category $category) => new BreadcrumbData(
                title: $category->title,
                url: module_url('shop', [$category->slug]),
            ));

        return collect(view()->shared('breadcrumbs'))
            ->merge($categoryBreadcrumbs)
            ->values();
    }
}
