<?php

namespace Tests\Unit\Shop;

use App\Modules\Orders\Models\Order;
use App\Modules\Orders\Repository\OrderRepository;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OrderRepositoryTest extends TestCase
{
    use RefreshDatabase;

    private OrderRepository $repository;

    protected function setUp(): void
    {
        parent::setUp();
        $this->repository = new OrderRepository();
    }

    public function test_find_by_uuid_returns_correct_order(): void
    {
        $order = Order::factory()->create();

        $found = $this->repository->findByUuid($order->uuid);

        $this->assertEquals($order->id, $found->id);
        $this->assertEquals($order->uuid, $found->uuid);
    }

    public function test_find_by_uuid_eager_loads_items_relation(): void
    {
        $order = Order::factory()->create();

        $found = $this->repository->findByUuid($order->uuid);

        $this->assertTrue($found->relationLoaded('items'));
    }

    public function test_find_by_uuid_does_not_return_other_orders(): void
    {
        $target = Order::factory()->create();
        Order::factory()->create();

        $found = $this->repository->findByUuid($target->uuid);

        $this->assertEquals($target->id, $found->id);
    }

    public function test_find_by_uuid_throws_when_uuid_not_found(): void
    {
        $this->expectException(ModelNotFoundException::class);

        $this->repository->findByUuid('00000000-0000-0000-0000-000000000000');
    }
}
