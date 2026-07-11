<?php

namespace Tests\Unit\ShopCart;

use App\Modules\ShopCart\Actions\GetOrCreateCartAction;
use App\Modules\ShopCart\Models\Cart;
use App\Modules\Core\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Session;
use Tests\TestCase;

class GetOrCreateCartActionTest extends TestCase
{
    use RefreshDatabase;

    public function test_returns_existing_user_cart(): void
    {
        $user = User::factory()->create();
        $cart = Cart::factory()->create(['user_id' => $user->id]);

        $result = app(GetOrCreateCartAction::class)->handle($user);

        $this->assertSame($cart->id, $result->id);
        $this->assertSame($cart->uuid, Session::get('cart_uuid'));
    }

    public function test_attaches_matching_session_cart_to_user_without_cart(): void
    {
        $user = User::factory()->create();
        $sessionCart = Cart::factory()->create();
        Session::put('cart_uuid', $sessionCart->uuid);

        $result = app(GetOrCreateCartAction::class)->handle($user);

        $this->assertSame($sessionCart->id, $result->id);
    }

    public function test_creates_new_cart_for_user_without_cart_or_session(): void
    {
        $user = User::factory()->create();

        $result = app(GetOrCreateCartAction::class)->handle($user);

        $this->assertDatabaseHas('shop_carts', ['id' => $result->id, 'user_id' => $user->id]);
        $this->assertSame($result->uuid, Session::get('cart_uuid'));
    }

    public function test_ignores_session_cart_owned_by_another_user(): void
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();
        $otherCart = Cart::factory()->create(['user_id' => $otherUser->id]);
        Session::put('cart_uuid', $otherCart->uuid);

        $result = app(GetOrCreateCartAction::class)->handle($user);

        $this->assertNotSame($otherCart->id, $result->id);
        $this->assertSame($user->id, $result->user_id);
    }

    public function test_guest_reuses_matching_session_cart(): void
    {
        $cart = Cart::factory()->create();
        Session::put('cart_uuid', $cart->uuid);

        $result = app(GetOrCreateCartAction::class)->handle(null);

        $this->assertSame($cart->id, $result->id);
    }

    public function test_guest_without_session_gets_new_cart(): void
    {
        $result = app(GetOrCreateCartAction::class)->handle(null);

        $this->assertDatabaseHas('shop_carts', ['id' => $result->id, 'user_id' => null]);
        $this->assertSame($result->uuid, Session::get('cart_uuid'));
    }
}
