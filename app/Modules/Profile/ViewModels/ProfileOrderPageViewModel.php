<?php

namespace App\Modules\Profile\ViewModels;

use App\Modules\Core\Abstract\BaseViewModel;
use App\Modules\ShopOrder\ViewModels\OrderViewModel;

class ProfileOrderPageViewModel extends BaseViewModel
{
    public function __construct(
        public readonly ProfileUserViewModel $user,
        public readonly OrderViewModel $order,
        public readonly string $active_section = 'orders',
    ) {}
}
