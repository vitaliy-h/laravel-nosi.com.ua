<?php

namespace App\Modules\ShopCart\Actions;

use App\Modules\ShopCart\Dtos\PromocodeResultData;
use App\Modules\ShopCart\Queries\FindPromocodeByCodeQuery;

readonly class ValidatePromocodeAction
{
    public function __construct(
        private FindPromocodeByCodeQuery $findPromocodeByCode,
        private CalculateDiscountAction $calculateDiscount,
    ) {}

    public function handle(string $code, float $subtotal): PromocodeResultData
    {
        // The active() scope already covers display, date window and usage limit.
        $promocode = $this->findPromocodeByCode->handle($code);

        if ($promocode === null) {
            return PromocodeResultData::fail(t('shop.promocode_invalid'));
        }

        if ($promocode->min_order_amount !== null && $subtotal < (float) $promocode->min_order_amount) {
            return PromocodeResultData::fail(t('shop.promocode_min_order', [
                'amount' => t('shop.currency').number_format((float) $promocode->min_order_amount, 2),
            ]));
        }

        $discount = $this->calculateDiscount->handle($promocode, $subtotal);

        if ($discount <= 0) {
            return PromocodeResultData::fail(t('shop.promocode_invalid'));
        }

        return PromocodeResultData::ok(
            promocodeId: $promocode->id,
            code: $promocode->code,
            discount: $discount,
            message: t('shop.promocode_applied'),
        );
    }
}
