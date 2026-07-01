<?php

namespace App\Modules\Basket\Actions;

use App\Modules\Basket\Models\Basket;

class RecalculateBasketTotalAction
{
    public function handle(Basket $basket): void
    {
        $total = $basket->items()->sum('total');

        $basket->withoutEvents(fn() => $basket->update(['total_amount' => $total]));
    }
}
