<?php

namespace App\Modules\ShopCart\Queries;

use App\Modules\ShopCart\Models\Cart;
use App\Modules\User\Models\User;
use Illuminate\Support\Facades\Session;

/**
 * Finds an existing cart for the user or guest — never creates one.
 * Creating is GetOrCreateCartAction's job, which is why that one stays an Action.
 */
class FindCartQuery
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
