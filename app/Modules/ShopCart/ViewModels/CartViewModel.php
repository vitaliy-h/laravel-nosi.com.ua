<?php

namespace App\Modules\ShopCart\ViewModels;

use App\Modules\Core\Abstract\BaseViewModel;
use App\Modules\Core\Support\Money;
use App\Modules\ShopCart\Models\Cart;
use App\Modules\ShopCart\Models\CartProduct;
use Illuminate\Support\Collection;

class CartViewModel extends BaseViewModel
{
    /**
     * @param  Collection<int, CartItemViewModel>  $items
     */
    public function __construct(
        public readonly string $uuid,
        public readonly Collection $items,
        public readonly bool $is_empty,
        public readonly ?string $promocode_code,
        public readonly bool $has_discount,
        public readonly string $subtotal_amount_formatted,
        public readonly string $discount_amount_formatted,
        public readonly string $total_amount_formatted,
    ) {}

    /**
     * Needs `items.product` eager-loaded — the lines carry the pictures.
     */
    public static function fromCart(Cart $cart): self
    {
        $items = $cart->items->map(fn (CartProduct $item) => CartItemViewModel::from($item))->values();

        return new self(
            uuid: (string) $cart->uuid,
            items: $items,
            is_empty: $items->isEmpty(),
            promocode_code: $cart->promocode_code,
            has_discount: Money::from($cart->discount_amount)->isPositive(),
            subtotal_amount_formatted: Money::from($cart->subtotal_amount)->format(),
            discount_amount_formatted: Money::from($cart->discount_amount)->format(),
            total_amount_formatted: Money::from($cart->total_amount)->format(),
        );
    }
}
