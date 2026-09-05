<?php

namespace App\Modules\ShopCart\Actions;

use App\Modules\ShopCart\Enums\PromocodeTypeEnum;
use App\Modules\ShopCart\Models\Promocode;

class CalculateDiscountAction
{
    /**
     * Pure arithmetic — the only place a promocode turns into money.
     * The result never exceeds the subtotal and is never negative.
     */
    public function handle(Promocode $promocode, float $subtotal): float
    {
        $subtotal = max(0.0, $subtotal);

        $discount = match ($promocode->type) {
            PromocodeTypeEnum::PERCENT => round($subtotal * (float) $promocode->value / 100, 2),
            PromocodeTypeEnum::FIXED   => (float) $promocode->value,
        };

        if ($promocode->type === PromocodeTypeEnum::PERCENT && $promocode->max_discount !== null) {
            $discount = min($discount, (float) $promocode->max_discount);
        }

        return round(max(0.0, min($discount, $subtotal)), 2);
    }
}
