<?php

namespace Tests\Unit\ShopCart;

use App\Modules\ShopCart\Actions\GetOrCreateCartAction;
use App\Modules\ShopCart\Models\Cart;
use App\Modules\User\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * The action takes the guest cart uuid as an argument and never writes it back —
 * storing it in the session is CartContext's job, covered in CartContextTest.
 */
class GetOrCreateCartActionTest extends TestCase
{
    use RefreshDatabase;

    public function test_returns_existing_user_cart(): void
    {
        $user = User::factory()->create();
        $cart = Cart::factory()->create(['user_id' => $user->id]);

        $result = app(GetOrCreateCartAction::class)->handle($user);

        $this->assertSame($cart->id, $result->id);
    }

    public function test_attaches_matching_guest_cart_to_user_without_cart(): void
    {
        $user = User::factory()->create();
        $guestCart = Cart::factory()->create();

        $result = app(GetOrCreateCartAction::class)->handle($user, $guestCart->uuid);

        $this->assertSame($guestCart->id, $result->id);
    }

    public function test_creates_new_cart_for_user_without_cart_or_uuid(): void
    {
        $user = User::factory()->create();

        $result = app(GetOrCreateCartAction::class)->handle($user);

        $this->assertDatabaseHas('shop_carts', ['id' => $result->id, 'user_id' => $user->id]);
    }

    public function test_ignores_guest_cart_owned_by_another_user(): void
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();
        $otherCart = Cart::factory()->create(['user_id' => $otherUser->id]);

        $result = app(GetOrCreateCartAction::class)->handle($user, $otherCart->uuid);

        $this->assertNotSame($otherCart->id, $result->id);
        $this->assertSame($user->id, $result->user_id);
    }

    public function test_guest_reuses_matching_cart(): void
    {
        $cart = Cart::factory()->create();

        $result = app(GetOrCreateCartAction::class)->handle(null, $cart->uuid);

        $this->assertSame($cart->id, $result->id);
    }

    public function test_guest_without_uuid_gets_new_cart(): void
    {
        $result = app(GetOrCreateCartAction::class)->handle(null);

        $this->assertDatabaseHas('shop_carts', ['id' => $result->id, 'user_id' => null]);
    }
}
