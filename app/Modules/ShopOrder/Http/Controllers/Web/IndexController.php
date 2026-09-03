<?php

namespace App\Modules\ShopOrder\Http\Controllers\Web;

use App\Modules\Core\Abstract\BaseWebController;
use App\Modules\Page\Services\PageContextSharer;
use App\Modules\ShopOrder\Queries\FindOrderByUuidQuery;
use App\Modules\ShopOrder\ViewModels\OrderViewModel;
use Illuminate\View\View;

class IndexController extends BaseWebController
{
    public function __construct(
        private readonly FindOrderByUuidQuery $findOrderByUuid,
        private readonly PageContextSharer $pageContextSharer,
    ) {}

    public function show(string $uuid): View
    {
        $order = OrderViewModel::from($this->findOrderByUuid->handle($uuid));

        $this->pageContextSharer->shareWithoutPage();

        return $this->render('modules.shop_order.index', compact('order'));
    }

    public function success(string $uuid): View
    {
        $order = OrderViewModel::from($this->findOrderByUuid->handle($uuid));

        $this->pageContextSharer->shareWithoutPage();

        return $this->render('modules.shop_order.success', compact('order'));
    }

    public function error(string $uuid): View
    {
        $order = OrderViewModel::from($this->findOrderByUuid->handle($uuid));

        $this->pageContextSharer->shareWithoutPage();

        return $this->render('modules.shop_order.error', compact('order'));
    }
}
