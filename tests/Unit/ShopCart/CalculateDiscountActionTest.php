<?php

namespace Tests\Unit\ShopCart;

use App\Modules\ShopCart\Actions\CalculateDiscountAction;
use App\Modules\ShopCart\Models\Promocode;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CalculateDiscountActionTest extends TestCase
{
    use RefreshDatabase;

    private CalculateDiscountAction $action;

    protected function setUp(): void
    {
        parent::setUp();

        $this->action = app(CalculateDiscountAction::class);
    }

    public function test_fixed_promocode_returns_its_value(): void
    {
        $promocode = Promocode::factory()->fixed(20)->create();

        $this->assertSame(20.0, $this->action->handle($promocode, 100.0));
    }

    public function test_percent_promocode_returns_share_of_subtotal(): void
    {
        $promocode = Promocode::factory()->percent(10)->create();

        $this->assertSame(12.35, $this->action->handle($promocode, 123.45));
    }

    public function test_percent_promocode_is_capped_by_max_discount(): void
    {
        $promocode = Promocode::factory()->percent(50)->create(['max_discount' => 30]);

        $this->assertSame(30.0, $this->action->handle($promocode, 200.0));
    }

    public function test_discount_never_exceeds_subtotal(): void
    {
        $promocode = Promocode::factory()->fixed(100)->create();

        $this->assertSame(40.0, $this->action->handle($promocode, 40.0));
    }

    public function test_discount_is_never_negative(): void
    {
        $promocode = Promocode::factory()->fixed(20)->create();

        $this->assertSame(0.0, $this->action->handle($promocode, 0.0));
    }
}
