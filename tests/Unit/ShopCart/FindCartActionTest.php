<?php

namespace Tests\Unit\ShopCart;

use App\Modules\ShopCart\Actions\FindCartAction;
use App\Modules\ShopCart\Models\Cart;
use App\Modules\Core\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Session;
use Tests\TestCase;

class FindCartActionTest extends TestCase
{
    use RefreshDatabase;

    public function test_returns_null_for_guest_without_session_uuid(): void
    {
        $result = app(FindCartAction::class)->handle(null);

        $this->assertNull($result);
    }

    public function test_returns_guest_cart_by_session_uuid(): void
    {
        $cart = Cart::factory()->create();
        Session::put('cart_uuid', $cart->uuid);

        $result = app(FindCartAction::class)->handle(null);

        $this->assertSame($cart->id, $result->id);
    }

    public function test_ignores_session_uuid_belonging_to_a_users_cart(): void
    {
        $user = User::factory()->create();
        $cart = Cart::factory()->create(['user_id' => $user->id]);
        Session::put('cart_uuid', $cart->uuid);

        $result = app(FindCartAction::class)->handle(null);

        $this->assertNull($result);
    }

    public function test_returns_users_cart_regardless_of_session(): void
    {
        $user = User::factory()->create();
        $cart = Cart::factory()->create(['user_id' => $user->id]);

        $result = app(FindCartAction::class)->handle($user);

        $this->assertSame($cart->id, $result->id);
    }

    public function test_returns_null_when_user_has_no_cart(): void
    {
        $user = User::factory()->create();

        $result = app(FindCartAction::class)->handle($user);

        $this->assertNull($result);
    }
}
