<?php

namespace App\Modules\Orders\Actions;

use App\Modules\Basket\Models\Basket;
use App\Modules\Orders\Dtos\CreateOrderData;
use App\Modules\Orders\Dtos\CustomerData;
use App\Modules\Orders\Dtos\OrderItemData;
use App\Modules\Orders\Models\Order;

readonly class PlaceOrderFromBasketAction
{
    public function __construct(
        private CreateOrderAction $createOrder,
    ) {}

    public function handle(Basket $basket, CustomerData $customer): Order
    {
        $basket->loadMissing('items');

        $items = $basket->items->map(fn($item) => new OrderItemData(
            shop_product_id: $item->shop_product_id,
            product_name:    $item->product_name,
            product_article: $item->product_article,
            selling_price:   (float) $item->selling_price,
            quantity:        $item->quantity,
        ))->all();

        $order = $this->createOrder->handle(new CreateOrderData(
            user_id:      $basket->user_id ?? null,
            items:        $items,
            user_name:    $customer->user_name,
            user_surname: $customer->user_surname,
            user_email:   $customer->user_email,
            user_phone:   $customer->user_phone,
            comment:      $customer->comment,
        ));

        $basket->delete();

        return $order;
    }
}
