<?php

namespace Tests\Unit\ShopCart;

use App\Modules\ShopCart\Actions\ValidatePromocodeAction;
use App\Modules\ShopCart\Dtos\PromocodeData;
use App\Modules\ShopCart\Models\Promocode;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ValidatePromocodeActionTest extends TestCase
{
    use RefreshDatabase;

    private ValidatePromocodeAction $action;

    protected function setUp(): void
    {
        parent::setUp();

        $this->action = app(ValidatePromocodeAction::class);
    }

    public function test_unknown_code_fails(): void
    {
        $this->assertFalse($this->action->handle('NOPE', 100.0)->success);
    }

    public function test_disabled_code_fails(): void
    {
        Promocode::factory()->invisible()->create(['code' => 'HIDDEN']);

        $this->assertFalse($this->action->handle('HIDDEN', 100.0)->success);
    }

    public function test_expired_code_fails(): void
    {
        Promocode::factory()->expired()->create(['code' => 'OLD']);

        $this->assertFalse($this->action->handle('OLD', 100.0)->success);
    }

    public function test_not_yet_started_code_fails(): void
    {
        Promocode::factory()->notStarted()->create(['code' => 'SOON']);

        $this->assertFalse($this->action->handle('SOON', 100.0)->success);
    }

    public function test_exhausted_code_fails(): void
    {
        Promocode::factory()->exhausted()->create(['code' => 'USEDUP']);

        $this->assertFalse($this->action->handle('USEDUP', 100.0)->success);
    }

    public function test_code_below_min_order_amount_fails(): void
    {
        Promocode::factory()->fixed(10)->withMinOrder(100)->create(['code' => 'BIG']);

        $this->assertFalse($this->action->handle('BIG', 50.0)->success);
    }

    public function test_valid_code_returns_discount(): void
    {
        $promocode = Promocode::factory()->percent(10)->create(['code' => 'TAKE10']);

        $result = $this->action->handle('TAKE10', 200.0);

        $this->assertTrue($result->success);
        $this->assertInstanceOf(PromocodeData::class, $result->data);
        $this->assertSame($promocode->id, $result->data->promocode_id);
        $this->assertSame('TAKE10', $result->data->code);
        $this->assertSame(20.0, $result->data->discount);
    }

    public function test_failed_result_carries_no_payload(): void
    {
        $result = $this->action->handle('NOPE', 100.0);

        $this->assertFalse($result->success);
        $this->assertNull($result->data);
        $this->assertNotNull($result->message);
    }

    public function test_code_lookup_is_case_insensitive(): void
    {
        Promocode::factory()->fixed(5)->create(['code' => 'LOWER']);

        $this->assertTrue($this->action->handle(' lower ', 100.0)->success);
    }
}
