<?php

namespace App\Modules\Shop\Repository;

use App\Modules\Core\Enums\PageModule;
use App\Modules\Shop\Models\Brand;
use App\Modules\Shop\Models\Category;
use App\Modules\Shop\Models\Parameter;
use App\Modules\Shop\Models\Product;
use Illuminate\Http\Request;

class ShopRepository
{
    private int $paginate = 10;

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
        $category->products_count = Product::visible()
            ->whereIn('category_id', Category::whereDescendantOrSelf($category)->pluck('id'))
            ->count();
        return $category;
    }

    public function getProductById(int $id): Product
    {
        return Product::query()
            ->where('id', $id)
            ->firstOrFail();
    }

    public function getCategoryBySlug(string $slug): Category
    {
        return Category::visible()
            ->where('slug', $slug)
            ->firstOrFail();
    }

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

    public function prepareFilter(?Category $category, Request $request): array
    {
        $filter = [
            'categories' => [
                'key' => 'categories',
                'type' => 'tree',
                'data' => $this->allCategories($category?->id),
            ],

            'brand' => [
                'key' => 'brand',
                'type' => 'in',
                'value' => $request->query('brand'),
                'data' => collect(),
            ],

            'price' => [
                'key' => 'price',
                'type' => 'range',
                'from' => $request->query('price_from'),
                'to' => $request->query('price_to'),
                'data' => [
                    'min' => 0,
                    'max' => 0,
                ],
            ],

            /*
            'parameters' => [],
            'shop_parameters' => collect(),
            */
        ];

        /*
        if ($category && !empty($category->parameters)) {
            $parameterIds = $this->getFilterableParameterIds($category->parameters);

            if (!empty($parameterIds)) {
                $shopParameters = Parameter::visible()
                    ->whereIn('id', $parameterIds)
                    ->orderBy('rank', 'desc')
                    ->get()
                    ->keyBy('id');

                foreach ($shopParameters as $parameter) {
                    $key = 'p' . $parameter->id;

                    $filter['parameters'][$parameter->id] = [
                        'key' => $key,
                        'type' => $this->getParameterFilterType($parameter),
                        'value' => $request->query($key),
                        'data' => [],
                    ];
                }

                $filter['shop_parameters'] = $shopParameters;
            }
        }
        */

        $filter['brand']['data'] = $this->prepareBrandFilter($category, $filter);
        $filter['price']['data'] = $this->prepareRangeFilter($category, $filter, $filter['price']);

        /*
        foreach ($filter['parameters'] ?? [] as $parameterFilter) {
            if (($parameterFilter['type'] ?? null) === 'range') {
                $filter['parameters'][$parameterFilter['key']]['data'] = $this->prepareRangeFilter($category, $filter, $parameterFilter);
            }
        }
        */

        return $filter;
    }

    public function productsByCategory(?Category $category = null, array $filter = [])
    {
        return $this->buildProductsQuery($category, $filter)
            ->orderBy('price')
            ->paginate($this->paginate)
            ->withQueryString();
    }

    private function prepareBrandFilter(?Category $category = null, array $filter = [])
    {
        $query = $this->buildProductsQuery($category, $filter, 'brand');

        $brandCounts = $query
            ->selectRaw('brand_id, COUNT(*) as total')
            ->whereNotNull('brand_id')
            ->groupBy('brand_id')
            ->pluck('total', 'brand_id');

        return Brand::query()
            ->whereIn('id', $brandCounts->keys())
            ->orderBy('title')
            ->get()
            ->map(function ($brand) use ($brandCounts) {
                $brand->products_count = $brandCounts[$brand->id] ?? 0;
                return $brand;
            });
    }

    private function prepareRangeFilter(?Category $category, array $filter, array $currentFilter): array
    {
        $query = $this->buildProductsQuery($category, $filter, $currentFilter['key']);

        return [
            'min' => (float)((clone $query)->min($currentFilter['key']) ?? 0),
            'max' => (float)((clone $query)->max($currentFilter['key']) ?? 0),
        ];
    }

    private function buildProductsQuery(?Category $category = null, array $filter = [], ?string $except = null)
    {
        $query = Product::visible()
            ->with('brand');

        if ($category) {
            $categoryIds = Category::whereDescendantOrSelf($category)
                ->pluck('id');

            $query->whereIn('category_id', $categoryIds);
        }

        foreach ($filter as $item) {
            if (!is_array($item)) {
                continue;
            }

            if (($item['key'] ?? null) === $except) {
                continue;
            }

            $this->applyFilter($query, $item);
        }

        /*
        foreach ($filter['parameters'] ?? [] as $item) {
            if (($item['key'] ?? null) === $except) {
                continue;
            }

            $this->applyFilter($query, $item);
        }
        */

        return $query;
    }

    private function applyFilter($query, ?array $filter): void
    {
        if (!$filter) {
            return;
        }

        $key = $filter['key'] ?? null;
        $type = $filter['type'] ?? null;
        $value = $filter['value'] ?? null;

        if (!$key || !$type || $type === 'tree') {
            return;
        }

        if ($type === 'range') {
            $from = $filter['from'] ?? null;
            $to = $filter['to'] ?? null;

            if ($from !== null && $from !== '') {
                $query->where($key, '>=', (float)$from);
            }

            if ($to !== null && $to !== '') {
                $query->where($key, '<=', (float)$to);
            }

            return;
        }

        if ($value === null || $value === '') {
            return;
        }

        if ($type === 'in') {
            $ids = $this->stringToIntArray($value);

            if (!empty($ids)) {
                $query->whereIn($key === 'brand' ? 'brand_id' : $key, $ids);
            }

            return;
        }

        if ($type === 'equal' || $type === 'boolean') {
            $query->where($key, (int)$value);
            return;
        }

        if ($type === 'min') {
            $query->where($key, '>=', (float)$value);
            return;
        }

        if ($type === 'max') {
            $query->where($key, '<=', (float)$value);
            return;
        }

        if ($type === 'find_in_set') {
            $ids = $this->stringToIntArray($value);

            if (!empty($ids)) {
                $query->where(function ($q) use ($ids, $key) {
                    foreach ($ids as $id) {
                        $q->orWhereRaw("FIND_IN_SET(?, {$key})", [$id]);
                    }
                });
            }
        }
    }

    private function getParameterFilterType(Parameter $parameter): string
    {
        return match ((int)$parameter->type) {
            1 => 'in',
            2 => 'range',
            3 => 'boolean',
            4 => 'find_in_set',
            default => 'equal',
        };
    }

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

    private function stringToIntArray(string|array|null $value): array
    {
        if (is_array($value)) {
            return collect($value)
                ->map(fn($item) => (int)$item)
                ->filter(fn($item) => $item > 0)
                ->values()
                ->toArray();
        }

        if (!$value) {
            return [];
        }

        return collect(explode(',', $value))
            ->map(fn($item) => (int)$item)
            ->filter(fn($item) => $item > 0)
            ->values()
            ->toArray();
    }
}
