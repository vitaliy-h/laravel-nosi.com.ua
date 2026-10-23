<?php

namespace App\Modules\Shop\ViewModels;

use App\Modules\Core\Abstract\BaseViewModel;

class ShopProductPageViewModel extends BaseViewModel
{
    public function __construct(
        public readonly ProductViewModel $product,
        public readonly CategoryViewModel $category,
        public readonly int $cart_quantity,
    ) {}
}
