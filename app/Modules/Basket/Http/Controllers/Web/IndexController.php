<?php

namespace App\Modules\Basket\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Modules\Core\Interfaces\ModuleIndexControllerInterface;
use App\Modules\Shop\Models\Product;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class IndexController extends Controller implements ModuleIndexControllerInterface
{
    public function accessCheck(): RedirectResponse|Response|null
    {
        return null;
    }

    public function index(Request $request, array $segments = [])
    {
        if ($request->isMethod('post')) {
            return $this->change($request);
        }

        $basket = session('basket', [
            'products' => [],
        ]);

        $basketProducts = $basket['products'] ?? [];

        $ids = array_keys($basketProducts);

        $products = Product::query()
            ->whereIn('id', $ids)
            ->get()
            ->map(function ($product) use ($basketProducts) {
                $product->basket_count = $basketProducts[$product->id]['count'] ?? 1;
                $product->basket_sum = $product->price * $product->basket_count;

                return $product;
            });

        $total = $products->sum('basket_sum');

        return view('pages.basket.index', compact(
            'basket',
            'products',
            'total',
        ));
    }

    private function change(Request $request)
    {
        $productId = (int) $request->input('product_id');
        $count = (int) $request->input('quantity', 1);

        if ($productId <= 0) {
            return response()->json([
                'success' => false,
                'message' => 'Невірний товар',
            ], 422);
        }

        if ($count < 1) {
            $count = 1;
        }

        $product = Product::query()->find($productId);

        if (!$product) {
            return response()->json([
                'success' => false,
                'message' => 'Товар не знайдено',
            ], 404);
        }

        $basket = session('basket', [
            'products' => [],
        ]);

        $basket['products'][$product->id] = [
            'id' => $product->id,
            'count' => $count,
        ];

        session(['basket' => $basket]);

        return response()->json([
            'success' => true,
            'basket' => $basket,
            'product_id' => $product->id,
            'count' => $count,
            'message' => 'Товар додано в корзину',
        ]);
    }
}
