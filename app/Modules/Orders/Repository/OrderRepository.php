<?php

namespace App\Modules\Orders\Repository;

use App\Modules\Orders\Models\Order;

class OrderRepository
{
    public function findByUuid(string $uuid): Order
    {
        return Order::where('uuid', $uuid)
            ->with(['items.product.category'])
            ->firstOrFail();
    }
}
