<?php

namespace App\Modules\Basket\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Modules\Basket\Actions\AddOrUpdateBasketItemAction;
use App\Modules\Basket\Actions\ForkBasketAction;
use App\Modules\Basket\Actions\GetOrCreateBasketAction;
use App\Modules\Basket\Actions\RemoveBasketItemAction;
use App\Modules\Basket\Enums\BasketAction;
use App\Modules\Basket\Http\Requests\ChangeBasketRequest;
use App\Modules\Basket\Models\Basket;
use App\Modules\Shop\Models\Product;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;

class AjaxBasketController extends Controller
{
    public function change(
        ChangeBasketRequest $request,
        GetOrCreateBasketAction $getOrCreateBasket,
        AddOrUpdateBasketItemAction $addOrUpdate,
        RemoveBasketItemAction $remove,
        ForkBasketAction $fork,
    ) {
        $productId = (int) $request->input('id');
        $count = max(1, (int) $request->input('count'));
        $action = BasketAction::from($request->input('action'));
        $viewedUuid = $request->input('basket_uuid');

        $product = Product::query()->findOrFail($productId);

        $forked = false;

        if ($viewedUuid) {
            $viewedBasket = Basket::where('uuid', $viewedUuid)->firstOrFail();

            if ($this->isOwnBasket($viewedBasket)) {
                $basket = $viewedBasket;
            } else {
                $basket = $fork->handle($viewedBasket, Auth::user());
                $forked = true;
            }
        } else {
            $basket = $getOrCreateBasket->handle(Auth::user());
        }

        $actions = match ($action) {
            BasketAction::DeleteModal => $this->handleDeleteModal($basket, $product, $remove),
            BasketAction::DeleteCheckout => $this->handleDeleteCheckout($basket, $product, $remove),
            BasketAction::ChangeCount => $this->handleChangeCount($basket, $product, $count, $addOrUpdate),
            BasketAction::ChangeCountModal => $this->handleChangeCountModal($basket, $product, $count, $addOrUpdate),
            BasketAction::ChangeCountCheckout => $this->handleChangeCountCheckout($basket, $product, $count, $addOrUpdate),
        };

        if ($forked) {
            return response()->json([
                'success' => true,
                'actions' => [
                    ['method' => 'location', 'content' => module_url('basket', [$basket->uuid])],
                ],
            ]);
        }

        return response()->json([
            'success' => true,
            'actions' => $actions,
        ]);
    }

    private function handleDeleteModal(Basket $basket, Product $product, RemoveBasketItemAction $remove): array
    {
        $remove->handle($basket, $product);

        return [
            $this->basketCountAction($basket),
            [
                'selector' => '.js_basket_modal_content',
                'method' => 'html',
                'content' => '',
            ],
        ];
    }

    private function handleDeleteCheckout(Basket $basket, Product $product, RemoveBasketItemAction $remove): array
    {
        $remove->handle($basket, $product);
        $basket->refresh();

        return [
            $this->basketCountAction($basket),
            [
                'selector' => '.checkout_item[data-product="'.$product->id.'"]',
                'method' => 'remove',
            ],
            [
                'selector' => '.js_checkout_total',
                'method' => 'text',
                'content' => $basket->total_amount,
            ],
        ];
    }

    private function handleChangeCount(Basket $basket, Product $product, int $count, AddOrUpdateBasketItemAction $addOrUpdate): array
    {
        $item = $addOrUpdate->handle($basket, $product, $count);
        $basket->refresh();

        return [
            $this->basketCountAction($basket),
            [
                'selector' => '.js_basket_modal_content',
                'method' => 'html',
                'content' => view('pages.shop.basket_modal_item', compact('item'))->render(),
            ],
            [
                'selector' => '#cartModal',
                'method' => 'modal',
            ],
        ];
    }

    private function handleChangeCountModal(Basket $basket, Product $product, int $count, AddOrUpdateBasketItemAction $addOrUpdate): array
    {
        $item = $addOrUpdate->handle($basket, $product, $count);

        return [
            $this->basketCountAction($basket->refresh()),
            [
                'selector' => '.js_product_total',
                'method' => 'text',
                'content' => $item->total,
            ],
        ];
    }

    private function handleChangeCountCheckout(Basket $basket, Product $product, int $count, AddOrUpdateBasketItemAction $addOrUpdate): array
    {
        $item = $addOrUpdate->handle($basket, $product, $count);
        $basket->refresh();

        return [
            $this->basketCountAction($basket),
            [
                'selector' => '.js_product_checkout_total_'.$product->id,
                'method' => 'text',
                'content' => $item->total,
            ],
            [
                'selector' => '.js_checkout_total',
                'method' => 'text',
                'content' => $basket->total_amount,
            ],
        ];
    }

    private function basketCountAction(Basket $basket): array
    {
        return [
            'selector' => '.js_basket_count',
            'method' => 'text',
            'content' => $basket->items()->sum('quantity'),
        ];
    }

    private function isOwnBasket(Basket $basket): bool
    {
        if ($user = Auth::user()) {
            return $basket->user_id === $user->id;
        }

        return $basket->user_id === null && $basket->uuid === Session::get('basket_uuid');
    }
}
