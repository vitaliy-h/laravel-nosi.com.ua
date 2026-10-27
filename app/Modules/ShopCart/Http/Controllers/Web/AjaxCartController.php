<?php

namespace App\Modules\ShopCart\Http\Controllers\Web;

use App\Modules\Core\Abstract\BaseWebController;
use App\Modules\Core\Support\Money;
use App\Modules\Shop\Models\Product;
use App\Modules\ShopCart\Actions\AddOrUpdateCartItemAction;
use App\Modules\ShopCart\Actions\DuplicateCartAction;
use App\Modules\ShopCart\Actions\RemoveCartItemAction;
use App\Modules\ShopCart\Enums\CartAction;
use App\Modules\ShopCart\Http\Requests\ChangeCartRequest;
use App\Modules\ShopCart\Models\Cart;
use App\Modules\ShopCart\Services\CartContext;
use App\Modules\ShopCart\ViewModels\CartItemViewModel;
use App\Modules\ShopCart\ViewModels\CartViewModel;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class AjaxCartController extends BaseWebController
{
    public function __construct(
        private readonly CartContext $cart,
    ) {}

    public function change(
        ChangeCartRequest $request,
        AddOrUpdateCartItemAction $addOrUpdate,
        RemoveCartItemAction $remove,
        DuplicateCartAction $fork,
    ) {
        $productId = (int) $request->input('id');
        $pack = max(1, (int) $request->input('pack', 1));
        $count = max(1, (int) $request->input('count'));
        $action = CartAction::from($request->input('action'));
        $viewedUuid = $request->input('cart_uuid');

        $product = Product::query()->findOrFail($productId);

        // A line can only be added or changed in a set the product is sold in;
        // removing one of a set dropped since is still allowed.
        if (!in_array($action, [CartAction::DeleteModal, CartAction::DeleteCheckout], true) && $product->pack($pack) === null) {
            throw ValidationException::withMessages(['pack' => __('validation.in', ['attribute' => 'pack'])]);
        }

        $forked = false;

        if ($viewedUuid) {
            $viewedCart = Cart::where('uuid', $viewedUuid)->firstOrFail();

            if ($this->isOwnCart($viewedCart)) {
                $cart = $viewedCart;
            } else {
                $cart = $this->cart->remember(
                    $fork->handle($viewedCart, Auth::user(), $this->cart->uuid())
                );
                $forked = true;
            }
        } else {
            $cart = $this->cart->currentOrCreate();
        }

        $actions = match ($action) {
            CartAction::DeleteModal         => $this->handleDeleteModal($cart, $product, $pack, $remove),
            CartAction::DeleteCheckout      => $this->handleDeleteCheckout($cart, $product, $pack, $remove),
            CartAction::ChangeCount         => $this->handleChangeCount($cart, $product, $pack, $count, $addOrUpdate),
            CartAction::ChangeCountModal    => $this->handleChangeCountModal($cart, $product, $pack, $count, $addOrUpdate),
            CartAction::ChangeCountCheckout => $this->handleChangeCountCheckout($cart, $product, $pack, $count, $addOrUpdate),
        };

        if ($forked) {
            return response()->json([
                'success' => true,
                'actions' => [
                    ['method' => 'location', 'content' => save_route('cart.show', $cart->uuid)],
                ],
            ]);
        }

        return response()->json([
            'success' => true,
            'actions' => $actions,
        ]);
    }

    private function handleDeleteModal(Cart $cart, Product $product, int $pack, RemoveCartItemAction $remove): array
    {
        $remove->handle($cart, $product, $pack);

        return [
            $this->cartCountAction($cart),
            [
                'selector' => '.js_cart_modal_content',
                'method'   => 'html',
                'content'  => '',
            ],
        ];
    }

    private function handleDeleteCheckout(Cart $cart, Product $product, int $pack, RemoveCartItemAction $remove): array
    {
        $remove->handle($cart, $product, $pack);
        $cart->refresh();

        if ($cart->items()->count() === 0) {
            return [
                ['method' => 'location', 'content' => save_route('cart.show', $cart->uuid)],
            ];
        }

        return [
            $this->cartCountAction($cart),
            [
                'selector' => '.checkout_item[data-line="' . CartItemViewModel::lineKey($product->id, $pack) . '"]',
                'method'   => 'remove',
            ],
            $this->summaryDiscountAction($cart),
            $this->checkoutTotalAction($cart),
        ];
    }

    /**
     * The product page and the card add to what is already in the cart; the
     * cart modal and the cart page set the count.
     */
    private function handleChangeCount(Cart $cart, Product $product, int $pack, int $count, AddOrUpdateCartItemAction $addOrUpdate): array
    {
        $item = $addOrUpdate->add($cart, $product, $count, $pack);
        $cart->refresh();

        return [
            $this->cartCountAction($cart),
            [
                'selector' => '.js_cart_modal_content',
                'method'   => 'html',
                'content'  => $this->render('modules.shop_cart.partials.cart_modal_item', [
                    'item' => CartItemViewModel::from($item->load('product')),
                ])->render(),
            ],
            [
                'selector' => '#cartModal',
                'method'   => 'modal',
            ],
        ];
    }

    private function handleChangeCountModal(Cart $cart, Product $product, int $pack, int $count, AddOrUpdateCartItemAction $addOrUpdate): array
    {
        $item = $addOrUpdate->handle($cart, $product, $count, $pack);

        return [
            $this->cartCountAction($cart->refresh()),
            [
                'selector' => '.js_product_total',
                'method'   => 'text',
                'content'  => Money::from($item->total)->format(),
            ],
        ];
    }

    private function handleChangeCountCheckout(Cart $cart, Product $product, int $pack, int $count, AddOrUpdateCartItemAction $addOrUpdate): array
    {
        $item = $addOrUpdate->handle($cart, $product, $count, $pack);
        $cart->refresh();

        return [
            $this->cartCountAction($cart),
            [
                'selector' => '.js_product_checkout_total_' . CartItemViewModel::lineKey($product->id, $pack),
                'method'   => 'text',
                'content'  => Money::from($item->total)->format(),
            ],
            $this->summaryDiscountAction($cart),
            $this->checkoutTotalAction($cart),
        ];
    }

    /**
     * The promocode discount depends on the cart contents, so any line change
     * re-renders the discount rows alongside the total.
     */
    private function summaryDiscountAction(Cart $cart): array
    {
        return [
            'selector' => '.js_summary_discount',
            'method'   => 'replaceWith',
            'content'  => $this->render('modules.shop_cart.partials.summary_discount', [
                'cart' => CartViewModel::from($cart->load('items.product')),
            ])->render(),
        ];
    }

    private function checkoutTotalAction(Cart $cart): array
    {
        return [
            'selector' => '.js_checkout_total',
            'method'   => 'text',
            'content'  => Money::from($cart->total_amount)->format(),
        ];
    }

    /**
     * The badge is re-counted through CartContext rather than summed here, so the
     * count cached in the session stays in step with what the page is told to show.
     */
    private function cartCountAction(Cart $cart): array
    {
        return [
            'selector' => '.js_cart_count',
            'method'   => 'text',
            'content'  => $this->cart->refreshCount($cart),
        ];
    }

    private function isOwnCart(Cart $cart): bool
    {
        if ($user = Auth::user()) {
            return $cart->user_id === $user->id;
        }

        return $cart->user_id === null && $cart->uuid === $this->cart->uuid();
    }
}
