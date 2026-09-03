<?php

namespace App\Modules\ShopCart\Http\Controllers\Web;

use App\Modules\Core\Abstract\BaseWebController;
use App\Modules\Core\Dtos\ActionResult;
use App\Modules\ShopCart\Actions\ApplyPromocodeToCartAction;
use App\Modules\ShopCart\Actions\RemovePromocodeFromCartAction;
use App\Modules\ShopCart\Http\Requests\ApplyPromocodeRequest;
use App\Modules\ShopCart\Http\Requests\RemovePromocodeRequest;
use App\Modules\ShopCart\Models\Cart;
use App\Modules\ShopCart\ViewModels\CartViewModel;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;

class PromocodeController extends BaseWebController
{
    public function __construct(
        private readonly ApplyPromocodeToCartAction $applyPromocode,
        private readonly RemovePromocodeFromCartAction $removePromocode,
    ) {}

    public function apply(ApplyPromocodeRequest $request): JsonResponse|RedirectResponse
    {
        $cart = Cart::where('uuid', $request->validated('cart_uuid'))->firstOrFail();

        $result = $this->applyPromocode->handle($cart, $request->validated('code'));

        $cart->refresh();

        if ($request->expectsJson()) {
            return $this->summaryResponse($cart, $result);
        }

        return back()->with($result->success ? 'success' : 'error', $result->message);
    }

    public function remove(RemovePromocodeRequest $request): JsonResponse|RedirectResponse
    {
        $cart = Cart::where('uuid', $request->validated('cart_uuid'))->firstOrFail();

        $this->removePromocode->handle($cart);

        $cart->refresh();

        if ($request->expectsJson()) {
            return $this->summaryResponse($cart, null);
        }

        return back()->with('success', t('shop.promocode_removed'));
    }

    /**
     * Re-renders the promocode form, the discount rows and the total, in the
     * action format understood by public/assets/js/abc-response.js.
     */
    private function summaryResponse(Cart $cart, ?ActionResult $result): JsonResponse
    {
        $cartView = CartViewModel::from($cart->load('items.product'));

        return response()->json([
            'success' => true,
            'actions' => [
                [
                    'method' => 'replaceWith',
                    'selector' => '.js_promocode_block',
                    'content' => $this->render('modules.shop_cart.partials.promocode', [
                        'cart' => $cartView,
                        'result' => $result,
                    ])->render(),
                ],
                [
                    'method' => 'replaceWith',
                    'selector' => '.js_summary_discount',
                    'content' => $this->render('modules.shop_cart.partials.summary_discount', ['cart' => $cartView])->render(),
                ],
                [
                    'method' => 'text',
                    'selector' => '.js_checkout_total',
                    'content' => $cartView->total_amount_formatted,
                ],
            ],
        ]);
    }
}
