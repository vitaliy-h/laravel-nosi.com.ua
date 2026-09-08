<?php

namespace Tests\Unit\ShopCart;

use App\Modules\Shop\Models\Product;
use App\Modules\ShopCart\Actions\MergeCartOnAuthAction;
use App\Modules\ShopCart\Actions\RecalculateCartTotalAction;
use App\Modules\ShopCart\Models\Cart;
use App\Modules\ShopCart\Models\CartProduct;
use App\Modules\User\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use RuntimeException;
use Tests\TestCase;

class MergeCartOnAuthActionTest extends TestCase
{
    use RefreshDatabase;

    public function test_does_nothing_without_a_guest_cart_uuid(): void
    {
        $user = User::factory()->create();

        app(MergeCartOnAuthAction::class)->handle($user, null);

        $this->assertNull($user->cart()->first());
    }

    public function test_does_nothing_when_the_guest_cart_already_has_a_user(): void
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();
        $sessionCart = Cart::factory()->create(['user_id' => $otherUser->id]);
        app(MergeCartOnAuthAction::class)->handle($user, $sessionCart->uuid);

        $sessionCart->refresh();
        $this->assertSame($otherUser->id, $sessionCart->user_id);
    }

    public function test_assigns_guest_cart_to_user_without_existing_cart(): void
    {
        $user = User::factory()->create();
        $sessionCart = Cart::factory()->create();
        app(MergeCartOnAuthAction::class)->handle($user, $sessionCart->uuid);

        $sessionCart->refresh();
        $this->assertSame($user->id, $sessionCart->user_id);
    }

    public function test_merges_items_into_existing_user_cart_and_deletes_guest_cart(): void
    {
        $user = User::factory()->create();
        $userCart = Cart::factory()->create(['user_id' => $user->id]);
        $sessionCart = Cart::factory()->create();
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

        app(MergeCartOnAuthAction::class)->handle($user, $sessionCart->uuid);

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

    /**
     * Copying the lines over and dropping the guest cart is one transaction: a
     * failure in between would leave the user holding both carts, with the
     * shared lines counted twice.
     */
    public function test_a_failed_merge_leaves_both_carts_as_they_were(): void
    {
        $user = User::factory()->create();
        $userCart = Cart::factory()->create(['user_id' => $user->id]);
        $guestCart = Cart::factory()->create();
        CartProduct::factory()->create(['cart_id' => $guestCart->id, 'total' => 10]);

        $this->mock(RecalculateCartTotalAction::class)
            ->shouldReceive('handle')
            ->andThrow(new RuntimeException('recalculation failed'));

        try {
            app(MergeCartOnAuthAction::class)->handle($user, $guestCart->uuid);
            $this->fail('Merging the carts was expected to fail.');
        } catch (RuntimeException) {
            // The rollback is what the assertions below look at.
        }

        $this->assertModelExists($guestCart);
        $this->assertSame(1, $guestCart->items()->count());
        $this->assertSame(0, $userCart->items()->count());
    }
}
