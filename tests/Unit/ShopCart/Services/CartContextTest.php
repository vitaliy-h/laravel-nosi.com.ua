<?php

namespace Tests\Unit\ShopCart\Services;

use App\Modules\ShopCart\Models\Cart;
use App\Modules\ShopCart\Models\CartProduct;
use App\Modules\ShopCart\Services\CartContext;
use App\Modules\User\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Session;
use Tests\TestCase;

/**
 * CartContext is the one class allowed to read the session and Auth on the
 * cart's behalf, so the session wiring the Actions and Queries no longer do is
 * asserted here.
 */
class CartContextTest extends TestCase
{
    use RefreshDatabase;

    private function context(): CartContext
    {
        return app(CartContext::class);
    }

    public function test_current_finds_the_guest_cart_named_by_the_session(): void
    {
        $cart = Cart::factory()->create();
        Session::put('cart_uuid', $cart->uuid);

        $this->assertSame($cart->id, $this->context()->current()->id);
    }

    public function test_current_prefers_the_carts_of_the_signed_in_user(): void
    {
        $user = User::factory()->create();
        $userCart = Cart::factory()->create(['user_id' => $user->id]);
        Session::put('cart_uuid', Cart::factory()->create()->uuid);

        $this->actingAs($user);

        $this->assertSame($userCart->id, $this->context()->current()->id);
    }

    public function test_current_or_create_stores_the_new_uuid_in_the_session(): void
    {
        $cart = $this->context()->currentOrCreate();

        $this->assertSame($cart->uuid, Session::get('cart_uuid'));
    }

    public function test_remember_points_the_session_at_a_cart(): void
    {
        $cart = Cart::factory()->create();

        $this->context()->remember($cart);

        $this->assertSame($cart->uuid, Session::get('cart_uuid'));
    }

    public function test_count_sums_quantities_and_caches_the_answer(): void
    {
        $cart = Cart::factory()->create();
        CartProduct::factory()->create(['cart_id' => $cart->id, 'quantity' => 2]);
        CartProduct::factory()->create(['cart_id' => $cart->id, 'quantity' => 3]);
        Session::put('cart_uuid', $cart->uuid);

        $this->assertSame(5, $this->context()->count());
        $this->assertSame(5, Session::get('cart_count'));
    }

    public function test_count_reads_the_cached_value_rather_than_the_cart(): void
    {
        $cart = Cart::factory()->create();
        CartProduct::factory()->create(['cart_id' => $cart->id, 'quantity' => 2]);
        Session::put('cart_uuid', $cart->uuid);
        Session::put('cart_count', 42);

        $this->assertSame(42, $this->context()->count());
    }

    public function test_forget_count_makes_the_next_read_recount(): void
    {
        $cart = Cart::factory()->create();
        CartProduct::factory()->create(['cart_id' => $cart->id, 'quantity' => 2]);
        Session::put('cart_uuid', $cart->uuid);
        Session::put('cart_count', 42);

        $this->context()->forgetCount();

        $this->assertSame(2, $this->context()->count());
    }

    public function test_clear_drops_both_keys(): void
    {
        Session::put('cart_uuid', Cart::factory()->create()->uuid);
        Session::put('cart_count', 7);

        $this->context()->clear();

        $this->assertFalse(Session::has('cart_uuid'));
        $this->assertSame(0, $this->context()->count());
    }

    public function test_count_is_zero_while_the_module_is_off(): void
    {
        config()->set('modules.list.shop_cart', false);

        $cart = Cart::factory()->create();
        CartProduct::factory()->create(['cart_id' => $cart->id, 'quantity' => 2]);
        Session::put('cart_uuid', $cart->uuid);

        $this->assertSame(0, $this->context()->count());
    }
}
