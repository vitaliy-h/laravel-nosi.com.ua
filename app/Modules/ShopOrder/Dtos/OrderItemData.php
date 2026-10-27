<?php

namespace App\Modules\ShopOrder\Dtos;

use Spatie\LaravelData\Data;

class OrderItemData extends Data
{
    public function __construct(
        public readonly int $shop_product_id,
        public readonly string $product_name,
        public readonly ?string $product_article,
        public readonly float $selling_price,
        public readonly int $quantity,
        // The set size of the line («5 шт.»); `quantity` counts such sets.
        public readonly int $pack_quantity = 1,
    ) {}
}
