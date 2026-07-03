<?php

namespace App\Modules\Profile\Repository;

use App\Modules\Profile\Dtos\OrderFilterData;
use App\Modules\Orders\Models\Order;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class OrderRepository
{
    private int $paginate = 4;

    public function forUser(int $userId, OrderFilterData $filter): LengthAwarePaginator
    {
        return Order::query()
            ->where('user_id', $userId)
            ->when($filter->status, fn($q) => $q->where('payment_status', $filter->status))
            ->when($filter->search, fn($q) => $q->where('order_number', 'like', '%' . $filter->search . '%'))
            ->with('items.product')
            ->orderBy('created_at', 'desc')
            ->paginate($this->paginate)
            ->withQueryString();
    }

    public function findForUser(int $orderId, int $userId): Order
    {
        return Order::query()
            ->where('id', $orderId)
            ->where('user_id', $userId)
            ->with('items.product')
            ->firstOrFail();
    }
}
