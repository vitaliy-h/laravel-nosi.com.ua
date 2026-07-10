<?php

namespace App\Modules\Orders\Http\Controllers\Web;

use App\Modules\Basket\Models\Basket;
use App\Modules\Core\Abstract\BaseWebController;
use App\Modules\Orders\Actions\PlaceOrderFromBasketAction;
use App\Modules\Orders\Dtos\CustomerData;
use App\Modules\Orders\Http\Requests\PlaceOrderRequest;
use Illuminate\Http\RedirectResponse;

class OrderController extends BaseWebController
{
    public function __construct(
        private readonly PlaceOrderFromBasketAction $placeOrder,
    ) {}

    public function create(PlaceOrderRequest $request): RedirectResponse
    {
        $basket = Basket::where('uuid', $request->validated('basket_uuid'))->firstOrFail();

        $order = $this->placeOrder->handle($basket, CustomerData::from($request->validated()));

        return redirect()->route('order.show', $order->uuid);
    }
}
