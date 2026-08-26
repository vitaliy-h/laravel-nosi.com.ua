<?php

namespace App\Modules\Profile\Http\Controllers\Web;

use App\Modules\Core\Abstract\BaseWebController;
use App\Modules\ShopOrder\Enums\PaymentStatusEnum;
use App\Modules\Profile\Dtos\OrderFilterData;
use App\Modules\Profile\Queries\FindUserOrderQuery;
use App\Modules\Profile\Queries\GetUserOrdersQuery;
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
        $user = auth()->user()->load('socialProfiles');
        $activeSection = 'edit';

        return view('pages.profile.edit', compact('user', 'activeSection'));
    }

    public function orders(Request $request)
    {
        $user = auth()->user();
        $activeSection = 'orders';

        $filter = new OrderFilterData(
            search: $request->query('search') ?: null,
            status: $request->query('status') ? PaymentStatusEnum::tryFrom($request->query('status')) : null,
        );
        $orders = $this->getUserOrders->handle($user->id, $filter);

        return view('pages.profile.orders', compact('user', 'activeSection', 'orders', 'filter'));
    }

    public function order(Request $request, string $id)
    {
        $user = auth()->user();
        $activeSection = 'orders';
        $order = $this->findUserOrder->handle((int) $id, $user->id);

        return view('pages.profile.order', compact('user', 'activeSection', 'order'));
    }
}
