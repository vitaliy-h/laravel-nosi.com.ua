<?php

namespace Tests\Unit\ShopCart;

use App\Modules\Shop\Models\Product;
use App\Modules\ShopCart\Actions\MergeCartOnAuthAction;
use App\Modules\ShopCart\Models\Cart;
use App\Modules\ShopCart\Models\CartProduct;
use App\Modules\User\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Session;
use Tests\TestCase;

class MergeCartOnAuthActionTest extends TestCase
{
    use RefreshDatabase;

    public function test_does_nothing_without_session_uuid(): void
    {
        $user = User::factory()->create();

        app(MergeCartOnAuthAction::class)->handle($user);

        $this->assertNull($user->cart()->first());
    }

    public function test_does_nothing_when_session_cart_already_has_a_user(): void
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();
        $sessionCart = Cart::factory()->create(['user_id' => $otherUser->id]);
        Session::put('cart_uuid', $sessionCart->uuid);

        app(MergeCartOnAuthAction::class)->handle($user);

        $sessionCart->refresh();
        $this->assertSame($otherUser->id, $sessionCart->user_id);
    }

    public function test_assigns_session_cart_to_user_without_existing_cart(): void
    {
        $user = User::factory()->create();
        $sessionCart = Cart::factory()->create();
        Session::put('cart_uuid', $sessionCart->uuid);

        app(MergeCartOnAuthAction::class)->handle($user);

        $sessionCart->refresh();
        $this->assertSame($user->id, $sessionCart->user_id);
    }

    public function test_merges_items_into_existing_user_cart_and_deletes_session_cart(): void
    {
        $user = User::factory()->create();
        $userCart = Cart::factory()->create(['user_id' => $user->id]);
        $sessionCart = Cart::factory()->create();
        Session::put('cart_uuid', $sessionCart->uuid);

        $sharedProduct = Product::factory()->create();
        CartProduct::factory()->create([
            'cart_id'         => $userCart->id,
            'shop_product_id' => $sharedProduct->id,
            'quantity'        => 1,
            'total'           => 10,
        ]);
        CartProduct::factory()->create([
            'cart_id'         => $sessionCart->id,
            'shop_product_id' => $sharedProduct->id,
            'quantity'        => 3,
            'total'           => 30,
        ]);
        $uniqueItem = CartProduct::factory()->create(['cart_id' => $sessionCart->id, 'total' => 5]);

        app(MergeCartOnAuthAction::class)->handle($user);

        $this->assertModelMissing($sessionCart);
        $this->assertSame(2, $userCart->items()->count());
        $this->assertDatabaseHas('shop_cart_products', [
            'cart_id'         => $userCart->id,
            'shop_product_id' => $sharedProduct->id,
            'quantity'        => 3,
            'total'           => 30,
        ]);
        $this->assertDatabaseHas('shop_cart_products', [
            'cart_id'         => $userCart->id,
            'shop_product_id' => $uniqueItem->shop_product_id,
        ]);

        $userCart->refresh();
        $this->assertSame('35.00', (string) $userCart->total_amount);
    }
}
