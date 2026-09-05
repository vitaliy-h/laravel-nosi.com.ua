<?php

namespace App\Modules\ShopCart\Actions;

use App\Modules\Core\Dtos\ActionResult;
use App\Modules\ShopCart\Dtos\PromocodeData;
use App\Modules\ShopCart\Queries\FindPromocodeByCodeQuery;

readonly class ValidatePromocodeAction
{
    public function __construct(
        private FindPromocodeByCodeQuery $findPromocodeByCode,
        private CalculateDiscountAction $calculateDiscount,
    ) {}

    /**
     * @return ActionResult<PromocodeData>
     */
    public function handle(string $code, float $subtotal): ActionResult
    {
        // The active() scope already covers display, date window and usage limit.
        $promocode = $this->findPromocodeByCode->handle($code);

        if ($promocode === null) {
            return ActionResult::fail(t('shop.promocode_invalid'));
        }

        if ($promocode->min_order_amount !== null && $subtotal < (float) $promocode->min_order_amount) {
            return ActionResult::fail(t('shop.promocode_min_order', [
                'amount' => t('shop.currency') . number_format((float) $promocode->min_order_amount, 2),
            ]));
        }

        $discount = $this->calculateDiscount->handle($promocode, $subtotal);

        if ($discount <= 0) {
            return ActionResult::fail(t('shop.promocode_invalid'));
        }

        return ActionResult::ok(
            new PromocodeData(
                promocode_id: $promocode->id,
                code: $promocode->code,
                discount: $discount,
            ),
            t('shop.promocode_applied'),
        );
    }
}
