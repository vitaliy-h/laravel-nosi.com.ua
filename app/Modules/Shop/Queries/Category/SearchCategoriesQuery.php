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
