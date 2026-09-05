<?php

namespace Tests\Feature\ShopCart;

use App\Modules\ShopCart\Models\Cart;
use App\Modules\ShopCart\Models\CartProduct;
use App\Modules\ShopCart\Models\Promocode;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PromocodeCartTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seedPagesForRouting();
    }

    public function test_apply_valid_promocode_updates_cart_and_returns_actions(): void
    {
        $promocode = Promocode::factory()->fixed(20)->create(['code' => 'MINUS20']);
        $cart = $this->cartWithItemTotal(100);

        $response = $this->postJson(route('promocode.apply'), [
            'cart_uuid' => $cart->uuid,
            'code'      => 'MINUS20',
        ]);

        $response->assertOk();
        $response->assertJsonStructure(['actions' => [['method', 'selector']]]);
        $this->assertStringContainsString('js_summary_discount', $response->json('actions.1.content'));

        $this->assertDatabaseHas('shop_carts', [
            'id'              => $cart->id,
            'promocode_id'    => $promocode->id,
            'promocode_code'  => 'MINUS20',
            'subtotal_amount' => 100.00,
            'discount_amount' => 20.00,
            'total_amount'    => 80.00,
        ]);
    }

    public function test_apply_lower_case_promocode_is_accepted(): void
    {
        Promocode::factory()->fixed(20)->create(['code' => 'MINUS20']);
        $cart = $this->cartWithItemTotal(100);

        $this->postJson(route('promocode.apply'), [
            'cart_uuid' => $cart->uuid,
            'code'      => 'minus20',
        ])->assertOk();

        $this->assertDatabaseHas('shop_carts', [
            'id'             => $cart->id,
            'promocode_code' => 'MINUS20',
        ]);
    }

    public function test_apply_invalid_promocode_leaves_cart_untouched(): void
    {
        $cart = $this->cartWithItemTotal(100);

        $this->postJson(route('promocode.apply'), [
            'cart_uuid' => $cart->uuid,
            'code'      => 'NOSUCHCODE',
        ])->assertOk();

        $this->assertDatabaseHas('shop_carts', [
            'id'              => $cart->id,
            'promocode_id'    => null,
            'promocode_code'  => null,
            'discount_amount' => 0.00,
            'total_amount'    => 100.00,
        ]);
    }

    public function test_apply_requires_a_code(): void
    {
        $cart = $this->cartWithItemTotal(100);

        $this->postJson(route('promocode.apply'), [
            'cart_uuid' => $cart->uuid,
        ])->assertStatus(422);
    }

    public function test_remove_promocode_clears_discount(): void
    {
        $promocode = Promocode::factory()->fixed(20)->create(['code' => 'MINUS20']);
        $cart = $this->cartWithItemTotal(100, [
            'promocode_id'    => $promocode->id,
            'promocode_code'  => 'MINUS20',
            'discount_amount' => 20,
            'total_amount'    => 80,
        ]);

        $this->postJson(route('promocode.remove'), [
            'cart_uuid' => $cart->uuid,
        ])->assertOk();

        $this->assertDatabaseHas('shop_carts', [
            'id'              => $cart->id,
            'promocode_id'    => null,
            'promocode_code'  => null,
            'discount_amount' => 0.00,
            'total_amount'    => 100.00,
        ]);
    }

    public function test_changing_item_quantity_recalculates_the_discount(): void
    {
        $promocode = Promocode::factory()->percent(10)->create(['code' => 'TAKE10']);
        $cart = $this->cartWithItemTotal(100, [
            'promocode_id'   => $promocode->id,
            'promocode_code' => 'TAKE10',
        ]);

        $item = $cart->items()->first();
        $item->update(['quantity' => 2, 'total' => 200]);

        $cart->refresh();

        $this->assertSame('20.00', $cart->discount_amount);
        $this->assertSame('180.00', $cart->total_amount);
    }

    /**
     * @param  array<string, mixed>  $cartAttributes
     */
    private function cartWithItemTotal(float $total, array $cartAttributes = []): Cart
    {
        $cart = Cart::factory()->create($cartAttributes);

        CartProduct::factory()->create([
            'cart_id'       => $cart->id,
            'selling_price' => $total,
            'quantity'      => 1,
            'total'         => $total,
        ]);

        return $cart->refresh();
    }
}
