<?php

namespace App\Modules\ShopCart\Actions;

use App\Modules\ShopCart\Models\Cart;
use App\Modules\Core\Models\User;
use Illuminate\Support\Facades\Session;

class FindCartAction
{
    public function handle(?User $user): ?Cart
    {
        if ($user) {
            return $user->cart;
        }

        $uuid = Session::get('cart_uuid');

        if (!$uuid) {
            return null;
        }

        return Cart::whereNull('user_id')->where('uuid', $uuid)->first();
    }
}
