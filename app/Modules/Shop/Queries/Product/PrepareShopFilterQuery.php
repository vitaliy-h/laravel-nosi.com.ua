<?php

namespace App\Modules\Shop\Queries\Product;

use App\Modules\Core\Support\ModuleGate;
use App\Modules\Shop\Dtos\Filters\AttributeFilterData;
use App\Modules\Shop\Dtos\Filters\InFilterData;
use App\Modules\Shop\Dtos\Filters\RangeFilterData;
use App\Modules\Shop\Dtos\Filters\SearchFilterData;
use App\Modules\Shop\Dtos\Filters\ShopFilterData;
use App\Modules\Shop\Dtos\ShopFilterCriteriaData;
use App\Modules\Shop\Enums\FilterTypeEnum;
use App\Modules\Shop\Models\Attribute;
use App\Modules\Shop\Models\AttributeOption;
use App\Modules\Shop\Models\Brand;
use App\Modules\Shop\Models\Category;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

readonly class PrepareShopFilterQuery
{
    public function __construct(
        private BuildProductsQuery $buildProductsQuery,
    ) {}

    /**
     * Prepare all available filters for the shop page.
     * This includes brands, product parameters and the price range.
     */
    public function handle(?Category $category, ShopFilterCriteriaData $criteria): ShopFilterData
    {
        $filter = new ShopFilterData(
            brand: new InFilterData(
                key: 'brand',
                type: FilterTypeEnum::IN,
                items: collect(),
                selected: $criteria->brands,
            ),
            price: new RangeFilterData(
                key: 'price',
                type: FilterTypeEnum::RANGE,
                min: 0,
                max: 0,
                from: $criteria->priceFrom,
                to: $criteria->priceTo,
            ),
            search: new SearchFilterData(
                key: 'search',
                type: FilterTypeEnum::SEARCH,
                value: $criteria->search,
            ),
        );

        $filter = $filter->withAttributes($this->attributeFilters($criteria));

        $filter = $filter->withBrand($filter->brand->withItems($this->prepareBrandFilter($category, $filter)));

        $filter = $filter->withAttributes($filter->attributes->map(
            fn (AttributeFilterData $item): AttributeFilterData => $item->withItems($this->prepareAttributeFilter($category, $filter, $item))
        ));

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
    private function prepareBrandFilter(?Category $category, ShopFilterData $filter): Collection
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
     * A filter per visible, filterable parameter, with what the URL selected
     * under its code. Their options are counted afterwards, once every
     * selection is known — each count depends on the other filters.
     *
     * @return Collection<string, AttributeFilterData>
     */
    private function attributeFilters(ShopFilterCriteriaData $criteria): Collection
    {
        return Attribute::visible()
            ->where('is_filterable', true)
            ->with('options')
            ->orderByDesc('rank')
            ->orderBy('id')
            ->get()
            ->mapWithKeys(fn (Attribute $attribute): array => [
                $attribute->code => new AttributeFilterData(
                    key: $attribute->code,
                    type: FilterTypeEnum::IN,
                    attributeId: $attribute->id,
                    title: (string) $attribute->title,
                    items: $attribute->options,
                    selected: array_values(array_intersect(
                        $criteria->attributes[$attribute->code] ?? [],
                        $attribute->options->pluck('id')->all(),
                    )),
                ),
            ]);
    }

    /**
     * Options of one parameter with their product counts, the parameter's
     * own selection left out — same as the brands. An option no product has
     * is dropped unless it is selected.
     */
    private function prepareAttributeFilter(?Category $category, ShopFilterData $filter, AttributeFilterData $current): Collection
    {
        $products = $this->buildProductsQuery->handle($category, $filter, $current->key, [])
            ->select('shop_products.id');

        $counts = DB::table('shop_attribute_option_product')
            ->whereIn('option_id', $current->items->pluck('id'))
            ->whereIn('product_id', $products)
            ->groupBy('option_id')
            ->selectRaw('option_id, COUNT(*) as total')
            ->pluck('total', 'option_id');

        return $current->items
            ->filter(fn (AttributeOption $option): bool => isset($counts[$option->id]) || in_array($option->id, $current->selected, true))
            ->map(function (AttributeOption $option) use ($counts): AttributeOption {
                $option->products_count = (int) ($counts[$option->id] ?? 0);

                return $option;
            })
            ->values();
    }

    /**
     * Prepare min and max values for a range filter.
     * The current range filter is excluded to avoid locking its own values.
     */
    private function prepareRangeFilter(?Category $category, ShopFilterData $filter, RangeFilterData $currentFilter): array
    {
        $query = $this->buildProductsQuery->handle($category, $filter, $currentFilter->key);

        return [
            'min' => (float) ((clone $query)->min($currentFilter->key) ?? 0),
            'max' => (float) ((clone $query)->max($currentFilter->key) ?? 0),
        ];
    }
}
