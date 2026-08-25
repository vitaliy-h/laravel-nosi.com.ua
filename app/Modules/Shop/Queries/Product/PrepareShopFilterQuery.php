<?php

namespace App\Modules\Shop\Queries\Product;

use App\Modules\Core\Support\ModuleGate;
use App\Modules\Shop\Dtos\Filters\InFilterDto;
use App\Modules\Shop\Dtos\Filters\RangeFilterDto;
use App\Modules\Shop\Dtos\Filters\SearchFilterDto;
use App\Modules\Shop\Dtos\Filters\ShopFilterDto;
use App\Modules\Shop\Enums\FilterTypeEnum;
use App\Modules\Shop\Models\Brand;
use App\Modules\Shop\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

readonly class PrepareShopFilterQuery
{
    public function __construct(
        private BuildProductsQuery $buildProductsQuery,
    ) {}

    /**
     * Prepare all available filters for the shop page.
     * This includes categories, brands, and price range.
     */
    public function handle(?Category $category, Request $request): ShopFilterDto
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
                from: $request->query('price_from') !== null ? (float) $request->query('price_from') : null,
                to: $request->query('price_to') !== null ? (float) $request->query('price_to') : null,
            ),
            search: new SearchFilterDto(
                key: 'search',
                type: FilterTypeEnum::SEARCH,
                value: $request->query('search'),
            ),
        );

        $filter = $filter->withBrand($filter->brand->withItems($this->prepareBrandFilter($category, $filter)));

        $rangeData = $this->prepareRangeFilter($category, $filter, $filter->price);
        $filter = $filter->withPrice($filter->price->withRange($rangeData['min'], $rangeData['max']));

        return $filter;
    }

    /**
     * Prepare brand filter options.
     * Counts products for each brand while excluding the brand filter itself.
     *
     * Empty with shop_brands off, which drops the whole widget from the
     * sidebar — brands are not a thing on the storefront either then.
     */
    private function prepareBrandFilter(?Category $category, ShopFilterDto $filter): Collection
    {
        if (!ModuleGate::isEnabled('shop_brands')) {
            return collect();
        }

        $query = $this->buildProductsQuery->handle($category, $filter, 'brand');

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
        $query = $this->buildProductsQuery->handle($category, $filter, $currentFilter->key);

        return [
            'min' => (float) ((clone $query)->min($currentFilter->key) ?? 0),
            'max' => (float) ((clone $query)->max($currentFilter->key) ?? 0),
        ];
    }

    /**
     * Convert a comma-separated string or an array into a clean integer array.
     */
    private function stringToIntArray(string|array|null $value): array
    {
        if (is_array($value)) {
            return collect($value)
                ->map(fn ($item) => (int) $item)
                ->filter(fn ($item) => $item > 0)
                ->values()
                ->toArray();
        }

        if (! $value) {
            return [];
        }

        return collect(explode(',', $value))
            ->map(fn ($item) => (int) $item)
            ->filter(fn ($item) => $item > 0)
            ->values()
            ->toArray();
    }
}
