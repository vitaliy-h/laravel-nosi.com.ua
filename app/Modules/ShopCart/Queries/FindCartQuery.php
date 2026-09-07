<?php

namespace App\Modules\ShopCart\Queries;

use App\Modules\ShopCart\Models\Cart;
use App\Modules\User\Models\User;

/**
 * Finds an existing cart for the user or guest — never creates one.
 * Creating is GetOrCreateCartAction's job, which is why that one stays an Action.
 *
 * The guest cart's uuid comes in as an argument, not off the session:
 * `CartContext` is the only class that reads it. Same for $user.
 */
class FindCartQuery
{
    public function handle(?User $user, ?string $guestCartUuid = null): ?Cart
    {
        if ($user) {
            return $user->cart;
        }

        if (!$guestCartUuid) {
            return null;
        }

        return Cart::whereNull('user_id')->where('uuid', $guestCartUuid)->first();
    }
}
