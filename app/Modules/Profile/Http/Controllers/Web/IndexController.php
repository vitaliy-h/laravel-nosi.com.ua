<?php

namespace App\Modules\Profile\Http\Controllers\Web;

use App\Modules\Core\Abstract\BaseWebController;
use App\Modules\Profile\Dtos\OrderFilterData;
use App\Modules\Profile\Queries\FindUserOrderQuery;
use App\Modules\Profile\Queries\GetUserOrdersQuery;
use App\Modules\Profile\ViewModels\ProfileEditPageViewModel;
use App\Modules\Profile\ViewModels\ProfileOrderPageViewModel;
use App\Modules\Profile\ViewModels\ProfileOrdersPageViewModel;
use App\Modules\Profile\ViewModels\ProfileUserViewModel;
use App\Modules\ShopOrder\Enums\PaymentStatusEnum;
use App\Modules\ShopOrder\ViewModels\OrderViewModel;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class IndexController extends BaseWebController
{
    public function __construct(
        private readonly GetUserOrdersQuery $getUserOrders,
        private readonly FindUserOrderQuery $findUserOrder,
    ) {}

    public function index(Request $request): RedirectResponse
    {
        return redirect()->to(save_route('profile.edit'));
    }

    public function edit(Request $request)
    {
        $page = new ProfileEditPageViewModel(
            user: ProfileUserViewModel::from(auth()->user()->load('socialProfiles')),
        );

        return $this->render('modules.profile.edit', $page);
    }

    public function orders(Request $request)
    {
        $user = auth()->user();

        $filter = new OrderFilterData(
            search: $request->query('search') ?: null,
            status: $request->query('status') ? PaymentStatusEnum::tryFrom($request->query('status')) : null,
        );

        $page = new ProfileOrdersPageViewModel(
            user: ProfileUserViewModel::from($user),
            orders: OrderViewModel::collect($this->getUserOrders->handle($user->id, $filter)),
            filter: $filter,
        );

        return $this->render('modules.profile.orders', $page);
    }

    public function order(Request $request, string $id)
    {
        $user = auth()->user();

        $page = new ProfileOrderPageViewModel(
            user: ProfileUserViewModel::from($user),
            order: OrderViewModel::from($this->findUserOrder->handle((int) $id, $user->id)),
        );

        return $this->render('modules.profile.order', $page);
    }
}
