<?php

namespace App\Modules\ShopOrders\Actions;

use App\Modules\ShopCart\Actions\RecalculateCartTotalAction;
use App\Modules\ShopCart\Models\Cart;
use App\Modules\ShopCart\Services\CartService;
use App\Modules\ShopOrders\Dtos\CreateOrderData;
use App\Modules\ShopOrders\Dtos\CustomerData;
use App\Modules\ShopOrders\Dtos\OrderItemData;
use App\Modules\ShopOrders\Models\Order;

readonly class PlaceOrderFromCartAction
{
    public function __construct(
        private CreateOrderAction $createOrder,
        private CartService $cartService,
        private RecalculateCartTotalAction $recalculateCartTotal,
    ) {}

    public function handle(Cart $cart, CustomerData $customer): Order
    {
        // Re-validates the promocode at checkout time: it may have expired or
        // hit its usage limit since it was applied to the cart.
        $this->recalculateCartTotal->handle($cart);
        $cart->refresh();

        $cart->loadMissing('items');

        $items = $cart->items->map(fn ($item) => new OrderItemData(
            shop_product_id: $item->shop_product_id,
            product_name: $item->product_name,
            product_article: $item->product_article,
            selling_price: (float) $item->selling_price,
            quantity: $item->quantity,
        ))->all();

        $order = $this->createOrder->handle(new CreateOrderData(
            user_id: $cart->user_id ?? null,
            items: $items,
            user_name: $customer->user_name,
            user_surname: $customer->user_surname,
            user_email: $customer->user_email,
            user_phone: $customer->user_phone,
            comment: $customer->comment,
            promocode_id: $cart->promocode_id,
            promocode_code: $cart->promocode_code,
            discount_amount: (float) $cart->discount_amount,
        ));

        $cart->delete();

        $this->cartService->refreshCount(null);

        return $order;
    }
}
