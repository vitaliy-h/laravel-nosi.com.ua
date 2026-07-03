<?php

namespace Tests\Unit\Basket;

use App\Modules\Basket\Actions\MergeBasketOnAuthAction;
use App\Modules\Basket\Models\Basket;
use App\Modules\Basket\Models\BasketProduct;
use App\Modules\Core\Models\User;
use App\Modules\Shop\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Session;
use Tests\TestCase;

class MergeBasketOnAuthActionTest extends TestCase
{
    use RefreshDatabase;

    public function test_does_nothing_without_session_uuid(): void
    {
        $user = User::factory()->create();

        app(MergeBasketOnAuthAction::class)->handle($user);

        $this->assertNull($user->basket()->first());
    }

    public function test_does_nothing_when_session_basket_already_has_a_user(): void
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();
        $sessionBasket = Basket::factory()->create(['user_id' => $otherUser->id]);
        Session::put('basket_uuid', $sessionBasket->uuid);

        app(MergeBasketOnAuthAction::class)->handle($user);

        $sessionBasket->refresh();
        $this->assertSame($otherUser->id, $sessionBasket->user_id);
    }

    public function test_assigns_session_basket_to_user_without_existing_basket(): void
    {
        $user = User::factory()->create();
        $sessionBasket = Basket::factory()->create();
        Session::put('basket_uuid', $sessionBasket->uuid);

        app(MergeBasketOnAuthAction::class)->handle($user);

        $sessionBasket->refresh();
        $this->assertSame($user->id, $sessionBasket->user_id);
    }

    public function test_merges_items_into_existing_user_basket_and_deletes_session_basket(): void
    {
        $user = User::factory()->create();
        $userBasket = Basket::factory()->create(['user_id' => $user->id]);
        $sessionBasket = Basket::factory()->create();
        Session::put('basket_uuid', $sessionBasket->uuid);

        $sharedProduct = Product::factory()->create();
        BasketProduct::factory()->create([
            'basket_id' => $userBasket->id,
            'shop_product_id' => $sharedProduct->id,
            'quantity' => 1,
            'total' => 10,
        ]);
        BasketProduct::factory()->create([
            'basket_id' => $sessionBasket->id,
            'shop_product_id' => $sharedProduct->id,
            'quantity' => 3,
            'total' => 30,
        ]);
        $uniqueItem = BasketProduct::factory()->create(['basket_id' => $sessionBasket->id, 'total' => 5]);

        app(MergeBasketOnAuthAction::class)->handle($user);

        $this->assertModelMissing($sessionBasket);
        $this->assertSame(2, $userBasket->items()->count());
        $this->assertDatabaseHas('shop_basket_products', [
            'basket_id' => $userBasket->id,
            'shop_product_id' => $sharedProduct->id,
            'quantity' => 3,
            'total' => 30,
        ]);
        $this->assertDatabaseHas('shop_basket_products', [
            'basket_id' => $userBasket->id,
            'shop_product_id' => $uniqueItem->shop_product_id,
        ]);

        $userBasket->refresh();
        $this->assertSame('35.00', (string) $userBasket->total_amount);
    }
}
