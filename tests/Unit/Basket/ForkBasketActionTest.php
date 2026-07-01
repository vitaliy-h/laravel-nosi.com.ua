<?php

namespace Tests\Unit\Basket;

use App\Modules\Basket\Actions\ForkBasketAction;
use App\Modules\Basket\Models\Basket;
use App\Modules\Basket\Models\BasketProduct;
use App\Modules\Core\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Session;
use Tests\TestCase;

class ForkBasketActionTest extends TestCase
{
    use RefreshDatabase;

    public function test_forks_items_from_source_basket_into_new_basket_for_guest(): void
    {
        $source = Basket::factory()->create();
        BasketProduct::factory()->count(2)->create(['basket_id' => $source->id]);

        $result = app(ForkBasketAction::class)->handle($source, null);

        $this->assertNotSame($source->id, $result->id);
        $this->assertNull($result->user_id);
        $this->assertDatabaseCount('basket_shop_products', 4);
        $this->assertSame(2, $result->items()->count());
        $this->assertSame($result->uuid, Session::get('basket_uuid'));
    }

    public function test_forks_items_for_authenticated_user(): void
    {
        $user = User::factory()->create();
        $source = Basket::factory()->create();
        BasketProduct::factory()->create(['basket_id' => $source->id, 'total' => 15]);

        $result = app(ForkBasketAction::class)->handle($source, $user);

        $this->assertSame($user->id, $result->user_id);
        $this->assertSame('15.00', (string) $result->total_amount);
    }

    public function test_deletes_existing_basket_for_target_before_forking(): void
    {
        $user = User::factory()->create();
        $existing = Basket::factory()->create(['user_id' => $user->id]);
        $source = Basket::factory()->create();
        BasketProduct::factory()->create(['basket_id' => $source->id]);

        app(ForkBasketAction::class)->handle($source, $user);

        $this->assertModelMissing($existing);
    }
}
