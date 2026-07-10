<?php

namespace App\Modules\ShopBasket\Actions;

use App\Modules\ShopBasket\Models\Basket;
use App\Modules\Core\Models\User;
use Illuminate\Support\Facades\Session;

class MergeBasketOnAuthAction
{
    public function __construct(
        private RecalculateBasketTotalAction $recalculate,
    ) {}

    public function handle(User $user): void
    {
        $uuid = Session::get('basket_uuid');

        if (!$uuid) {
            return;
        }

        $sessionBasket = Basket::where('uuid', $uuid)->first();

        if (!$sessionBasket || $sessionBasket->user_id !== null) {
            return;
        }

        $userBasket = $user->basket;

        if (!$userBasket) {
            $sessionBasket->update(['user_id' => $user->id]);

            return;
        }

        foreach ($sessionBasket->items as $item) {
            $userBasket->items()->updateOrCreate(
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

        $this->recalculate->handle($userBasket);

        $sessionBasket->delete();
    }
}
