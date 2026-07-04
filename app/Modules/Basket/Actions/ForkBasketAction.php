<?php

namespace App\Modules\Basket\Actions;

use App\Modules\Basket\Models\Basket;
use App\Modules\Core\Models\User;
use Illuminate\Support\Facades\Session;

class ForkBasketAction
{
    public function __construct(
        private RecalculateBasketTotalAction $recalculate,
        private FindBasketAction $findBasket,
    ) {}

    public function handle(Basket $source, ?User $user): Basket
    {
        $this->findBasket->handle($user)?->delete();

        $basket = Basket::create(['user_id' => $user?->id]);

        foreach ($source->items as $item) {
            $basket->items()->create([
                'shop_product_id' => $item->shop_product_id,
                'product_name' => $item->product_name,
                'product_article' => $item->product_article,
                'selling_price' => $item->selling_price,
                'quantity' => $item->quantity,
                'total' => $item->total,
            ]);
        }

        $this->recalculate->handle($basket);

        Session::put('basket_uuid', $basket->uuid);

        return $basket;
    }
}
