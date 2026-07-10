<?php

namespace App\Modules\ShopOrders\Queries;

use App\Modules\ShopOrders\Models\Order;

class FindOrderByUuidQuery
{
    public function handle(string $uuid): Order
    {
        return Order::where('uuid', $uuid)
            ->with(['items.product.category'])
            ->firstOrFail();
    }
}
