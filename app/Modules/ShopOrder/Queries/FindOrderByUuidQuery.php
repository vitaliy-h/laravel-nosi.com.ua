<?php

namespace App\Modules\ShopOrder\Queries;

use App\Modules\ShopOrder\Models\Order;

class FindOrderByUuidQuery
{
    public function handle(string $uuid): Order
    {
        return Order::where('uuid', $uuid)
            ->with(['items.product.category'])
            ->firstOrFail();
    }
}
