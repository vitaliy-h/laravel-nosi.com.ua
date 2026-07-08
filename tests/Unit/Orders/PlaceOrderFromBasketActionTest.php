<?php

namespace Tests\Unit\Orders;

use App\Modules\Basket\Models\Basket;
use App\Modules\Basket\Models\BasketProduct;
use App\Modules\Core\Models\User;
use App\Modules\Orders\Actions\PlaceOrderFromBasketAction;
use App\Modules\Orders\Dtos\CustomerData;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PlaceOrderFromBasketActionTest extends TestCase
{
    use RefreshDatabase;

    public function test_creates_order_with_items_copied_from_basket(): void
    {
        $basket = Basket::factory()->create();
        BasketProduct::factory()->create([
            'basket_id' => $basket->id,
            'product_name' => 'Test Product',
            'product_article' => 'SKU-1',
            'selling_price' => 15,
            'quantity' => 3,
            'total' => 45,
        ]);

        $order = app(PlaceOrderFromBasketAction::class)->handle($basket, new CustomerData(
            user_name: 'John',
            user_email: 'john@example.com',
        ));

        $this->assertSame(1, $order->items()->count());

        $item = $order->items()->first();
        $this->assertSame('Test Product', $item->product_name);
        $this->assertSame('SKU-1', $item->product_article);
        $this->assertSame(3, $item->quantity);
        $this->assertSame('John', $order->user_name);
        $this->assertSame('john@example.com', $order->user_email);
    }

    public function test_copies_user_id_from_basket_when_present(): void
    {
        $user = User::factory()->create();
        $basket = Basket::factory()->create(['user_id' => $user->id]);
        BasketProduct::factory()->create(['basket_id' => $basket->id]);

        $order = app(PlaceOrderFromBasketAction::class)->handle($basket, new CustomerData());

        $this->assertSame($user->id, $order->user_id);
    }

    public function test_deletes_the_basket_after_placing_order(): void
    {
        $basket = Basket::factory()->create();
        BasketProduct::factory()->create(['basket_id' => $basket->id]);

        app(PlaceOrderFromBasketAction::class)->handle($basket, new CustomerData());

        $this->assertDatabaseMissing('shop_baskets', ['id' => $basket->id]);
    }
}
