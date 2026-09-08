<?php

namespace Tests\Feature\ShopCart;

use App\Modules\Core\Support\Money;
use App\Modules\Shop\Models\Product;
use App\Modules\ShopCart\Models\Cart;
use App\Modules\ShopCart\Models\CartProduct;
use App\Modules\User\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\InteractsWithPages;
use Tests\TestCase;

class AjaxCartControllerTest extends TestCase
{
    use InteractsWithPages;
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seedPagesForRouting();
    }

    public function test_change_count_creates_item_for_guest(): void
    {
        $product = Product::factory()->create();

        $response = $this->postJson(save_route('change_product_count'), [
            'id'     => $product->id,
            'count'  => 2,
            'action' => 'change_count',
        ]);

        $response->assertStatus(200);
        $response->assertJsonPath('success', true);
        $this->assertDatabaseHas('shop_cart_products', [
            'shop_product_id' => $product->id,
            'quantity'        => 2,
        ]);
    }

    public function test_change_count_for_authenticated_user_uses_their_cart(): void
    {
        $user = User::factory()->create();
        $product = Product::factory()->create();

        $response = $this->actingAs($user)->postJson(save_route('change_product_count'), [
            'id'     => $product->id,
            'count'  => 1,
            'action' => 'change_count',
        ]);

        $response->assertStatus(200);
        $cart = $user->fresh()->cart;
        $this->assertNotNull($cart);
        $this->assertDatabaseHas('shop_cart_products', [
            'cart_id'         => $cart->id,
            'shop_product_id' => $product->id,
        ]);
    }

    public function test_delete_modal_removes_item_and_returns_empty_content(): void
    {
        $cart = Cart::factory()->create();
        $product = Product::factory()->create();
        CartProduct::factory()->create(['cart_id' => $cart->id, 'shop_product_id' => $product->id]);
        session(['cart_uuid' => $cart->uuid]);

        $response = $this->withSession(['cart_uuid' => $cart->uuid])->postJson(save_route('change_product_count'), [
            'id'     => $product->id,
            'count'  => 1,
            'action' => 'delete_modal',
        ]);

        $response->assertStatus(200);
        $response->assertJsonPath('success', true);
        $this->assertDatabaseMissing('shop_cart_products', ['cart_id' => $cart->id, 'shop_product_id' => $product->id]);
    }

    public function test_delete_checkout_returns_updated_total_action(): void
    {
        $cart = Cart::factory()->create();
        $product = Product::factory()->create();
        $other = Product::factory()->create();
        CartProduct::factory()->create(['cart_id' => $cart->id, 'shop_product_id' => $product->id, 'total' => 10]);
        CartProduct::factory()->create(['cart_id' => $cart->id, 'shop_product_id' => $other->id, 'total' => 5]);

        $response = $this->withSession(['cart_uuid' => $cart->uuid])->postJson(save_route('change_product_count'), [
            'id'     => $product->id,
            'count'  => 1,
            'action' => 'delete_checkout',
        ]);

        $response->assertStatus(200);
        $actions = $response->json('actions');
        // The total travels the way the page renders it — formatted, currency included.
        $expected = Money::from($cart->fresh()->total_amount)->format();
        $this->assertTrue(collect($actions)->contains(fn ($a) => ($a['selector'] ?? null) === '.js_checkout_total' && $a['content'] === $expected));
    }

    public function test_delete_checkout_last_item_redirects_to_cart(): void
    {
        $cart = Cart::factory()->create();
        $product = Product::factory()->create();
        CartProduct::factory()->create(['cart_id' => $cart->id, 'shop_product_id' => $product->id, 'total' => 10]);

        $response = $this->withSession(['cart_uuid' => $cart->uuid])->postJson(save_route('change_product_count'), [
            'id'     => $product->id,
            'count'  => 1,
            'action' => 'delete_checkout',
        ]);

        $response->assertStatus(200);
        $this->assertSame('location', $response->json('actions.0.method'));
    }

    public function test_change_count_zero_is_clamped_to_minimum_one(): void
    {
        $product = Product::factory()->create();

        $response = $this->postJson(save_route('change_product_count'), [
            'id'     => $product->id,
            'count'  => 0,
            'action' => 'change_count',
        ]);

        $response->assertStatus(200);
        $this->assertDatabaseHas('shop_cart_products', [
            'shop_product_id' => $product->id,
            'quantity'        => 1,
        ]);
    }

    public function test_viewing_own_cart_by_uuid_does_not_fork(): void
    {
        $cart = Cart::factory()->create();
        $product = Product::factory()->create();

        $response = $this->withSession(['cart_uuid' => $cart->uuid])->postJson(save_route('change_product_count'), [
            'id'        => $product->id,
            'count'     => 1,
            'action'    => 'change_count',
            'cart_uuid' => $cart->uuid,
        ]);

        $response->assertStatus(200);
        $this->assertDatabaseCount('shop_carts', 1);
    }

    public function test_viewing_foreign_cart_forks_and_returns_redirect_action(): void
    {
        $foreignCart = Cart::factory()->create();
        $product = Product::factory()->create();
        CartProduct::factory()->create(['cart_id' => $foreignCart->id]);

        $response = $this->postJson(save_route('change_product_count'), [
            'id'        => $product->id,
            'count'     => 1,
            'action'    => 'change_count',
            'cart_uuid' => $foreignCart->uuid,
        ]);

        $response->assertStatus(200);
        $response->assertJsonPath('success', true);
        $this->assertSame('location', $response->json('actions.0.method'));
        $this->assertDatabaseCount('shop_carts', 2);
    }

    public function test_invalid_action_returns_validation_error(): void
    {
        $product = Product::factory()->create();

        $response = $this->postJson(save_route('change_product_count'), [
            'id'     => $product->id,
            'count'  => 1,
            'action' => 'not_a_real_action',
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['action']);
    }

    public function test_nonexistent_product_id_returns_validation_error(): void
    {
        $response = $this->postJson(save_route('change_product_count'), [
            'id'     => 999999,
            'count'  => 1,
            'action' => 'change_count',
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['id']);
    }

    public function test_missing_required_fields_returns_validation_error(): void
    {
        $response = $this->postJson(save_route('change_product_count'), []);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['id', 'count', 'action']);
    }
}
