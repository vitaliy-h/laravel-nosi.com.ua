<?php

namespace App\Modules\Shop\Queries;

use App\Modules\Shop\Models\Category;
use App\Modules\Shop\Models\Product;

class GetAllCategoriesQuery
{
    /**
     * Get all root categories with their recursive children.
     * Also marks the active category and opened parent categories.
     */
    public function handle(?int $activeCategoryId = null)
    {
        $ancestorIds = $activeCategoryId
            ? Category::ancestorsOf($activeCategoryId)->pluck('id')->toArray()
            : [];

        $productCounts = Product::visible()
            ->selectRaw('category_id, COUNT(*) as total')
            ->groupBy('category_id')
            ->pluck('total', 'category_id');

        $categories = Category::visible()->defaultOrder()->get();

        $categories->each(function (Category $category) use ($activeCategoryId, $ancestorIds, $productCounts, $categories) {
            $category->_active = $category->id === $activeCategoryId ? 1 : 0;
            $category->_opened = in_array($category->id, $ancestorIds) ? 1 : 0;
            $category->products_count = $categories
                ->filter(fn (Category $c) => $c->_lft >= $category->_lft && $c->_lft <= $category->_rgt)
                ->sum(fn (Category $c) => $productCounts->get($c->id, 0));
        });

        return $categories->toTree();
    }
}
