<?php

namespace App\Modules\ShopCart\Actions;

use App\Modules\ShopCart\Models\Cart;
use App\Modules\User\Models\User;

/**
 * Returns the cart for the user or guest, creating one if there is none.
 *
 * Storing the resulting uuid back into the session is the caller's job —
 * `CartContext::currentOrCreate()` does it.
 */
class GetOrCreateCartAction
{
    public function handle(?User $user, ?string $guestCartUuid = null): Cart
    {
        if ($user) {
            return $user->cart
                ?? $this->findGuestCart($guestCartUuid, $user)
                ?? Cart::create(['user_id' => $user->id]);
        }

        return $this->findGuestCart($guestCartUuid, null) ?? Cart::create();
    }

    /**
     * A cart started before signing in: it is only picked up when it is still
     * unclaimed, or already belongs to this very user.
     */
    private function findGuestCart(?string $uuid, ?User $user): ?Cart
    {
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
