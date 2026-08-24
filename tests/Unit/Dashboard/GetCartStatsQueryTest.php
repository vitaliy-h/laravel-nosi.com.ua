<?php

namespace Tests\Unit\Dashboard;

use App\Modules\Core\Dtos\DashboardPeriodData;
use App\Modules\Core\Models\User;
use App\Modules\Shop\Models\Product;
use App\Modules\ShopCart\Models\Cart;
use App\Modules\ShopCart\Models\CartProduct;
use App\Modules\ShopCart\Queries\Cart\GetCartStatsQuery;
use App\Modules\ShopOrders\Models\Order;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

class GetCartStatsQueryTest extends TestCase
{
    use RefreshDatabase;

    private GetCartStatsQuery $query;

    protected function setUp(): void
    {
        parent::setUp();

        Carbon::setTestNow('2026-08-23 12:00:00');
        Cache::flush();

        $this->query = app(GetCartStatsQuery::class);
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();

        parent::tearDown();
    }

    public function test_empty_carts_are_ignored(): void
    {
        Cart::factory()->create(['total_amount' => 500, 'updated_at' => '2026-08-23 10:00:00']);

        $stats = $this->query->handle(DashboardPeriodData::fromFilters(['period' => '30']));

        $this->assertSame(0, $stats->activeCarts);
        $this->assertSame(0, $stats->abandonedCarts);
        $this->assertSame(0.0, $stats->cartsValue);
    }

    public function test_active_and_abandoned_carts_are_split_by_last_update(): void
    {
        $this->cartWithItems(100.00, '2026-08-23 10:00:00');
        $this->cartWithItems(200.00, '2026-08-20 10:00:00');

        $stats = $this->query->handle(DashboardPeriodData::fromFilters(['period' => '30']));

        $this->assertSame(1, $stats->activeCarts);
        $this->assertSame(1, $stats->abandonedCarts);
        $this->assertSame(300.0, $stats->cartsValue);
    }

    public function test_a_cart_followed_by_an_order_from_the_same_user_is_not_abandoned(): void
    {
        $user = User::factory()->create();

        $this->cartWithItems(150.00, '2026-08-19 10:00:00', $user);
        Order::factory()->create([
            'user_id' => $user->id,
            'created_at' => '2026-08-19 10:30:00',
            'updated_at' => '2026-08-19 10:30:00',
        ]);

        $stats = $this->query->handle(DashboardPeriodData::fromFilters(['period' => '30']));

        $this->assertSame(0, $stats->abandonedCarts);
    }

    public function test_conversion_is_orders_over_carts_of_the_period(): void
    {
        Cart::factory()->count(4)->create([
            'created_at' => '2026-08-20 10:00:00',
            'updated_at' => '2026-08-20 10:00:00',
        ]);
        Order::factory()->create([
            'created_at' => '2026-08-20 11:00:00',
            'updated_at' => '2026-08-20 11:00:00',
        ]);

        $stats = $this->query->handle(DashboardPeriodData::fromFilters(['period' => '30']));

        // 4 surviving carts + 1 order (PlaceOrderFromCartAction deletes the
        // cart on checkout, so that order was itself a 5th cart the carts
        // table no longer has a row for).
        $this->assertSame(5, $stats->cartsInPeriod);
        $this->assertSame(1, $stats->ordersInPeriod);
        $this->assertSame(20.0, $stats->conversion);
    }

    public function test_conversion_never_exceeds_100_percent_even_when_most_carts_already_converted(): void
    {
        // Regression: an order deletes its cart on checkout, so a period with
        // few surviving carts but many orders used to compute e.g. 10 orders
        // / 2 carts = 500%. Orders must count as carts too.
        Cart::factory()->count(2)->create([
            'created_at' => '2026-08-20 10:00:00',
            'updated_at' => '2026-08-20 10:00:00',
        ]);
        Order::factory()->count(10)->create([
            'created_at' => '2026-08-20 11:00:00',
            'updated_at' => '2026-08-20 11:00:00',
        ]);

        $stats = $this->query->handle(DashboardPeriodData::fromFilters(['period' => '30']));

        $this->assertSame(12, $stats->cartsInPeriod);
        $this->assertSame(10, $stats->ordersInPeriod);
        $this->assertSame(83.3, $stats->conversion);
    }

    public function test_conversion_is_null_when_there_were_no_carts(): void
    {
        $stats = $this->query->handle(DashboardPeriodData::fromFilters(['period' => '30']));

        $this->assertNull($stats->conversion);
    }

    public function test_an_order_with_no_surviving_cart_still_counts_as_a_converted_cart(): void
    {
        // No Cart row at all: it was deleted by PlaceOrderFromCartAction when
        // this order was placed. That's still 1 cart for the period, not 0.
        Order::factory()->create([
            'created_at' => '2026-08-20 11:00:00',
            'updated_at' => '2026-08-20 11:00:00',
        ]);

        $stats = $this->query->handle(DashboardPeriodData::fromFilters(['period' => '30']));

        $this->assertSame(1, $stats->cartsInPeriod);
        $this->assertSame(100.0, $stats->conversion);
    }

    private function cartWithItems(float $total, string $updatedAt, ?User $user = null): Cart
    {
        $cart = Cart::factory()->create(['user_id' => $user?->id]);

        // CartProductObserver recalculates and re-saves the cart (via a
        // separately fetched model instance) on item creation, which both
        // resets total_amount to the sum of item totals and bumps updated_at
        // to "now". Refresh $cart so its in-memory state matches the DB
        // before forcing the values this test actually wants — otherwise
        // Eloquent sees no change from the original (pre-recalculation)
        // in-memory attributes and silently skips them in the UPDATE.
        CartProduct::factory()->create([
            'cart_id' => $cart->id,
            'shop_product_id' => Product::factory(),
        ]);

        $cart->refresh()->forceFill([
            'total_amount' => $total,
            'created_at' => $updatedAt,
            'updated_at' => $updatedAt,
        ])->save();

        return $cart;
    }
}
