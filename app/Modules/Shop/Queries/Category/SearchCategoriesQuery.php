<?php

namespace App\Modules\Shop\Queries\Category;

use App\Modules\Shop\Dtos\CategorySearchData;
use App\Modules\Shop\Models\Category;
use Illuminate\Support\Collection;

class SearchCategoriesQuery
{
    /**
     * Search visible categories by simple criteria (homepage widgets, header and footer menus).
     */
    public function handle(CategorySearchData $criteria): Collection
    {
        $query = Category::visible();

        if ($criteria->root) {
            $query->whereIsRoot();
        }

        if ($criteria->headerMenu) {
            $query->where('display_menu', true);
        }

        if ($criteria->footerMenu) {
            $query->where('display_menu_footer', true);
        }

        if ($criteria->root || $criteria->headerMenu || $criteria->footerMenu) {
            $query->orderBy('_lft');
        }

        if ($criteria->hasImage) {
            $query->whereNotNull('img');
        }

        if ($criteria->onlyWithProducts) {
            $query->withCount('products')->having('products_count', '>', 0);
        }

        // A visible product in the category itself or any of its subcategories —
        // the category page lists the whole subtree, so it is not empty.
        if ($criteria->withProductsInTree) {
            $query->whereExists(fn ($products) => $products
                ->selectRaw('1')
                ->from('shop_products')
                ->join('shop_categories as product_category', 'product_category.id', '=', 'shop_products.category_id')
                ->where('shop_products.display', true)
                ->whereColumn('product_category._lft', '>=', 'shop_categories._lft')
                ->whereColumn('product_category._rgt', '<=', 'shop_categories._rgt'));
        }

        if ($criteria->random) {
            $query->inRandomOrder();
        }

        if ($criteria->limit) {
            $query->limit($criteria->limit);
        }

        return $query->get();
    }

    /**
     * Preload the whole visible category tree in one query and attach each node's
     * children in memory, instead of letting CategoryResource query children per node.
     *
     * Lives here rather than in the controller: it is a read, and a controller is
     * an HTTP adapter (see .claude/backend.md).
     */
    public function withVisibleChildren(Collection $categories): Collection
    {
        if ($categories->isEmpty()) {
            return $categories;
        }

        $byParent = Category::visible()->defaultOrder()->get()->groupBy('parent_id');

        $assign = function (Category $node) use (&$assign, $byParent): void {
            $children = $byParent->get($node->id, collect());
            $node->setRelation('children', $children);
            $children->each($assign);
        };

        $categories->each($assign);

        return $categories;
    }
}
