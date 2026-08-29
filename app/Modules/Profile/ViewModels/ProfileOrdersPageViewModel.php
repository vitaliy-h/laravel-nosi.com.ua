<?php

namespace App\Modules\Profile\ViewModels;

use App\Modules\Core\Abstract\BaseViewModel;
use App\Modules\Profile\Dtos\OrderFilterData;
use App\Modules\ShopOrder\ViewModels\OrderViewModel;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

/**
 * `filter` stays a Dto: the same object drives the orders query.
 */
class ProfileOrdersPageViewModel extends BaseViewModel
{
    /**
     * @param  LengthAwarePaginator<int, OrderViewModel>  $orders
     */
    public function __construct(
        public readonly ProfileUserViewModel $user,
        public readonly LengthAwarePaginator $orders,
        public readonly OrderFilterData $filter,
        public readonly string $active_section = 'orders',
    ) {}
}
