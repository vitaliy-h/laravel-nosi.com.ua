<?php

namespace Tests\Feature\ShopCart;

use App\Modules\ShopCart\Models\Cart;
use App\Modules\Core\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ClearAbandonedCartsCommandTest extends TestCase
{
    use RefreshDatabase;

    public function test_deletes_guest_carts_older_than_given_days(): void
    {
        $old = Cart::factory()->create(['created_at' => now()->subDays(40)]);
        $recent = Cart::factory()->create(['created_at' => now()->subDays(5)]);

        $this->artisan('cart:clear-abandoned', ['days' => 30])->assertSuccessful();

        $this->assertModelMissing($old);
        $this->assertModelExists($recent);
    }

    public function test_keeps_user_carts_regardless_of_age(): void
    {
        $user = User::factory()->create();
        $userCart = Cart::factory()->create(['user_id' => $user->id, 'created_at' => now()->subDays(60)]);

        $this->artisan('cart:clear-abandoned', ['days' => 30])->assertSuccessful();

        $this->assertModelExists($userCart);
    }

    public function test_uses_default_thirty_days_when_argument_omitted(): void
    {
        $old = Cart::factory()->create(['created_at' => now()->subDays(31)]);

        $this->artisan('cart:clear-abandoned')->assertSuccessful();

        $this->assertModelMissing($old);
    }
}
