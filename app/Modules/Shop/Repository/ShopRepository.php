<?php

namespace App\Modules\Shop\Repository;

use App\Modules\Core\Enums\PageModule;
use App\Modules\Shop\Models\Category;
use App\Modules\Shop\Models\Parameter;
use App\Modules\Shop\Models\Product;
use Illuminate\Http\Request;

class ShopRepository
{
    private int $paginate = 10;

    /**
     * Get shop categories tree.
     */
    public function getTree()
    {
        return Category::visible()->get()->toTree();
    }


    public function allCategories(?int $activeCategoryId = null)
    {
        $categories = Category::visible()
            ->with('childrenRecursive')
            ->whereNull('parent_id')
            ->orderBy('_lft')
            ->get();

        $openedIds = [];

        if ($activeCategoryId) {
            $activeCategory = Category::find($activeCategoryId);

            if ($activeCategory) {
                $openedIds = $activeCategory
                    ->ancestors()
                    ->pluck('id')
                    ->toArray();
            }
        }

        return $categories->map(function ($category) use ($activeCategoryId, $openedIds) {
            return $this->markCategoryTree($category, $activeCategoryId, $openedIds);
        });
    }

    private function markCategoryTree(Category $category, ?int $activeCategoryId, array $openedIds): Category
    {
        $category->_active = $category->id == $activeCategoryId ? 1 : 0;
        $category->_opened = in_array($category->id, $openedIds) ? 1 : 0;

        $category->childrenRecursive = $category->childrenRecursive->map(function ($child) use ($activeCategoryId, $openedIds) {
            return $this->markCategoryTree($child, $activeCategoryId, $openedIds);
        });

        return $category;
    }



    /**
     * Get top-level shop categories.
     */
    public function mainCategories()
    {
        return Category::visible()
            ->whereNull('parent_id')
            ->orderBy('_lft')
            ->get();
    }

    public function getProductById(int $id)
    {
        return Product::query()
            ->where('id', $id)
            ->firstOrFail();
    }

    /**
     * Get category by slug.
     */
    public function getCategoryBySlug(string $slug): Category
    {
        return Category::visible()
            ->where('slug', $slug)
            ->firstOrFail();
    }

    /**
     * Get product by slug and category.
     */
    public function getProductBySlug(string $slug, Category $category): Product
    {
        return Product::visible()
            ->where('slug', $slug)
            ->where('category_id', $category->id)
            ->with('brand')
            ->firstOrFail();
    }

    /**
     * Get direct child categories.
     */
    public function childCategories(Category $category)
    {
        return Category::visible()
            ->where('parent_id', $category->id)
            ->orderBy('_lft')
            ->get();
    }

    /**
     * Get category breadcrumbs by nested set keys.
     */
    public function breadcrumbs(Category $category): array
    {
        return Category::visible()
            ->where('_lft', '<=', $category->_lft)
            ->where('_rgt', '>=', $category->_rgt)
            ->orderBy('_lft')
            ->get()
            ->map(fn($item) => [
                'name' => $item->title,
                'url' => module_url(PageModule::SHOP, [$item->slug]),
            ])
            ->toArray();
    }

    /**
     * Prepare filter data from request.
     */
    public function prepareFilters(Category $category, Request $request): array
    {
        $filters = [
            'brand' => $request->query('brand'),
            'price' => $request->query('price'),
            'parameters' => [],
            'shop_parameters' => collect(),
        ];

        if (!$category->parameters) {
            return $filters;
        }

        $parameterIds = $this->getFilterableParameterIds($category->parameters);

        if (empty($parameterIds)) {
            return $filters;
        }

        $shopParameters = Parameter::visible()
            ->whereIn('id', $parameterIds)
            ->orderBy('rank', 'desc')
            ->get()
            ->keyBy('id');

        foreach ($shopParameters as $parameter) {
            $filters['parameters'][$parameter->id] = $request->query('p' . $parameter->id);
        }

        $filters['shop_parameters'] = $shopParameters;

        return $filters;
    }

    /**
     * Get products by category with filters.
     */
    /*
    public function productsByCategory(ShopCategory $category, array $filters)
    {
        $query = ShopProduct::visible()
            ->where('category', $category->id);

        // Brand filter.
        if (!empty($filters['brand'])) {
            $brandIds = $this->stringToIntArray($filters['brand']);

            if (!empty($brandIds)) {
                $query->whereIn('brand', $brandIds);
            }
        }

        // Price filter. Expected format: min-max.
        if (!empty($filters['price'])) {
            [$min, $max] = array_pad(explode('-', $filters['price']), 2, 0);

            if ((int) $min > 0) {
                $query->where('price', '>=', (int) $min);
            }

            if ((int) $max > 0) {
                $query->where('price', '<=', (int) $max);
            }
        }

        // Dynamic parameter filters.
        foreach ($filters['shop_parameters'] as $parameter) {
            $value = $filters['parameters'][$parameter->id] ?? null;

            if ($value === null || $value === '') {
                continue;
            }

            $field = 'p' . $parameter->id;

            // Select.
            if ((int) $parameter->type === 1) {
                $ids = $this->stringToIntArray($value);

                if (!empty($ids)) {
                    $query->whereIn($field, $ids);
                }
            }

            // Number from-to. Expected format: min-max.
            elseif ((int) $parameter->type === 2) {
                [$min, $max] = array_pad(explode('-', $value), 2, 0);

                if ((int) $min > 0) {
                    $query->where($field, '>=', (int) $min);
                }

                if ((int) $max > 0) {
                    $query->where($field, '<=', (int) $max);
                }
            }

            // Boolean.
            elseif ((int) $parameter->type === 3) {
                $query->where($field, (int) $value);
            }

            // Multi checkbox stored as comma-separated values.
            elseif ((int) $parameter->type === 4) {
                $ids = $this->stringToIntArray($value);

                $query->where(function ($q) use ($ids, $field) {
                    foreach ($ids as $id) {
                        $q->orWhereRaw("FIND_IN_SET(?, {$field})", [$id]);
                    }
                });
            }
        }

        return $query
            ->orderBy('price')
            ->paginate($this->paginate)
            ->withQueryString();
    }
    /**/
    public function productsByCategory(?Category $category = null, array $filters = [])
    {
        $query = Product::visible()
            ->with('brand');

        if ($category) {
            $categoryIds = Category::whereDescendantOrSelf($category)
                ->pluck('id');

            $query->whereIn('category_id', $categoryIds);
        }

        return $query
            ->orderBy('price')
            ->paginate($this->paginate)
            ->withQueryString();
    }

    /**
     * Extract filterable parameter IDs from serialized category parameters.
     */
    private function getFilterableParameterIds(?string $parameters): array
    {
        $data = @unserialize($parameters);

        if (!is_array($data)) {
            return [];
        }

        $ids = [];

        foreach ($data as $id => $item) {
            if (!empty($item['display']) && !empty($item['filter'])) {
                $ids[] = (int)$id;
            }
        }

        return $ids;
    }

    /**
     * Convert comma-separated string to integer array.
     */
    private function stringToIntArray(string $value): array
    {
        return collect(explode(',', $value))
            ->map(fn($item) => (int)$item)
            ->filter(fn($item) => $item > 0)
            ->values()
            ->toArray();
    }
}
