<?php

namespace App\Modules\Shop\Repository;

use App\Modules\Shop\Models\Order;

class OrderRepository
{
    public function findByUuid(string $uuid): Order
    {
        return Order::where('uuid', $uuid)
            ->with(['items.product.category'])
            ->firstOrFail();
    }
}
