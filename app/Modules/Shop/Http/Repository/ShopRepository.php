<?php

namespace App\Modules\Shop\Http\Repository;

use App\Modules\Core\Enums\PageModule;
use App\Modules\Shop\Http\Models\ShopCategory;
use App\Modules\Shop\Http\Models\ShopParameter;
use App\Modules\Shop\Http\Models\ShopProduct;
use Illuminate\Http\Request;

class ShopRepository
{
    private int $paginate = 10;

    /**
     * Get top-level shop categories.
     */
    public function mainCategories()
    {
        return ShopCategory::visible()
            ->whereNull('parent_id')
            ->orderBy('rank')
            ->orderBy('_lft')
            ->get();
    }

    /**
     * Get category by slug.
     */
    public function getCategoryBySlug(string $slug): ShopCategory
    {
        return ShopCategory::visible()
            ->where('slug', $slug)
            ->firstOrFail();
    }

    /**
     * Get product by slug and category.
     */
    public function getProductBySlug(string $slug, ShopCategory $category): ShopProduct
    {
        return ShopProduct::visible()
            ->where('slug', $slug)
            ->where('category_id', $category->id)
            ->firstOrFail();
    }

    /**
     * Get direct child categories.
     */
    public function childCategories(ShopCategory $category)
    {
        return ShopCategory::visible()
            ->where('parent_id', $category->id)
            ->orderBy('rank')
            ->orderBy('_lft')
            ->get();
    }

    /**
     * Get category breadcrumbs by nested set keys.
     */
    public function breadcrumbs(ShopCategory $category): array
    {
        return ShopCategory::visible()
            ->where('_lft', '<=', $category->_lft)
            ->where('_rgt', '>=', $category->_rgt)
            ->orderBy('_lft')
            ->get()
            ->map(fn ($item) => [
                'name' => $item->title,
                'url' => module_url(PageModule::SHOP, [$item->slug]),
            ])
            ->toArray();
    }

    /**
     * Prepare filter data from request.
     */
    public function prepareFilters(ShopCategory $category, Request $request): array
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

        $shopParameters = ShopParameter::visible()
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
    public function productsByCategory(ShopCategory $category, array $filters = [])
    {
        return ShopProduct::visible()
            ->where('category_id', $category->id)
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
                $ids[] = (int) $id;
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
            ->map(fn ($item) => (int) $item)
            ->filter(fn ($item) => $item > 0)
            ->values()
            ->toArray();
    }
}
