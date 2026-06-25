<?php

namespace Tests\Feature\Filament\Shop;

use App\Filament\Resources\Shop\Orders\Pages\ManageOrders;
use App\Modules\Core\Models\User;
use App\Modules\Shop\Enums\PaymentStatusEnum;
use App\Modules\Shop\Enums\PaymentTypeEnum;
use App\Modules\Shop\Models\Order;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Actions\Testing\TestAction;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class OrderResourceTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->admin = $this->actingAsAdmin();
        $this->actingAs($this->admin);
    }

    public function test_list_renders_table(): void
    {
        Order::factory()->count(3)->create();

        Livewire::test(ManageOrders::class)
            ->assertSuccessful();
    }

    public function test_list_can_search_by_order_number(): void
    {
        $target = Order::factory()->create(['order_number' => 1000001]);
        $other  = Order::factory()->create(['order_number' => 9999999]);

        Livewire::test(ManageOrders::class)
            ->searchTable('1000001')
            ->assertCanSeeTableRecords([$target])
            ->assertCanNotSeeTableRecords([$other]);
    }

    public function test_list_can_filter_by_payment_status(): void
    {
        $paid   = Order::factory()->create(['payment_status' => PaymentStatusEnum::PAID]);
        $unpaid = Order::factory()->create(['payment_status' => PaymentStatusEnum::UNPAID]);

        Livewire::test(ManageOrders::class)
            ->filterTable('payment_status', PaymentStatusEnum::PAID->value)
            ->assertCanSeeTableRecords([$paid])
            ->assertCanNotSeeTableRecords([$unpaid]);
    }

    public function test_list_can_filter_by_payment_type(): void
    {
        $online = Order::factory()->create(['payment_type' => PaymentTypeEnum::ONLINE_PAYMENT]);
        $manual = Order::factory()->create(['payment_type' => PaymentTypeEnum::MANUAL_PAYMENT]);

        Livewire::test(ManageOrders::class)
            ->filterTable('payment_type', PaymentTypeEnum::ONLINE_PAYMENT->value)
            ->assertCanSeeTableRecords([$online])
            ->assertCanNotSeeTableRecords([$manual]);
    }

    public function test_edit_order_payment_status(): void
    {
        $order = Order::factory()->create(['payment_status' => PaymentStatusEnum::UNPAID]);

        Livewire::test(ManageOrders::class)
            ->callAction(TestAction::make(EditAction::getDefaultName())->table($order), data: [
                'payment_status' => PaymentStatusEnum::PAID->value,
            ])
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('shop_orders', [
            'id'             => $order->id,
            'payment_status' => PaymentStatusEnum::PAID->value,
        ]);
    }

    public function test_delete_order(): void
    {
        $order = Order::factory()->create();

        Livewire::test(ManageOrders::class)
            ->callAction(TestAction::make(DeleteAction::getDefaultName())->table($order))
            ->assertSuccessful();

        $this->assertModelMissing($order);
    }
}
