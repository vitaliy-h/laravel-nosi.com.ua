<?php

namespace App\Modules\ShopCart\Http\Controllers\Web;

use App\Modules\ShopCart\Actions\FindCartAction;
use App\Modules\ShopCart\Models\Cart;
use App\Modules\Core\Abstract\BaseWebController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class IndexController extends BaseWebController
{
    public function __construct(
        private readonly FindCartAction $findCart,
    ) {}

    public function index(Request $request)
    {
        $cart = $this->findCart->handle(Auth::user());
        $cart?->load('items.product');

        if ($cart && $cart->items->isNotEmpty()) {
            return redirect(save_route('cart.show', $cart->uuid));
        }

        return view('pages.cart.empty_cart');
    }

    public function checkout(Request $request)
    {
        $cart = $this->findCart->handle(Auth::user());
        $cart?->load('items.product');

        return view('pages.cart.checkout', compact('cart'));
    }

    public function show(Request $request, string $uuid)
    {
        if (!Str::isUuid($uuid)) {
            return $this->index($request);
        }

        $cart = Cart::where('uuid', $uuid)->firstOrFail();
        $cart->load('items.product');

        if ($cart->items->isEmpty()) {
            return view('pages.cart.empty_cart');
        }

        return view('pages.cart.index', compact('cart'));
    }
}
