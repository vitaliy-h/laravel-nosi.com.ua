<?php

namespace Tests\Unit\Basket;

use App\Modules\Basket\Actions\FindBasketAction;
use App\Modules\Basket\Models\Basket;
use App\Modules\Core\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Session;
use Tests\TestCase;

class FindBasketActionTest extends TestCase
{
    use RefreshDatabase;

    public function test_returns_null_for_guest_without_session_uuid(): void
    {
        $result = app(FindBasketAction::class)->handle(null);

        $this->assertNull($result);
    }

    public function test_returns_guest_basket_by_session_uuid(): void
    {
        $basket = Basket::factory()->create();
        Session::put('basket_uuid', $basket->uuid);

        $result = app(FindBasketAction::class)->handle(null);

        $this->assertSame($basket->id, $result->id);
    }

    public function test_ignores_session_uuid_belonging_to_a_users_basket(): void
    {
        $user = User::factory()->create();
        $basket = Basket::factory()->create(['user_id' => $user->id]);
        Session::put('basket_uuid', $basket->uuid);

        $result = app(FindBasketAction::class)->handle(null);

        $this->assertNull($result);
    }

    public function test_returns_users_basket_regardless_of_session(): void
    {
        $user = User::factory()->create();
        $basket = Basket::factory()->create(['user_id' => $user->id]);

        $result = app(FindBasketAction::class)->handle($user);

        $this->assertSame($basket->id, $result->id);
    }

    public function test_returns_null_when_user_has_no_basket(): void
    {
        $user = User::factory()->create();

        $result = app(FindBasketAction::class)->handle($user);

        $this->assertNull($result);
    }
}
