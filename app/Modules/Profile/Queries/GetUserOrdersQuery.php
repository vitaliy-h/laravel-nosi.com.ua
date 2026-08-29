<?php

namespace App\Modules\Profile\Queries;

use App\Modules\Profile\Dtos\OrderFilterData;
use App\Modules\ShopOrder\Models\Order;
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
            // `category` is what gives an order line its link back to the catalogue.
            ->with('items.product.category')
            ->orderBy('created_at', 'desc')
            ->paginate($this->paginate)
            ->withQueryString();
    }
}
