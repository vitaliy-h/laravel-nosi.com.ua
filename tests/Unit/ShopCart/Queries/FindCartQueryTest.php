<?php

namespace Tests\Unit\ShopCart\Queries;

use App\Modules\ShopCart\Models\Cart;
use App\Modules\ShopCart\Queries\FindCartQuery;
use App\Modules\User\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FindCartQueryTest extends TestCase
{
    use RefreshDatabase;

    public function test_returns_null_for_guest_without_uuid(): void
    {
        $result = app(FindCartQuery::class)->handle(null);

        $this->assertNull($result);
    }

    public function test_returns_guest_cart_by_uuid(): void
    {
        $cart = Cart::factory()->create();

        $result = app(FindCartQuery::class)->handle(null, $cart->uuid);

        $this->assertSame($cart->id, $result->id);
    }

    public function test_ignores_uuid_belonging_to_a_users_cart(): void
    {
        $user = User::factory()->create();
        $cart = Cart::factory()->create(['user_id' => $user->id]);

        $result = app(FindCartQuery::class)->handle(null, $cart->uuid);

        $this->assertNull($result);
    }

    public function test_returns_users_cart_regardless_of_uuid(): void
    {
        $user = User::factory()->create();
        $cart = Cart::factory()->create(['user_id' => $user->id]);
        $guestCart = Cart::factory()->create();

        $result = app(FindCartQuery::class)->handle($user, $guestCart->uuid);

        $this->assertSame($cart->id, $result->id);
    }

    public function test_returns_null_when_user_has_no_cart(): void
    {
        $user = User::factory()->create();

        $result = app(FindCartQuery::class)->handle($user);

        $this->assertNull($result);
    }
}
