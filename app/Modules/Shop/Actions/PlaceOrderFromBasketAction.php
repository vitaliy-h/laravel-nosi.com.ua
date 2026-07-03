<?php

namespace App\Modules\Shop\Actions;

use App\Modules\Basket\Models\Basket;
use App\Modules\Shop\Dtos\CreateOrderData;
use App\Modules\Shop\Dtos\OrderItemData;
use App\Modules\Shop\Models\Order;

readonly class PlaceOrderFromBasketAction
{
    public function __construct(
        private CreateOrderAction $createOrder,
    ) {}

    public function handle(Basket $basket, array $customer = []): Order
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
            user_id:      $basket->user_id ?? 0,
            items:        $items,
            user_name:    $customer['user_name'] ?? null,
            user_surname: $customer['user_surname'] ?? null,
            user_email:   $customer['user_email'] ?? null,
            user_phone:   $customer['user_phone'] ?? null,
            comment:      $customer['comment'] ?? null,
        ));

        $basket->delete();

        return $order;
    }
}
