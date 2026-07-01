<?php

namespace App\Modules\Basket\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Modules\Basket\Actions\FindBasketAction;
use App\Modules\Basket\Models\Basket;
use App\Modules\Core\Interfaces\ModuleIndexControllerInterface;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class IndexController extends Controller implements ModuleIndexControllerInterface
{
    public function __construct(
        private readonly FindBasketAction $findBasket,
    ) {}

    public function accessCheck(): RedirectResponse|Response|null
    {
        return null;
    }

    public function checkout(Request $request)
    {
    }

    public function index(Request $request, array $segments = [])
    {
        if (isset($segments[0]) && $segments[0] === 'checkout') {
            $basket = $this->findBasket->handle(Auth::user());
            $basket?->load('items.product');

            return view('pages.basket.checkout', compact('basket'));
        }

        if (isset($segments[0]) && Str::isUuid($segments[0])) {
            $basket = Basket::where('uuid', $segments[0])->firstOrFail();
            $basket->load('items.product');

            if ($basket->items->isEmpty()) {
                return view('pages.basket.empty_basket');
            }

            return view('pages.basket.index', compact('basket'));
        }

        $basket = $this->findBasket->handle(Auth::user());
        $basket?->load('items.product');

        if ($basket && $basket->items->isNotEmpty()) {
            return redirect(module_url('basket', [$basket->uuid]));
        }

        return view('pages.basket.empty_basket');
    }
}
