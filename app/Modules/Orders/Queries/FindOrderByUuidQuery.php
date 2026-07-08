<?php

namespace App\Modules\Orders\Queries;

use App\Modules\Orders\Models\Order;

class FindOrderByUuidQuery
{
    public function handle(string $uuid): Order
    {
        return Order::where('uuid', $uuid)
            ->with(['items.product.category'])
            ->firstOrFail();
    }
}
