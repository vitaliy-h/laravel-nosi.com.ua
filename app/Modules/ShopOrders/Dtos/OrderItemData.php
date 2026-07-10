<?php

namespace App\Modules\ShopOrders\Dtos;

use Spatie\LaravelData\Data;

class OrderItemData extends Data
{
    public function __construct(
        public readonly int $shop_product_id,
        public readonly string $product_name,
        public readonly ?string $product_article,
        public readonly float $selling_price,
        public readonly int $quantity,
    ) {}
}
