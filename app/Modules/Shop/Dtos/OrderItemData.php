<?php

namespace App\Modules\Shop\Dtos;

use Spatie\LaravelData\Data;

class OrderItemData extends Data
{
    public function __construct(
        public readonly int $shop_product_id,
        public readonly string $product_name,
        public readonly ?string $product_sku,
        public readonly float $sale_price,
        public readonly float $quantity,
    ) {}
}
