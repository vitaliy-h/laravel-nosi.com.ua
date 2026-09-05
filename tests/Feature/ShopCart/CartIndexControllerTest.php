<?php

namespace Tests\Feature\ShopCart;

use App\Modules\ShopCart\Models\Cart;
use App\Modules\ShopCart\Models\CartProduct;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class CartIndexControllerTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seedPagesForRouting();
    }

    public function test_index_shows_empty_cart_view_without_session(): void
    {
        $response = $this->get(save_route('cart.index'));

        $response->assertStatus(200);
        $response->assertViewIs('modules.shop_cart.empty_cart');
    }

    public function test_index_redirects_to_uuid_url_when_cart_has_items(): void
    {
        $cart = Cart::factory()->create();
        CartProduct::factory()->create(['cart_id' => $cart->id]);

        $response = $this->withSession(['cart_uuid' => $cart->uuid])->get(save_route('cart.index'));

        $response->assertRedirect(save_route('cart.show', $cart->uuid));
    }

    public function test_index_by_uuid_shows_cart_view_with_items(): void
    {
        $cart = Cart::factory()->create();
        CartProduct::factory()->create(['cart_id' => $cart->id]);

        $response = $this->get(save_route('cart.show', $cart->uuid));

        $response->assertStatus(200);
        $response->assertViewIs('modules.shop_cart.index');
    }

    public function test_index_by_uuid_shows_empty_view_when_no_items(): void
    {
        $cart = Cart::factory()->create();

        $response = $this->get(save_route('cart.show', $cart->uuid));

        $response->assertStatus(200);
        $response->assertViewIs('modules.shop_cart.empty_cart');
    }

    public function test_index_by_unknown_uuid_returns_404(): void
    {
        $response = $this->get(save_route('cart.show', Str::uuid()));

        $response->assertStatus(404);
    }

    public function test_checkout_segment_renders_checkout_view(): void
    {
        $cart = Cart::factory()->create();
        CartProduct::factory()->create(['cart_id' => $cart->id]);

        $response = $this->withSession(['cart_uuid' => $cart->uuid])->get(save_route('cart.checkout'));

        $response->assertStatus(200);
        $response->assertViewIs('modules.shop_cart.checkout');
    }
}
