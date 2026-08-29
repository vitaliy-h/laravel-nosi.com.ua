<?php

namespace App\Modules\ShopOrder\Http\Controllers\Web;

use App\Modules\Core\Abstract\BaseWebController;
use App\Modules\Page\Services\MenuService;
use App\Modules\ShopCart\Services\CartService;
use App\Modules\ShopOrder\Queries\FindOrderByUuidQuery;
use Illuminate\View\View;

class IndexController extends BaseWebController
{
    public function __construct(
        private readonly FindOrderByUuidQuery $findOrderByUuid,
        private readonly MenuService $menu,
        private readonly CartService $cart,
    ) {}

    public function show(string $uuid): View
    {
        $order = $this->findOrderByUuid->handle($uuid);

        $this->shareLayoutVars();

        return $this->render('pages.shop.order.index', compact('order'));
    }

    public function success(string $uuid): View
    {
        $order = $this->findOrderByUuid->handle($uuid);

        $this->shareLayoutVars();

        return $this->render('pages.shop.order.success', compact('order'));
    }

    public function error(string $uuid): View
    {
        $order = $this->findOrderByUuid->handle($uuid);

        $this->shareLayoutVars();

        return $this->render('pages.shop.order.error', compact('order'));
    }

    private function shareLayoutVars(): void
    {
        view()->share([
            'page' => null,
            'breadcrumbs' => collect(),
            'headerMenu' => $this->menu->getHeaderMenu(),
            'footerMenu' => $this->menu->getFooterMenu(),
            'cartCount' => $this->cart->getCount(),
        ]);
    }
}
