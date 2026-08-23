<?php

namespace App\Modules\ShopOrders\Observers;

use App\Modules\ShopCart\Models\Promocode;
use App\Modules\ShopOrders\Actions\GenerateOrderNumberAction;
use App\Modules\ShopOrders\Actions\RecalculateOrderTotalAction;
use App\Modules\ShopOrders\Models\Order;

readonly class OrderObserver
{
    public function __construct(
        private GenerateOrderNumberAction $generateOrderNumber,
        private RecalculateOrderTotalAction $recalculate,
    ) {}

    public function created(Order $order): void
    {
        $this->generateOrderNumber->handle($order);
    }

    public function updated(Order $order): void
    {
        $this->generateOrderNumber->handle($order);

        // Admin edited the discount or the promocode in Filament — the stored
        // code and the total must follow. No recursion: both writes below go
        // through withoutEvents().
        if ($order->wasChanged('promocode_id')) {
            $code = $order->promocode_id !== null
                ? Promocode::find($order->promocode_id)?->code
                : null;

            $order->withoutEvents(fn () => $order->update(['promocode_code' => $code]));
        }

        if ($order->wasChanged(['discount_amount', 'promocode_id'])) {
            $this->recalculate->handle($order);
        }
    }
}
