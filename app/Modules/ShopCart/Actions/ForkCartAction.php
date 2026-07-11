<?php

namespace App\Modules\ShopCart\Actions;

use App\Modules\ShopCart\Models\Cart;
use App\Modules\Core\Models\User;
use Illuminate\Support\Facades\Session;

class ForkCartAction
{
    public function __construct(
        private RecalculateCartTotalAction $recalculate,
        private FindCartAction $findCart,
    ) {}

    public function handle(Cart $source, ?User $user): Cart
    {
        $this->findCart->handle($user)?->delete();

        $cart = Cart::create(['user_id' => $user?->id]);

        foreach ($source->items as $item) {
            $cart->items()->create([
                'shop_product_id' => $item->shop_product_id,
                'product_name' => $item->product_name,
                'product_article' => $item->product_article,
                'selling_price' => $item->selling_price,
                'quantity' => $item->quantity,
                'total' => $item->total,
            ]);
        }

        $this->recalculate->handle($cart);

        Session::put('cart_uuid', $cart->uuid);

        return $cart;
    }
}
