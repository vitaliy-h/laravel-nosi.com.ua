<?php

namespace App\Modules\ShopCart\Dtos;

use Spatie\LaravelData\Data;

class CartStatsData extends Data
{
    public function __construct(
        public readonly int $activeCarts,
        public readonly int $abandonedCarts,
        public readonly float $cartsValue,
        /** Every cart created in the period, including ones since converted
         *  into an order and deleted — see GetCartStatsQuery. */
        public readonly int $cartsInPeriod,
        public readonly int $ordersInPeriod,
        /** Percent of carts that turned into an order, null when there were no carts. */
        public readonly ?float $conversion,
    ) {}
}
