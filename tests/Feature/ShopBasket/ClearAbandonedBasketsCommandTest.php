<?php

namespace Tests\Feature\ShopBasket;

use App\Modules\ShopBasket\Models\Basket;
use App\Modules\Core\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ClearAbandonedBasketsCommandTest extends TestCase
{
    use RefreshDatabase;

    public function test_deletes_guest_baskets_older_than_given_days(): void
    {
        $old = Basket::factory()->create(['created_at' => now()->subDays(40)]);
        $recent = Basket::factory()->create(['created_at' => now()->subDays(5)]);

        $this->artisan('basket:clear-abandoned', ['days' => 30])->assertSuccessful();

        $this->assertModelMissing($old);
        $this->assertModelExists($recent);
    }

    public function test_keeps_user_baskets_regardless_of_age(): void
    {
        $user = User::factory()->create();
        $userBasket = Basket::factory()->create(['user_id' => $user->id, 'created_at' => now()->subDays(60)]);

        $this->artisan('basket:clear-abandoned', ['days' => 30])->assertSuccessful();

        $this->assertModelExists($userBasket);
    }

    public function test_uses_default_thirty_days_when_argument_omitted(): void
    {
        $old = Basket::factory()->create(['created_at' => now()->subDays(31)]);

        $this->artisan('basket:clear-abandoned')->assertSuccessful();

        $this->assertModelMissing($old);
    }
}
