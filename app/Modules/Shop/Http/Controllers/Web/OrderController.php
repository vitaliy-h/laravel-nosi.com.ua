<?php

namespace App\Modules\Shop\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Modules\Core\Services\MenuService;
use App\Modules\Shop\Repository\OrderRepository;
use Illuminate\View\View;

class OrderController extends Controller
{
    public function __construct(
        private readonly OrderRepository $repository,
        private readonly MenuService     $menu,
    )
    {
    }

    public function show(string $uuid): View
    {
        $order = $this->repository->findByUuid($uuid);

        $this->shareLayoutVars();

        return view('pages.shop.order.index', compact('order'));
    }

    public function success(string $uuid): View
    {
        $order = $this->repository->findByUuid($uuid);

        $this->shareLayoutVars();

        return view('pages.shop.order.success', compact('order'));
    }

    public function error(string $uuid): View
    {
        $order = $this->repository->findByUuid($uuid);

        $this->shareLayoutVars();

        return view('pages.shop.order.error', compact('order'));
    }

    private function shareLayoutVars(): void
    {
        view()->share([
            'page'        => null,
            'breadcrumbs' => collect(),
            'headerMenu'  => $this->menu->getHeaderMenu(),
            'footerMenu'  => $this->menu->getFooterMenu(),
        ]);
    }
}
