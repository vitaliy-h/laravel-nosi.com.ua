<?php

namespace App\Modules\ShopCart\Actions;

use App\Modules\ShopCart\Models\Cart;
use App\Modules\User\Models\User;
use Illuminate\Support\Facades\Session;

class GetOrCreateCartAction
{
    public function handle(?User $user): Cart
    {
        if ($user) {
            $cart = $user->cart;

            if (!$cart) {
                $cart = $this->findSessionCart($user);
            }

            if (!$cart) {
                $cart = Cart::create(['user_id' => $user->id]);
            }

            Session::put('cart_uuid', $cart->uuid);

            return $cart;
        }

        $cart = $this->findSessionCart(null);

        if (!$cart) {
            $cart = Cart::create();
        }

        Session::put('cart_uuid', $cart->uuid);

        return $cart;
    }

    private function findSessionCart(?User $user): ?Cart
    {
        $uuid = Session::get('cart_uuid');

        if (!$uuid) {
            return null;
        }

        $cart = Cart::where('uuid', $uuid)->first();

        if (!$cart) {
            return null;
        }

        if ($cart->user_id !== null && $cart->user_id !== $user?->id) {
            return null;
        }

        return $cart;
    }
}
