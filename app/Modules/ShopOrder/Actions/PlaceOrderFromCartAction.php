<?php

namespace App\Modules\ShopOrder\Actions;

use App\Modules\ShopCart\Actions\RecalculateCartTotalAction;
use App\Modules\ShopCart\Models\Cart;
use App\Modules\ShopOrder\Dtos\CreateOrderData;
use App\Modules\ShopOrder\Dtos\CustomerData;
use App\Modules\ShopOrder\Dtos\OrderItemData;
use App\Modules\ShopOrder\Models\Order;
use Illuminate\Support\Facades\DB;

readonly class PlaceOrderFromCartAction
{
    public function __construct(
        private CreateOrderAction $createOrder,
        private RecalculateCartTotalAction $recalculateCartTotal,
    ) {}

    /**
     * Recalculation, the order itself and the emptying of the cart are one
     * transaction: a failure halfway used to leave a half-written order, a
     * promocode already counted as used and a cart still full.
     */
    public function handle(Cart $cart, CustomerData $customer): Order
    {
        return DB::transaction(fn () => $this->place($cart, $customer));
    }

    private function place(Cart $cart, CustomerData $customer): Order
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

        return $order;
    }
}
