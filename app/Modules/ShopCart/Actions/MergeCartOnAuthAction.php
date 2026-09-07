<?php

namespace App\Modules\ShopCart\Actions;

use App\Modules\Core\Support\ModuleGate;
use App\Modules\ShopCart\Models\Cart;
use App\Modules\User\Models\User;

/**
 * Folds the cart a guest started into the cart of the user who just signed in.
 *
 * The guest cart's uuid comes from the caller (`CartContext::uuid()`), so the
 * action itself never touches the session and stays callable outside a request.
 */
class MergeCartOnAuthAction
{
    public function __construct(
        private RecalculateCartTotalAction $recalculate,
    ) {}

    public function handle(User $user, ?string $guestCartUuid): void
    {
        if (!ModuleGate::isEnabled('shop_cart')) {
            return;
        }

        if (!$guestCartUuid) {
            return;
        }

        $guestCart = Cart::where('uuid', $guestCartUuid)->first();

        if (!$guestCart || $guestCart->user_id !== null) {
            return;
        }

        $userCart = $user->cart;

        if (!$userCart) {
            $guestCart->update(['user_id' => $user->id]);

            return;
        }

        foreach ($guestCart->items as $item) {
            $userCart->items()->updateOrCreate(
                ['shop_product_id' => $item->shop_product_id],
                [
                    'product_name'    => $item->product_name,
                    'product_article' => $item->product_article,
                    'selling_price'   => $item->selling_price,
                    'quantity'        => $item->quantity,
                    'total'           => $item->total,
                ],
            );
        }

        $this->recalculate->handle($userCart);

        $guestCart->delete();
    }
}
