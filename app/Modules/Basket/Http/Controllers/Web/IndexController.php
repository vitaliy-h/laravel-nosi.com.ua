<?php

namespace App\Modules\Basket\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Modules\Core\Interfaces\ModuleIndexControllerInterface;
use App\Modules\Shop\Models\Product;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Modules\Basket\Http\Requests\ChangeBasketRequest;


// TODO: Refactor basket to use spatie/laravel-data DTOs instead of raw arrays.
//
// Current problem: basket lives as untyped array in session — no validation, no type safety,
// shape can silently drift between methods (e.g. 'sum' vs 'summ' inconsistency in this file).
//
// Proposed structure:
//   BasketItemData extends Data { int $id, string $name, int $count, float $price, float $sum }
//   BasketData     extends Data { DataCollection $products, int $count, float $total }
//
// Benefits:
//   - BasketData::from(session('basket', [])) — auto-cast & validate on read
//   - BasketData->toArray()                   — safe write back to session
//   - IDE autocomplete, static analysis, no more magic array keys
//   - Easy to transform to API resource or event payload later
//
// Steps:
//   1. Create app/Modules/Basket/Data/BasketItemData.php
//   2. Create app/Modules/Basket/Data/BasketData.php
//   3. Replace raw array reads/writes in index(), api_change(), recalculateBasket()
//   4. Replace compact() view calls with $basketData->toArray() or pass DTO directly
//   5. Update ChangeBasketRequest to use BasketItemData::validateAndCreate() if applicable
class IndexController extends Controller implements ModuleIndexControllerInterface
{
    public function accessCheck(): RedirectResponse|Response|null
    {
        return null;
    }
    // TODO: Replace string $action with a PHP enum + handler map.
    //
    // Problem: raw string comparisons scattered across nested if-blocks — adding
    // a new action means editing multiple in_array() calls and adding more if-branches.
    //
    // Option A — BackedEnum (simplest, recommended):
    //   enum BasketAction: string {
    //       case DeleteModal    = 'delete_modal';
    //       case DeleteCheckout = 'delete_checkout';
    //       case ChangeCount         = 'change_count';
    //       case ChangeCountModal    = 'change_count_modal';
    //       case ChangeCountCheckout = 'change_count_checkout';
    //
    //       public function isDelete(): bool  { return in_array($this, [self::DeleteModal, self::DeleteCheckout]); }
    //       public function isChange(): bool  { return !$this->isDelete(); }
    //   }
    //   $action = BasketAction::from($request->input('action')); // throws ValueError on unknown value — free validation
    //   if ($action->isDelete()) { ... }
    //   match ($action) { BasketAction::DeleteModal => ..., BasketAction::DeleteCheckout => ... }
    //
    //
    // Option B — separate controller methods + route per action (REST-ish):
    //   DELETE /basket/{product}           → BasketController@destroy
    //   PATCH  /basket/{product}/count     → BasketController@updateCount
    //   Cleanest HTTP semantics; requires JS to call different endpoints.
    //
    // Recommendation: Option A now (zero new files), Option B if JS side is refactored.
    public function api_change(ChangeBasketRequest $request)
    {
        $productId = (int) $request->input('id');
        $count = max(1, (int) $request->input('count'));
        $action = $request->input('action');

        $actions = [];

        $basket = session('basket', [
            'products' => [],
            'count' => 0,
            'total' => 0,
        ]);

        $product = Product::query()->findOrFail($productId);

        if (in_array($action, ['delete_modal', 'delete_checkout'])) {

            unset($basket['products'][$productId]);

            $basket = $this->recalculateBasket($basket);
            session(['basket' => $basket]);

            $actions[] = [
                'selector' => '.js_basket_count',
                'method' => 'text',
                'content' => $basket['count'],
            ];

            if ($action == 'delete_modal') {
                $actions[] = [
                    'selector' => '.js_basket_modal_content',
                    'method' => 'html',
                    'content' => '',
                ];
            }

            if ($action == 'delete_checkout') {
                $actions[] = [
                    'selector' => '.checkout_item[data-product="'.$product->id.'"]',
                    'method' => 'remove',
                ];

                $actions[] = [
                    'selector' => '.js_checkout_total',
                    'method' => 'text',
                    'content' => $basket['total'],
                ];
            }

            return response()->json([
                'success' => true,
                'actions' => $actions,
            ]);
        }

        if (in_array($action, ['change_count', 'change_count_modal', 'change_count_checkout'])) {

            $sum = $count * $product->price;

            $basket['products'][$productId] = [
                'id' => $product->id,
                'name' => $product->title,
                'count' => $count,
                'price' => $product->price,
                'sum' => $sum,
            ];

            $product->_count = $count;
            $product->_sum = $sum;
            // todo: with DTO this wouldn't be needed — just pass BasketItemData directly to the modal view

            $basket = $this->recalculateBasket($basket);
            session(['basket' => $basket]);

            $actions[] = [
                'selector' => '.js_basket_count',
                'method' => 'text',
                'content' => $basket['count'],
            ];

            if ($action == 'change_count') {
                $actions[] = [
                    'selector' => '.js_basket_modal_content',
                    'method' => 'html',
                    'content' => view('pages.shop.basket_modal_item', compact('product'))->render(),
                ];

                $actions[] = [
                    'selector' => 'cartModal',
                    'method' => 'modal',
                ];
            }

            if ($action == 'change_count_modal') {
                $actions[] = [
                    'selector' => '.js_product_total',
                    'method' => 'text',
                    'content' => $product->_sum,
                ];
            }

            if ($action == 'change_count_checkout') {
                $actions[] = [
                    'selector' => '.js_product_checkout_total_'.$product->id,
                    'method' => 'text',
                    'content' => $product->_sum,
                ];

                $actions[] = [
                    'selector' => '.js_checkout_total',
                    'method' => 'text',
                    'content' => $basket['total'],
                ];
            }
        }

        return response()->json([
            'success' => true,
            'actions' => $actions,
        ]);
    }
    private function recalculateBasket(array $basket): array
    {
        $basket['count'] = 0;
        $basket['total'] = 0;

        $products = Product::query()
            ->whereIn('id', array_keys($basket['products']))
            ->get()
            ->keyBy('id');

        // TODO: Drop &$item reference magic — use direct key access instead.
        // &$item mutates $basket['products'][$id] in-place but requires unset($item)
        // after loop or next variable reuses the reference silently.
        // Replace with: foreach ($basket['products'] as $id => $item) { $basket['products'][$id]['name'] = ...; }
        foreach ($basket['products'] as $id => &$item) {

            if (!isset($products[$id])) {
                unset($basket['products'][$id]);
                continue;
            }

            $product = $products[$id];

            $item['name'] = $product->title;
            $item['price'] = $product->price;
            $item['summ'] = $product->price * $item['count'];

            $basket['count'] += $item['count'];
            $basket['total'] += $item['summ'];
        }

        unset($item);

        return $basket;
    }
    public function checkout(Request $request){
        echo 8888;
        /*todo remove this*/





    }
    public function index(Request $request, array $segments = [])
    {
        //dd($segments);
        $basket = session('basket', [
            'products' => [],
            'count' => 0,
            'total' => 0,
        ]);

        $basketProducts = $basket['products'] ?? [];

        $ids = array_keys($basketProducts);
        //dd($ids);
        $products = Product::query()
            ->whereIn('id', $ids)
            ->get()
            ->map(function (Product $product) use (&$basket, $basketProducts) {
                $count = (int) ($basketProducts[$product->id]['count'] ?? 1);

                $product->basket_count = $count;
                $product->basket_total = $product->price * $count;
                $basket['products'][$product->id]['img'] = $product->image??'/assets/images/no_image.svg';
                /* todo app/Modules/Core/Traits/Models/HasImage.php already returns "/assets/images/no_image.svg" in image when no image is set*/
                return $product;
            });
        //dd($basket);
        //dd($basket);
        /*todo remove comments*/
        $total = $basket['total'] ?? 0;
        $count = $basket['count'] ?? 0;

        if (isset($segments[0]) AND $segments[0] == 'checkout'){
            return view('pages.basket.checkout', compact(
                'basket',
                'products',
                'total',
                'count'
            ));

        }
        //dd($basket);
        elseif($count && $total && @$basket['products']){
            //dd($count);
            /*todo: why two separate views for basket and empty basket? */
            return view('pages.basket.index', compact(
                'basket',
                'products',
                'total',
                'count'
            ));
        }
        else{
            return view('pages.basket.empty_basket');
        }
    }
}
