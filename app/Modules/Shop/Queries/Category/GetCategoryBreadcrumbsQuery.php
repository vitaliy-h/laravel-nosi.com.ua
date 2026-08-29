<?php

namespace App\Modules\Shop\Queries\Category;

use App\Modules\Core\ViewModels\BreadcrumbViewModel;
use App\Modules\Shop\Models\Category;
use Illuminate\Support\Collection;

class GetCategoryBreadcrumbsQuery
{
    /**
     * Build breadcrumbs for the current category.
     *
     * @return Collection<int, BreadcrumbViewModel>
     */
    public function handle(Category $category): Collection
    {
        $categoryBreadcrumbs = Category::visible()
            ->where('_lft', '<=', $category->_lft)
            ->where('_rgt', '>=', $category->_rgt)
            ->orderBy('_lft')
            ->get()
            ->map(fn (Category $category) => new BreadcrumbViewModel(
                title: $category->title,
                url: save_route('shop.category', $category->slug),
            ));

        return collect(view()->shared('breadcrumbs'))
            ->merge($categoryBreadcrumbs)
            ->values();
    }
}
