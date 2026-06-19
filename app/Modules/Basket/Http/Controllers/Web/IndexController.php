<?php

namespace App\Modules\Basket\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Modules\Core\Interfaces\ModuleIndexControllerInterface;
use App\Modules\Shop\Models\Product;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Modules\Basket\Http\Requests\ChangeBasketRequest;


class IndexController extends Controller implements ModuleIndexControllerInterface
{
    public function accessCheck(): RedirectResponse|Response|null
    {
        return null;
    }
    public function api_change(ChangeBasketRequest $request)
    {
        $productId = (int) $request->input('id');
        $count = max(1, (int) $request->input('count'));
        $action = $request->input('action');

        $basket = session('basket', [
            'products' => [],
            'count' => 0,
            'total' => 0,
        ]);

        if ($action === 'delete') {
            unset($basket['products'][$productId]);
        }

        if ($action === 'change_count') {
            $product = Product::query()->findOrFail($productId);

            $basket['products'][$productId] = [
                'id' => $product->id,
                'name' => $product->title,
                'count' => $count,
                'price' => $product->price,
                'summ' => 0,
            ];
        }

        $basket = $this->recalculateBasket($basket);

        session(['basket' => $basket]);

        return response()->json([
            'success' => true,
            'actions' => [
                array(
                    'selector' => '.js_basket_count',
                    'method' => 'text',
                    'content' => $basket['count']
                )
            ],
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
    public function index(Request $request, array $segments = [])
    {

    }

}
