<?php

namespace App\Modules\ShopOrders\Http\Controllers\Web;

use App\Modules\ShopBasket\Services\BasketService;
use App\Modules\Core\Abstract\BaseWebController;
use App\Modules\Core\Services\MenuService;
use App\Modules\ShopOrders\Queries\FindOrderByUuidQuery;
use Illuminate\View\View;

class IndexController extends BaseWebController
{
    public function __construct(
        private readonly FindOrderByUuidQuery $findOrderByUuid,
        private readonly MenuService $menu,
        private readonly BasketService $basket,
    ) {}

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
