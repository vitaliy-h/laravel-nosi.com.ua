<?php

namespace Tests\Unit\ShopCart;

use App\Modules\ShopCart\Actions\DuplicateCartAction;
use App\Modules\ShopCart\Models\Cart;
use App\Modules\ShopCart\Models\CartProduct;
use App\Modules\Core\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Session;
use Tests\TestCase;

class DuplicateCartActionTest extends TestCase
{
    use RefreshDatabase;

    public function test_forks_items_from_source_cart_into_new_cart_for_guest(): void
    {
        $source = Cart::factory()->create();
        CartProduct::factory()->count(2)->create(['cart_id' => $source->id]);

        $result = app(DuplicateCartAction::class)->handle($source, null);

        $this->assertNotSame($source->id, $result->id);
        $this->assertNull($result->user_id);
        $this->assertDatabaseCount('shop_cart_products', 4);
        $this->assertSame(2, $result->items()->count());
        $this->assertSame($result->uuid, Session::get('cart_uuid'));
    }

    public function test_forks_items_for_authenticated_user(): void
    {
        $user = User::factory()->create();
        $source = Cart::factory()->create();
        CartProduct::factory()->create(['cart_id' => $source->id, 'total' => 15]);

        $result = app(DuplicateCartAction::class)->handle($source, $user);

        $this->assertSame($user->id, $result->user_id);
        $this->assertSame('15.00', (string) $result->total_amount);
    }

    public function test_deletes_existing_cart_for_target_before_forking(): void
    {
        $user = User::factory()->create();
        $existing = Cart::factory()->create(['user_id' => $user->id]);
        $source = Cart::factory()->create();
        CartProduct::factory()->create(['cart_id' => $source->id]);

        app(DuplicateCartAction::class)->handle($source, $user);

        $this->assertModelMissing($existing);
    }
}
