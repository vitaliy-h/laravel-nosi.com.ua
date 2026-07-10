<?php

namespace Tests\Unit\ShopOrders\Queries;

use App\Modules\ShopOrders\Models\Order;
use App\Modules\ShopOrders\Queries\FindOrderByUuidQuery;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FindOrderByUuidQueryTest extends TestCase
{
    use RefreshDatabase;

    private FindOrderByUuidQuery $query;

    protected function setUp(): void
    {
        parent::setUp();
        $this->query = new FindOrderByUuidQuery;
    }

    public function test_find_by_uuid_returns_correct_order(): void
    {
        $order = Order::factory()->create();

        $found = $this->query->handle($order->uuid);

        $this->assertEquals($order->id, $found->id);
        $this->assertEquals($order->uuid, $found->uuid);
    }

    public function test_find_by_uuid_eager_loads_items_relation(): void
    {
        $order = Order::factory()->create();

        $found = $this->query->handle($order->uuid);

        $this->assertTrue($found->relationLoaded('items'));
    }

    public function test_find_by_uuid_does_not_return_other_orders(): void
    {
        $target = Order::factory()->create();
        Order::factory()->create();

        $found = $this->query->handle($target->uuid);

        $this->assertEquals($target->id, $found->id);
    }

    public function test_find_by_uuid_throws_when_uuid_not_found(): void
    {
        $this->expectException(ModelNotFoundException::class);

        $this->query->handle('00000000-0000-0000-0000-000000000000');
    }
}
