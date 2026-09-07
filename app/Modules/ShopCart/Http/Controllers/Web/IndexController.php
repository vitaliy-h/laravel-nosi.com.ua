<?php

namespace App\Modules\ShopCart\Http\Controllers\Web;

use App\Modules\Core\Abstract\BaseWebController;
use App\Modules\ShopCart\Models\Cart;
use App\Modules\ShopCart\Services\CartContext;
use App\Modules\ShopCart\ViewModels\CartViewModel;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class IndexController extends BaseWebController
{
    public function __construct(
        private readonly CartContext $cart,
    ) {}

    public function index(Request $request)
    {
        $cart = $this->cart->current();
        $cart?->load('items.product');

        if ($cart && $cart->items->isNotEmpty()) {
            return redirect(save_route('cart.show', $cart->uuid));
        }

        return $this->render('modules.shop_cart.empty_cart');
    }

    /**
     * The checkout template renders no cart data of its own yet — the totals
     * live on the cart page.
     */
    public function checkout(Request $request)
    {
        return $this->render('modules.shop_cart.checkout');
    }

    public function show(Request $request, string $uuid)
    {
        if (!Str::isUuid($uuid)) {
            return $this->index($request);
        }

        $cart = Cart::where('uuid', $uuid)->firstOrFail();
        $cart->load('items.product');

        if ($cart->items->isEmpty()) {
            return $this->render('modules.shop_cart.empty_cart');
        }

        return $this->render('modules.shop_cart.index', ['cart' => CartViewModel::from($cart)]);
    }
}
