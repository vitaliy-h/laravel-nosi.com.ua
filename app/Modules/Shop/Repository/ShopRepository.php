<?php

namespace App\Modules\Shop\Repository;

use App\Modules\Core\Dtos\BreadcrumbData;
use App\Modules\Core\Enums\PageModule;
use App\Modules\Shop\Models\Brand;
use App\Modules\Shop\Models\Category;
use App\Modules\Shop\Models\Parameter;
use App\Modules\Shop\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

class ShopRepository
{
    /**
     * Number of products displayed per page.
     */
    private int $paginate = 12;

    public function __construct()
    {
        $count = (int)request('c', 12);

        $this->paginate = in_array($count, [12, 24, 48, 96], true)
            ? $count
            : 12;
    }

    /**
     * Get shop categories tree.
     * TODO: used in API — kept for that reason
     * \App\Modules\Shop\Http\Controllers\Api\ShopController::categories
     */
    public function getTree()
    {
        return Category::visible()->get()->toTree();
    }

    /**
     * Get product by slug and category.
     * TODO: used in API — kept for that reason
     * \App\Modules\Shop\Http\Controllers\Api\ShopController::product
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
     * Get all root categories with their recursive children.
     * Also marks the active category and opened parent categories.
     * @todo extracted from filters and rewrote to reduce DB queries
     */
    public function allCategories(?int $activeCategoryId = null)
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
                ->filter(fn(Category $c) => $c->_lft >= $category->_lft && $c->_lft <= $category->_rgt)
                ->sum(fn(Category $c) => $productCounts->get($c->id, 0));
        });

        return $categories->toTree();
    }

    /**
     * Get a single product by ID.
     */
    public function getProductById(int $id): Product
    {
        return Product::query()
            ->where('id', $id)
            ->firstOrFail();
    }

    /**
     * Get a visible category by slug.
     */
    public function getCategoryBySlug(string $slug): Category
    {
        return Category::visible()
            ->where('slug', $slug)
            ->firstOrFail();
    }

    /**
     * Build breadcrumbs for the current category.
     */
    public function breadcrumbs(Category $category): Collection
    {
        $categoryBreadcrumbs = Category::visible()
            ->where('_lft', '<=', $category->_lft)
            ->where('_rgt', '>=', $category->_rgt)
            ->orderBy('_lft')
            ->get()
            ->map(fn(Category $category) => new BreadcrumbData(
                title: $category->title,
                url: module_url('shop', [$category->slug]),
            ));

        return collect(view()->shared('breadcrumbs'))
            ->merge($categoryBreadcrumbs)
            ->values();
    }

    /**
     * Prepare all available filters for the shop page.
     * This includes categories, brands, and price range.
     */
    public function prepareFilter(?Category $category, Request $request): array
    {
        /*
         * TODO: Refactor filters using DTO + Enum
         *
         * Current approach — plain arrays. Proposed approach — typed DTOs:
         *
         * 1. Filter type enum:
         *
         *    enum FilterType: string
         *    {
         *        case IN     = 'in';
         *        case RANGE  = 'range';
         *        case SEARCH = 'search';
         *        case SORT   = 'sort';
         *    }
         *
         * 2. DTO per filter type (with apply method):
         *
         *    abstract class BaseFilterDto extends Data
         *    {
         *        abstract public function apply(Builder $query): void;
         *    }
         *
         *    class InFilterDto extends BaseFilterDto
         *    {
         *        public function __construct(
         *            public readonly string     $key,
         *            public readonly FilterType $type,
         *            public readonly Collection $items,    // for display (brands, options)
         *            public readonly array      $selected, // selected ids
         *        ) {}
         *
         *        public function apply(Builder $query): void
         *        {
         *            if (empty($this->selected)) return;
         *            $column = $this->key === 'brand' ? 'brand_id' : $this->key;
         *            $query->whereIn($column, $this->selected);
         *        }
         *    }
         *
         *    class RangeFilterDto extends BaseFilterDto
         *    {
         *        public function __construct(
         *            public readonly string     $key,
         *            public readonly FilterType $type,
         *            public readonly float      $min,
         *            public readonly float      $max,
         *            public readonly ?float     $from,
         *            public readonly ?float     $to,
         *        ) {}
         *
         *        public function apply(Builder $query): void
         *        {
         *            if ($this->from !== null) $query->where($this->key, '>=', $this->from);
         *            if ($this->to   !== null) $query->where($this->key, '<=', $this->to);
         *        }
         *    }
         *
         *    class SearchFilterDto extends BaseFilterDto
         *    {
         *        public function __construct(
         *            public readonly FilterType $type,
         *            public readonly ?string    $value,
         *        ) {}
         *
         *        public function apply(Builder $query): void
         *        {
         *            if ($this->value) $query->where('title', 'like', "%{$this->value}%");
         *        }
         *    }
         *
         * 3. prepareFilter returns Collection<BaseFilterDto>:
         *
         *    public function prepareFilter(?Category $category, Request $request): Collection
         *    {
         *        $filters = collect([
         *            'brand' => new InFilterDto(
         *                key: 'brand', type: FilterType::IN, items: collect(),
         *                selected: array_filter(array_map('intval', (array) $request->query('brand'))),
         *            ),
         *            'price' => new RangeFilterDto(
         *                key: 'price', type: FilterType::RANGE,
         *                min: 0, max: 0,
         *                from: $request->float('price_from'),
         *                to: $request->float('price_to'),
         *            ),
         *            'search' => new SearchFilterDto(type: FilterType::SEARCH, value: $request->query('search')),
         *        ]);
         *
         *        $filters['brand'] = $filters['brand']->with(items: $this->prepareBrandFilter($category, $filters));
         *        $filters['price'] = $this->prepareRangeFilter($category, $filters, $filters['price']);
         *
         *        return $filters;
         *    }
         *
         * 4. buildProductsQuery simplifies to:
         *
         *    $filters
         *        ->reject(fn ($f, $key) => $key === $except)
         *        ->each(fn (BaseFilterDto $f) => $f->apply($query));
         *
         * 5. Blade — partial per type (no if/switch):
         *
         *    @foreach($filters as $key => $filter)
         *        @includeIf('pages.shop.filters.' . $filter->type->value, ['filter' => $filter])
         *    @endforeach
         *
         *    resources/views/pages/shop/filters/
         *        range.blade.php   ← $filter->min, $filter->max, $filter->from
         *        in.blade.php      ← @foreach($filter->items as $item)
         *        search.blade.php  ← <input value="{{ $filter->value }}">
         * 6. After refactoring, consider splitting this repository — it is getting large
         */
        $filter = [
            /**
             * Brand filter.
             */
            'brand' => [
                'key' => 'brand',
                'type' => 'in',
                'value' => $request->query('brand'),
                'data' => collect(),
            ],
            /**
             * Price range filter.
             */
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
            'search' => [
                'key' => 'search',
                'type' => 'search',
                'value' => $request->query('search'),
            ],

            'sort' => [
                'key' => 'sort',
                'type' => 'sort',
                'value' => $request->query('sort'),
            ],
            /*
             * commented parameters
             */
            /*
            'parameters' => [],
            'shop_parameters' => collect(),
            */
        ];

        /*
         * commented parameters
         */
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
        /**
         * Prepare dynamic filter data based on the current category and selected filters.
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

    /**
     * Get paginated products for the current category and selected filters.
     */
    public function productsByCategory(?Category $category = null, array $filter = [])
    {
        $query = $this->buildProductsQuery($category, $filter);

        $this->applySorting($query, request('sort'));

        return $query
            ->paginate($this->paginate)
            ->withQueryString();
    }

    /**
     * Prepare brand filter options.
     * Counts products for each brand while excluding the brand filter itself.
     */
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

    /**
     * Prepare min and max values for a range filter.
     * The current range filter is excluded to avoid locking its own values.
     */
    private function prepareRangeFilter(?Category $category, array $filter, array $currentFilter): array
    {
        $query = $this->buildProductsQuery($category, $filter, $currentFilter['key']);

        return [
            'min' => (float)((clone $query)->min($currentFilter['key']) ?? 0),
            'max' => (float)((clone $query)->max($currentFilter['key']) ?? 0),
        ];
    }

    /**
     * Build the base product query.
     * Applies category restriction and all selected filters except the excluded one.
     */
    private function buildProductsQuery(?Category $category = null, array $filter = [], ?string $except = null)
    {
        $query = Product::visible()
            ->with('brand', 'category');
        /**
         * Restrict products to the current category and all its descendants.
         */
        if ($category) {
            $categoryIds = Category::whereDescendantOrSelf($category)
                ->pluck('id');

            $query->whereIn('category_id', $categoryIds);
        }
        /**
         * Apply all active filters except the one currently being recalculated.
         */
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
        //$this->applySorting($query, request('sort'));

        return $query;
    }

    /**
     * Apply a single filter to the product query.
     */
    private function applyFilter($query, ?array $filter): void
    {
        if (!$filter) {
            return;
        }

        $key = $filter['key'] ?? null;
        $type = $filter['type'] ?? null;
        $value = $filter['value'] ?? null;
        /**
         * Tree filters are used only for display and are not applied here.
         */
        if (!$key || !$type || $type === 'tree') {
            return;
        }
        /**
         * Apply range filter.
         */
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
        if ($type === 'search') {
            if ($value !== null && trim($value) !== '') {
                $query->where('title', 'like', '%' . trim($value) . '%');
            }

            return;
        }

        if ($type === 'sort') {
            return;
        }
        if ($value === null || $value === '') {
            return;
        }
        /**
         * Apply multiple selected values.
         */
        if ($type === 'in') {
            $ids = $this->stringToIntArray($value);

            if (!empty($ids)) {
                $query->whereIn($key === 'brand' ? 'brand_id' : $key, $ids);
            }

            return;
        }
        /**
         * Apply exact value filters.
         */
        if ($type === 'equal' || $type === 'boolean') {
            $query->where($key, (int)$value);
            return;
        }
        /**
         * Apply minimum value filter.
         */
        if ($type === 'min') {
            $query->where($key, '>=', (float)$value);
            return;
        }
        /**
         * Apply maximum value filter.
         */
        if ($type === 'max') {
            $query->where($key, '<=', (float)$value);
            return;
        }
        /**
         * Apply FIND_IN_SET filter for comma-separated database fields.
         */
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

    /**
     * Apply selected sorting to the products query.
     * Falls back to default shop sorting when no valid sort option is provided.
     */
    private function applySorting($query, ?string $sort): void
    {
        match ($sort) {
            'price_asc' => $query->orderBy('price', 'asc'),
            'price_desc' => $query->orderBy('price', 'desc'),
            'newest' => $query->orderBy('id', 'desc'),
            'title_asc' => $query->orderBy('title', 'asc'),
            'title_desc' => $query->orderBy('title', 'desc'),
            default => $query->orderBy('id', 'desc'),
        };
    }

    /**
     * Detect filter type by parameter type.
     */
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
     * Convert a comma-separated string or an array into a clean integer array.
     */
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
