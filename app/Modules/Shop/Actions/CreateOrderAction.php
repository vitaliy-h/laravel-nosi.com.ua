<?php

namespace App\Modules\Shop\Actions;

use App\Modules\Shop\Dtos\CreateOrderData;
use App\Modules\Shop\Models\Order;

readonly class CreateOrderAction
{
    public function __construct(
        private RecalculateOrderTotalAction $recalculate,
    ) {}

    public function handle(CreateOrderData $dto): Order
    {
        $order = Order::create([
            'user_id'        => $dto->user_id,
            'payment_status' => $dto->payment_status,
            'payment_type'   => $dto->payment_type,
            'total_amount'   => 0,
        ]);

        foreach ($dto->items as $item) {
            $order->items()->create([
                'shop_product_id' => $item->shop_product_id,
                'product_name'    => $item->product_name,
                'product_article'     => $item->product_article,
                'selling_price'      => $item->selling_price,
                'quantity'        => $item->quantity,
                'total'           => round($item->selling_price * $item->quantity, 2),
            ]);
        }

        $this->recalculate->handle($order);

        return $order->fresh();
    }
}
