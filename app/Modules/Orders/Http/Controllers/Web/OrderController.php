<?php

namespace App\Modules\Orders\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Modules\Basket\Models\Basket;
use App\Modules\Basket\Services\BasketService;
use App\Modules\Core\Services\MenuService;
use App\Modules\Orders\Actions\PlaceOrderFromBasketAction;
use App\Modules\Orders\Dtos\CustomerData;
use App\Modules\Orders\Http\Requests\PlaceOrderRequest;
use App\Modules\Orders\Queries\FindOrderByUuidQuery;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class OrderController extends Controller
{
    public function __construct(
        private readonly FindOrderByUuidQuery $findOrderByUuid,
        private readonly MenuService $menu,
        private readonly PlaceOrderFromBasketAction $placeOrder,
        private readonly BasketService $basket,
    ) {}

    public function placeOrder(PlaceOrderRequest $request): RedirectResponse
    {
        $basket = Basket::where('uuid', $request->validated('basket_uuid'))->firstOrFail();

        $order = $this->placeOrder->handle($basket, CustomerData::from($request->validated()));

        return redirect()->route('order.show', $order->uuid);
    }

    public function show(string $uuid): View
    {
        $order = $this->findOrderByUuid->handle($uuid);

        $this->shareLayoutVars();

        return view('pages.shop.order.index', compact('order'));
    }

    public function success(string $uuid): View
    {
        $order = $this->findOrderByUuid->handle($uuid);

        $this->shareLayoutVars();

        return view('pages.shop.order.success', compact('order'));
    }

    public function error(string $uuid): View
    {
        $order = $this->findOrderByUuid->handle($uuid);

        $this->shareLayoutVars();

        return view('pages.shop.order.error', compact('order'));
    }

    private function shareLayoutVars(): void
    {
        view()->share([
            'page' => null,
            'breadcrumbs' => collect(),
            'headerMenu' => $this->menu->getHeaderMenu(),
            'footerMenu' => $this->menu->getFooterMenu(),
            'basketCount' => $this->basket->getCount(),
        ]);
    }
}
