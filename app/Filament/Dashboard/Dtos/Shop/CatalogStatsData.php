<?php

namespace App\Filament\Dashboard\Dtos\Shop;

use Spatie\LaravelData\Data;

/**
 * Catalog size and health for the dashboard, as of right now — the period
 * filter does not apply to a catalog snapshot.
 */
class CatalogStatsData extends Data
{
    public function __construct(
        public readonly int $productsTotal,
        public readonly int $productsHidden,
        public readonly int $categoriesTotal,
        public readonly int $categoriesHidden,
        // null when the shop_brands module is off — the card is dropped
        // instead of showing a count nobody can act on.
        public readonly ?int $brandsTotal,
        public readonly int $incompleteProducts,
    ) {}
}
