<?php

namespace App\Modules\Profile\Queries;

use App\Modules\ShopOrders\Models\Order;

class FindUserOrderQuery
{
    public function handle(int $orderId, int $userId): Order
    {
        return Order::query()
            ->where('id', $orderId)
            ->where('user_id', $userId)
            ->with('items.product')
            ->firstOrFail();
    }
}
