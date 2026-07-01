<?php

namespace Tests\Feature\Basket;

use App\Modules\Basket\Models\Basket;
use App\Modules\Basket\Models\BasketProduct;
use App\Modules\Core\Models\User;
use App\Modules\Shop\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AjaxBasketControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_change_count_creates_item_for_guest(): void
    {
        $product = Product::factory()->create();

        $response = $this->postJson('/basket/change', [
            'id' => $product->id,
            'count' => 2,
            'action' => 'change_count',
        ]);

        $response->assertStatus(200);
        $response->assertJsonPath('success', true);
        $this->assertDatabaseHas('basket_shop_products', [
            'shop_product_id' => $product->id,
            'quantity' => 2,
        ]);
    }

    public function test_change_count_for_authenticated_user_uses_their_basket(): void
    {
        $user = User::factory()->create();
        $product = Product::factory()->create();

        $response = $this->actingAs($user)->postJson('/basket/change', [
            'id' => $product->id,
            'count' => 1,
            'action' => 'change_count',
        ]);

        $response->assertStatus(200);
        $basket = $user->fresh()->basket;
        $this->assertNotNull($basket);
        $this->assertDatabaseHas('basket_shop_products', [
            'basket_id' => $basket->id,
            'shop_product_id' => $product->id,
        ]);
    }

    public function test_delete_modal_removes_item_and_returns_empty_content(): void
    {
        $basket = Basket::factory()->create();
        $product = Product::factory()->create();
        BasketProduct::factory()->create(['basket_id' => $basket->id, 'shop_product_id' => $product->id]);
        session(['basket_uuid' => $basket->uuid]);

        $response = $this->withSession(['basket_uuid' => $basket->uuid])->postJson('/basket/change', [
            'id' => $product->id,
            'count' => 1,
            'action' => 'delete_modal',
        ]);

        $response->assertStatus(200);
        $response->assertJsonPath('success', true);
        $this->assertDatabaseMissing('basket_shop_products', ['basket_id' => $basket->id, 'shop_product_id' => $product->id]);
    }

    public function test_delete_checkout_returns_updated_total_action(): void
    {
        $basket = Basket::factory()->create();
        $product = Product::factory()->create();
        BasketProduct::factory()->create(['basket_id' => $basket->id, 'shop_product_id' => $product->id, 'total' => 10]);

        $response = $this->withSession(['basket_uuid' => $basket->uuid])->postJson('/basket/change', [
            'id' => $product->id,
            'count' => 1,
            'action' => 'delete_checkout',
        ]);

        $response->assertStatus(200);
        $actions = $response->json('actions');
        $this->assertTrue(collect($actions)->contains(fn ($a) => $a['selector'] === '.js_checkout_total' && $a['content'] === '0.00'));
    }

    public function test_change_count_zero_is_clamped_to_minimum_one(): void
    {
        $product = Product::factory()->create();

        $response = $this->postJson('/basket/change', [
            'id' => $product->id,
            'count' => 0,
            'action' => 'change_count',
        ]);

        $response->assertStatus(200);
        $this->assertDatabaseHas('basket_shop_products', [
            'shop_product_id' => $product->id,
            'quantity' => 1,
        ]);
    }

    public function test_viewing_own_basket_by_uuid_does_not_fork(): void
    {
        $basket = Basket::factory()->create();
        $product = Product::factory()->create();

        $response = $this->withSession(['basket_uuid' => $basket->uuid])->postJson('/basket/change', [
            'id' => $product->id,
            'count' => 1,
            'action' => 'change_count',
            'basket_uuid' => $basket->uuid,
        ]);

        $response->assertStatus(200);
        $this->assertDatabaseCount('baskets', 1);
    }

    public function test_viewing_foreign_basket_forks_and_returns_redirect_action(): void
    {
        $foreignBasket = Basket::factory()->create();
        $product = Product::factory()->create();
        BasketProduct::factory()->create(['basket_id' => $foreignBasket->id]);

        $response = $this->postJson('/basket/change', [
            'id' => $product->id,
            'count' => 1,
            'action' => 'change_count',
            'basket_uuid' => $foreignBasket->uuid,
        ]);

        $response->assertStatus(200);
        $response->assertJsonPath('success', true);
        $this->assertSame('location', $response->json('actions.0.method'));
        $this->assertDatabaseCount('baskets', 2);
    }

    public function test_invalid_action_returns_validation_error(): void
    {
        $product = Product::factory()->create();

        $response = $this->postJson('/basket/change', [
            'id' => $product->id,
            'count' => 1,
            'action' => 'not_a_real_action',
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['action']);
    }

    public function test_nonexistent_product_id_returns_validation_error(): void
    {
        $response = $this->postJson('/basket/change', [
            'id' => 999999,
            'count' => 1,
            'action' => 'change_count',
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['id']);
    }

    public function test_missing_required_fields_returns_validation_error(): void
    {
        $response = $this->postJson('/basket/change', []);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['id', 'count', 'action']);
    }
}
