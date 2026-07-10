<?php

namespace App\Modules\ShopBasket\Actions;

use App\Modules\ShopBasket\Models\Basket;
use App\Modules\Core\Models\User;
use Illuminate\Support\Facades\Session;

class GetOrCreateBasketAction
{
    public function handle(?User $user): Basket
    {
        if ($user) {
            $basket = $user->basket;

            if (!$basket) {
                $basket = $this->findSessionBasket($user);
            }

            if (!$basket) {
                $basket = Basket::create(['user_id' => $user->id]);
            }

            Session::put('basket_uuid', $basket->uuid);

            return $basket;
        }

        $basket = $this->findSessionBasket(null);

        if (!$basket) {
            $basket = Basket::create();
        }

        Session::put('basket_uuid', $basket->uuid);

        return $basket;
    }

    private function findSessionBasket(?User $user): ?Basket
    {
        $uuid = Session::get('basket_uuid');

        if (!$uuid) {
            return null;
        }

        $basket = Basket::where('uuid', $uuid)->first();

        if (!$basket) {
            return null;
        }

        if ($basket->user_id !== null && $basket->user_id !== $user?->id) {
            return null;
        }

        return $basket;
    }
}
