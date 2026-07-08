<?php

namespace App\Modules\Profile\Queries;

use App\Modules\Orders\Models\Order;
use App\Modules\Profile\Dtos\OrderFilterData;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class GetUserOrdersQuery
{
    private int $paginate = 4;

    public function handle(int $userId, OrderFilterData $filter): LengthAwarePaginator
    {
        return Order::query()
            ->where('user_id', $userId)
            ->when($filter->status, fn ($q) => $q->where('payment_status', $filter->status))
            ->when($filter->search, fn ($q) => $q->where('order_number', 'like', '%'.$filter->search.'%'))
            ->with('items.product')
            ->orderBy('created_at', 'desc')
            ->paginate($this->paginate)
            ->withQueryString();
    }
}
