<?php

namespace App\Modules\ShopOrder\Http\Controllers\Web;

use App\Modules\ShopCart\Models\Cart;
use App\Modules\Core\Abstract\BaseWebController;
use App\Modules\ShopOrder\Actions\PlaceOrderFromCartAction;
use App\Modules\ShopOrder\Dtos\CustomerData;
use App\Modules\ShopOrder\Http\Requests\PlaceOrderRequest;
use Illuminate\Http\RedirectResponse;

class OrderController extends BaseWebController
{
    public function __construct(
        private readonly PlaceOrderFromCartAction $placeOrder,
    ) {}

    public function create(PlaceOrderRequest $request): RedirectResponse
    {
        $cart = Cart::where('uuid', $request->validated('cart_uuid'))->firstOrFail();

        $order = $this->placeOrder->handle($cart, CustomerData::from($request->validated()));

        return redirect()->route('order.show', $order->uuid);
    }
}
