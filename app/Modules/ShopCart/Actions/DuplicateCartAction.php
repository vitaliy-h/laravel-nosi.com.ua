<?php

namespace App\Modules\ShopCart\Actions;

use App\Modules\ShopCart\Models\Cart;
use App\Modules\ShopCart\Queries\FindCartQuery;
use App\Modules\User\Models\User;

/**
 * Forks a cart somebody opened by link into a fresh cart of the caller's own,
 * dropping whatever cart they had before.
 *
 * Storing the new uuid in the session is the caller's job — see `CartContext`.
 */
class DuplicateCartAction
{
    public function __construct(
        private RecalculateCartTotalAction $recalculate,
        private FindCartQuery $findCart,
    ) {}

    public function handle(Cart $source, ?User $user, ?string $guestCartUuid = null): Cart
    {
        $this->findCart->handle($user, $guestCartUuid)?->delete();

        $cart = Cart::create(['user_id' => $user?->id]);

        foreach ($source->items as $item) {
            $cart->items()->create([
                'shop_product_id' => $item->shop_product_id,
                'product_name'    => $item->product_name,
                'product_article' => $item->product_article,
                'selling_price'   => $item->selling_price,
                'quantity'        => $item->quantity,
                'total'           => $item->total,
            ]);
        }

        $this->recalculate->handle($cart);

        return $cart;
    }
}
