<?php

namespace App\Modules\ShopOrder\Observers;

use App\Modules\ShopOrder\Actions\RecalculateOrderTotalAction;
use App\Modules\ShopOrder\Models\Order;
use App\Modules\ShopOrder\Models\OrderProduct;

readonly class OrderProductObserver
{
    public function __construct(
        private RecalculateOrderTotalAction $recalculate,
    ) {}

    public function created(OrderProduct $orderProduct): void
    {
        $this->refreshOrderOf($orderProduct);
    }

    public function updated(OrderProduct $orderProduct): void
    {
        $this->refreshOrderOf($orderProduct);
    }

    /**
     * Covers the Filament repeater: dropping a line from the order must move
     * the stored totals with it.
     */
    public function deleted(OrderProduct $orderProduct): void
    {
        $this->refreshOrderOf($orderProduct);
    }

    /**
     * Resolved by id rather than through `$orderProduct->order`: the relation
     * may be a cached instance loaded before the change, and the recalculation
     * reads `discount_amount` off it. Null when the parent order is itself gone.
     */
    private function refreshOrderOf(OrderProduct $orderProduct): void
    {
        $order = Order::find($orderProduct->shop_order_id);

        if ($order) {
            $this->recalculate->handle($order);
        }
    }
}
