<?php

namespace App\Modules\Basket\Http\Controllers\Web;

use App\Modules\Basket\Actions\FindBasketAction;
use App\Modules\Basket\Models\Basket;
use App\Modules\Core\Abstract\BaseWebController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class IndexController extends BaseWebController
{
    public function __construct(
        private readonly FindBasketAction $findBasket,
    ) {}

    public function index(Request $request)
    {
        $basket = $this->findBasket->handle(Auth::user());
        $basket?->load('items.product');

        if ($basket && $basket->items->isNotEmpty()) {
            return redirect(save_route('basket.show', $basket->uuid));
        }

        return view('pages.basket.empty_basket');
    }

    public function checkout(Request $request)
    {
        $basket = $this->findBasket->handle(Auth::user());
        $basket?->load('items.product');

        return view('pages.basket.checkout', compact('basket'));
    }

    public function show(Request $request, string $uuid)
    {
        if (!Str::isUuid($uuid)) {
            return $this->index($request);
        }

        $basket = Basket::where('uuid', $uuid)->firstOrFail();
        $basket->load('items.product');

        if ($basket->items->isEmpty()) {
            return view('pages.basket.empty_basket');
        }

        return view('pages.basket.index', compact('basket'));
    }
}
