<?php

namespace App\Modules\ShopCart\Actions;

use App\Modules\ShopCart\Models\Promocode;

class IncrementPromocodeUsageAction
{
    /**
     * Called once, when the order is created — not when the code is applied
     * to a cart, which may never be checked out.
     */
    public function handle(Promocode $promocode): void
    {
        $promocode->increment('used_count');
    }
}
