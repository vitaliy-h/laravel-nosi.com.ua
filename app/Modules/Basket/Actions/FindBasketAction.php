<?php

namespace App\Modules\Basket\Actions;

use App\Modules\Basket\Models\Basket;
use App\Modules\Core\Models\User;
use Illuminate\Support\Facades\Session;

class FindBasketAction
{
    public function handle(?User $user): ?Basket
    {
        if ($user) {
            return $user->basket;
        }

        $uuid = Session::get('basket_uuid');

        if (!$uuid) {
            return null;
        }

        return Basket::whereNull('user_id')->where('uuid', $uuid)->first();
    }
}
