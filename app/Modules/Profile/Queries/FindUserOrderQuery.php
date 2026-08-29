<?php

namespace App\Modules\Profile\Queries;

use App\Modules\ShopOrder\Models\Order;

class FindUserOrderQuery
{
    public function handle(int $orderId, int $userId): Order
    {
        return Order::query()
            ->where('id', $orderId)
            ->where('user_id', $userId)
            // `category` is what gives an order line its link back to the catalogue.
            ->with('items.product.category')
            ->firstOrFail();
    }
}
