<?php

namespace App\Modules\Shop\Queries\Category;

use App\Modules\Shop\Models\Category;
use App\Modules\Shop\Models\Product;
use Illuminate\Support\Collection;

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

        $subtreeCounts = $this->subtreeProductCounts($categories, $productCounts);

        $categories->each(function (Category $category) use ($activeCategoryId, $ancestorIds, $subtreeCounts) {
            $category->_active = $category->id === $activeCategoryId ? 1 : 0;
            $category->_opened = in_array($category->id, $ancestorIds) ? 1 : 0;
            $category->products_count = $subtreeCounts[$category->id] ?? 0;
        });

        return $categories->toTree();
    }

    /**
     * Sum each category's own product count plus its descendants', in a single pass
     * by walking leaves-to-root (ascending _rgt) and bubbling totals up via parent_id.
     * Assumes a visible category's ancestors are also visible (no hidden middle nodes).
     *
     * @return array<int, int>
     */
    private function subtreeProductCounts(Collection $categories, Collection $productCounts): array
    {
        $totals = [];

        $categories->sortBy('_rgt')->each(function (Category $category) use (&$totals, $productCounts) {
            $total = ($totals[$category->id] ?? 0) + $productCounts->get($category->id, 0);
            $totals[$category->id] = $total;

            if ($category->parent_id !== null) {
                $totals[$category->parent_id] = ($totals[$category->parent_id] ?? 0) + $total;
            }
        });

        return $totals;
    }
}
