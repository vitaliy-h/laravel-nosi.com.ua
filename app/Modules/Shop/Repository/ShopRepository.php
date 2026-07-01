<?php

namespace App\Modules\Shop\Repository;

use App\Modules\Core\Dtos\BreadcrumbData;
use App\Modules\Shop\Dtos\Filters\BaseFilterDto;
use App\Modules\Shop\Dtos\Filters\InFilterDto;
use App\Modules\Shop\Dtos\Filters\RangeFilterDto;
use App\Modules\Shop\Dtos\Filters\SearchFilterDto;
use App\Modules\Shop\Dtos\Filters\ShopFilterDto;
use App\Modules\Shop\Dtos\Filters\SortFilterDto;
use App\Modules\Shop\Enums\FilterTypeEnum;
use App\Modules\Shop\Enums\PerPageOptionEnum;
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
    private int $paginate;

    public function __construct()
    {
        $count = (int)request('c', PerPageOptionEnum::default()->value);

        $this->paginate = in_array($count, PerPageOptionEnum::values(), true)
            ? $count
            : PerPageOptionEnum::default()->value;
    }

    /**
     * Get shop categories tree.
     * used in API
     * \App\Modules\Shop\Http\Controllers\Api\ShopController::categories
     */
    public function getTree()
    {
        return Category::visible()->get()->toTree();
    }

    /**
     * Get product by slug and category.
     * used in API
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
        return Product::visible()
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
    public function prepareFilter(?Category $category, Request $request): ShopFilterDto
    {
        $filter = new ShopFilterDto(
            brand: new InFilterDto(
                key: 'brand',
                type: FilterTypeEnum::IN,
                items: collect(),
                selected: $this->stringToIntArray($request->query('brand')),
            ),
            price: new RangeFilterDto(
                key: 'price',
                type: FilterTypeEnum::RANGE,
                min: 0,
                max: 0,
                from: $request->query('price_from') !== null ? (float)$request->query('price_from') : null,
                to: $request->query('price_to') !== null ? (float)$request->query('price_to') : null,
            ),
            search: new SearchFilterDto(
                key: 'search',
                type: FilterTypeEnum::SEARCH,
                value: $request->query('search'),
            ),
            sort: new SortFilterDto(
                key: 'sort',
                type: FilterTypeEnum::SORT,
                value: $request->query('sort'),
            ),
        );

        $filter = $filter->withBrand($filter->brand->withItems($this->prepareBrandFilter($category, $filter)));

        $rangeData = $this->prepareRangeFilter($category, $filter, $filter->price);
        $filter = $filter->withPrice($filter->price->withRange($rangeData['min'], $rangeData['max']));

        return $filter;
    }

    /**
     * specialCategories
     */
    public function specialCategories(int $limit = 12)
    {
        return Category::visible()->whereNotNull('img')->withCount('products')->having('products_count', '>', 0)->inRandomOrder()->limit($limit)->get();
    }

    /**
     * bestSellers block
     */
    public function bestSellers(int $limit = 4)
    {
        return Product::visible()->with('category')->inRandomOrder()->limit($limit)->get();
    }

    /**
     * featured block
     */
    public function featuredCategories(int $limit = 8)
    {
        return Category::visible()->whereIsRoot()->orderBy('_lft')->get();
    }

    public function featuredProducts(int $limit = 8)
    {
        return Product::visible()->with(['category.ancestors'])->inRandomOrder()->limit($limit)->get();
    }

    /**
     * Get paginated products for the current category and selected filters.
     */
    public function productsByCategory(?Category $category = null, ?ShopFilterDto $filter = null)
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
    private function prepareBrandFilter(?Category $category, ShopFilterDto $filter): Collection
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
    private function prepareRangeFilter(?Category $category, ShopFilterDto $filter, RangeFilterDto $currentFilter): array
    {
        $query = $this->buildProductsQuery($category, $filter, $currentFilter->key);

        return [
            'min' => (float)((clone $query)->min($currentFilter->key) ?? 0),
            'max' => (float)((clone $query)->max($currentFilter->key) ?? 0),
        ];
    }

    /**
     * Build the base product query.
     * Applies category restriction and all selected filters except the excluded one.
     */
    private function buildProductsQuery(?Category $category = null, ?ShopFilterDto $filter = null, ?string $except = null)
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
        if ($filter) {
            $filter->toApplicable()
                ->reject(fn(BaseFilterDto $item) => $item->key === $except)
                ->each(fn(BaseFilterDto $item) => $item->apply($query));
        }

        return $query;
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
