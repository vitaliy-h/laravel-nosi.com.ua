<?php

namespace App\Modules\ShopCart\Actions;

use App\Modules\Core\Support\ModuleGate;
use App\Modules\ShopCart\Models\Cart;
use App\Modules\Core\Models\User;
use Illuminate\Support\Facades\Session;

class MergeCartOnAuthAction
{
    public function __construct(
        private RecalculateCartTotalAction $recalculate,
    ) {}

    public function handle(User $user): void
    {
        if(!ModuleGate::isEnabled('shop_cart')){
            return;
        }

        $uuid = Session::get('cart_uuid');

        if (!$uuid) {
            return;
        }

        $sessionCart = Cart::where('uuid', $uuid)->first();

        if (!$sessionCart || $sessionCart->user_id !== null) {
            return;
        }

        $userCart = $user->cart;

        if (!$userCart) {
            $sessionCart->update(['user_id' => $user->id]);

            return;
        }

        foreach ($sessionCart->items as $item) {
            $userCart->items()->updateOrCreate(
                ['shop_product_id' => $item->shop_product_id],
                [
                    'product_name' => $item->product_name,
                    'product_article' => $item->product_article,
                    'selling_price' => $item->selling_price,
                    'quantity' => $item->quantity,
                    'total' => $item->total,
                ],
            );
        }

        $this->recalculate->handle($userCart);

        $sessionCart->delete();
    }
}
