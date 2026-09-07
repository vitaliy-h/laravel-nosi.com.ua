<?php

namespace App\Modules\ShopCart\Queries;

use App\Modules\ShopCart\Models\Cart;

/**
 * How many items the cart holds — the number shown in the header badge.
 *
 * Counts quantities, not rows: three of the same product is three items.
 */
class CountCartItemsQuery
{
    public function handle(?Cart $cart): int
    {
        return (int) ($cart?->items()->sum('quantity') ?? 0);
    }
}
