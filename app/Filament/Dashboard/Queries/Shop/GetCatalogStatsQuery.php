<?php

namespace App\Filament\Dashboard\Queries\Shop;

use App\Filament\Dashboard\Dtos\Shop\CatalogStatsData;
use App\Modules\Core\Support\ModuleGate;
use App\Modules\Shop\Models\Brand;
use App\Modules\Shop\Models\Category;
use App\Modules\Shop\Models\Product;
use Illuminate\Database\Eloquent\Builder;

class GetCatalogStatsQuery
{
    public function handle(): CatalogStatsData
    {
        $products = Product::query()
            ->selectRaw('COUNT(*) AS total')
            ->selectRaw('SUM(CASE WHEN display = 0 THEN 1 ELSE 0 END) AS hidden')
            ->first();

        $categories = Category::query()
            ->selectRaw('COUNT(*) AS total')
            ->selectRaw('SUM(CASE WHEN display = 0 THEN 1 ELSE 0 END) AS hidden')
            ->first();

        return new CatalogStatsData(
            productsTotal: (int) ($products->total ?? 0),
            productsHidden: (int) ($products->hidden ?? 0),
            categoriesTotal: (int) ($categories->total ?? 0),
            categoriesHidden: (int) ($categories->hidden ?? 0),
            brandsTotal: ModuleGate::isEnabled('shop_brands')
                ? Brand::query()->count()
                : null,
            incompleteProducts: self::incomplete(Product::query())->count(),
        );
    }

    /**
     * Products that cannot be sold as they are: no picture or no price.
     *
     * Shared with the ProductResource "incomplete" table filter, so the card on
     * the dashboard and the list it links to always show the same rows.
     */
    public static function incomplete(Builder $query): Builder
    {
        return $query->where(fn (Builder $q) => $q->whereNull('img')->orWhereNull('price'));
    }
}
