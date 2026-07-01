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



// Question:
// Could you please explain what concrete issue the DTO approach would solve in the current basket implementation?
// The basket currently has a small session-based structure: products, count and total.
// The known issue with inconsistent array keys can be fixed locally without introducing a new package
// and rewriting the basket flow.
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

    // Question:
// Do we really need a PHP enum and a handler map here?
//
// The allowed action values should already be validated in ChangeBasketRequest,
// so the enum would mostly duplicate that validation while adding an extra file
// and another abstraction.
//
// Also, these actions are not fully independent handlers.
// For example, change_count, change_count_modal and change_count_checkout share
// the same basket mutation logic and differ only by the UI response actions.
// A handler map may split this shared logic into multiple places and increase duplication.
    public function ajax_basket_change(ChangeBasketRequest $request)
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
// Question:
// Do we really need to refactor this part and introduce BasketItemData just for the modal view?
// The current code already works, and this looks like a local view-data issue rather than
// a reason to refactor the basket flow.
// Could you please clarify what concrete problem this change is expected to solve?
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
                    'selector' => '#cartModal',
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

        foreach ($basket['products'] as $id => $item) {

            if (!isset($products[$id])) {
                unset($basket['products'][$id]);
                continue;
            }

            $product = $products[$id];

            $basket['products'][$id]['name'] = $product->title;
            $basket['products'][$id]['price'] = $product->price;
            $basket['products'][$id]['sum'] = $product->price * $item['count'];

            $basket['count'] += $item['count'];
            $basket['total'] += $basket['products'][$id]['sum'];
        }


        return $basket;
    }
    public function checkout(Request $request){
    }
    public function index(Request $request, array $segments = [])
    {
        $basket = session('basket', [
            'products' => [],
            'count' => 0,
            'total' => 0,
        ]);

        $basketProducts = $basket['products'] ?? [];

        $ids = array_keys($basketProducts);
        $products = Product::query()
            ->whereIn('id', $ids)
            ->get()
            ->map(function (Product $product) use (&$basket, $basketProducts) {
                $count = (int) ($basketProducts[$product->id]['count'] ?? 1);

                $product->basket_count = $count;
                $product->basket_total = $product->price * $count;
                $basket['products'][$product->id]['img'] = $product->image;
                return $product;
            });
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
        elseif($count && $total && @$basket['products']){
            /*todo: why two separate views for basket and empty basket?
            This is intentional.

An empty basket is not simply a basket without products. It is a separate page with a different purpose and may evolve into a mini catalog with featured categories, recommendations, promotions, and navigation to popular sections.

Because of that, it has its own template and layout. The basket with products and the empty basket share very little UI, so keeping them separate is simpler, cleaner, and easier to maintain than handling both states in a single view.

            */
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
