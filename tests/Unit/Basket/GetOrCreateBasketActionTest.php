<?php

namespace Tests\Unit\Basket;

use App\Modules\Basket\Actions\GetOrCreateBasketAction;
use App\Modules\Basket\Models\Basket;
use App\Modules\Core\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Session;
use Tests\TestCase;

class GetOrCreateBasketActionTest extends TestCase
{
    use RefreshDatabase;

    public function test_returns_existing_user_basket(): void
    {
        $user = User::factory()->create();
        $basket = Basket::factory()->create(['user_id' => $user->id]);

        $result = app(GetOrCreateBasketAction::class)->handle($user);

        $this->assertSame($basket->id, $result->id);
        $this->assertSame($basket->uuid, Session::get('basket_uuid'));
    }

    public function test_attaches_matching_session_basket_to_user_without_basket(): void
    {
        $user = User::factory()->create();
        $sessionBasket = Basket::factory()->create();
        Session::put('basket_uuid', $sessionBasket->uuid);

        $result = app(GetOrCreateBasketAction::class)->handle($user);

        $this->assertSame($sessionBasket->id, $result->id);
    }

    public function test_creates_new_basket_for_user_without_basket_or_session(): void
    {
        $user = User::factory()->create();

        $result = app(GetOrCreateBasketAction::class)->handle($user);

        $this->assertDatabaseHas('shop_baskets', ['id' => $result->id, 'user_id' => $user->id]);
        $this->assertSame($result->uuid, Session::get('basket_uuid'));
    }

    public function test_ignores_session_basket_owned_by_another_user(): void
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();
        $otherBasket = Basket::factory()->create(['user_id' => $otherUser->id]);
        Session::put('basket_uuid', $otherBasket->uuid);

        $result = app(GetOrCreateBasketAction::class)->handle($user);

        $this->assertNotSame($otherBasket->id, $result->id);
        $this->assertSame($user->id, $result->user_id);
    }

    public function test_guest_reuses_matching_session_basket(): void
    {
        $basket = Basket::factory()->create();
        Session::put('basket_uuid', $basket->uuid);

        $result = app(GetOrCreateBasketAction::class)->handle(null);

        $this->assertSame($basket->id, $result->id);
    }

    public function test_guest_without_session_gets_new_basket(): void
    {
        $result = app(GetOrCreateBasketAction::class)->handle(null);

        $this->assertDatabaseHas('shop_baskets', ['id' => $result->id, 'user_id' => null]);
        $this->assertSame($result->uuid, Session::get('basket_uuid'));
    }
}
