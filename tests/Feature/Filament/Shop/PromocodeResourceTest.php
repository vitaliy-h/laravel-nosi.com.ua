<?php

namespace Tests\Feature\Filament\Shop;

use App\Filament\Resources\Shop\Promocodes\Pages\ManagePromocodes;
use App\Modules\ShopCart\Enums\PromocodeTypeEnum;
use App\Modules\ShopCart\Models\Promocode;
use App\Modules\User\Models\User;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Actions\Testing\TestAction;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class PromocodeResourceTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->skipUnlessModules('shop_promocodes');

        $this->admin = $this->actingAsAdmin();
        $this->actingAs($this->admin);
    }

    public function test_list_renders_table(): void
    {
        Promocode::factory()->count(3)->create();

        Livewire::test(ManagePromocodes::class)
            ->assertSuccessful();
    }

    public function test_list_can_search_by_code(): void
    {
        $target = Promocode::factory()->create(['code' => 'FINDME']);
        $other = Promocode::factory()->create(['code' => 'OTHER']);

        Livewire::test(ManagePromocodes::class)
            ->searchTable('FINDME')
            ->assertCanSeeTableRecords([$target])
            ->assertCanNotSeeTableRecords([$other]);
    }

    public function test_list_can_filter_by_type(): void
    {
        $fixed = Promocode::factory()->fixed()->create();
        $percent = Promocode::factory()->percent()->create();

        Livewire::test(ManagePromocodes::class)
            ->filterTable('type', PromocodeTypeEnum::FIXED->value)
            ->assertCanSeeTableRecords([$fixed])
            ->assertCanNotSeeTableRecords([$percent]);
    }

    public function test_list_can_filter_by_active(): void
    {
        $active = Promocode::factory()->create();
        $expired = Promocode::factory()->expired()->create();

        Livewire::test(ManagePromocodes::class)
            ->filterTable('active')
            ->assertCanSeeTableRecords([$active])
            ->assertCanNotSeeTableRecords([$expired]);
    }

    public function test_create_promocode_with_valid_data(): void
    {
        Livewire::test(ManagePromocodes::class)
            ->callAction(TestAction::make(CreateAction::getDefaultName())->table(), data: [
                'code'    => 'NEWCODE',
                'type'    => PromocodeTypeEnum::PERCENT->value,
                'value'   => 15,
                'display' => true,
            ])
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('shop_promocodes', [
            'code' => 'NEWCODE',
            'type' => PromocodeTypeEnum::PERCENT->value,
        ]);
    }

    public function test_create_promocode_requires_code(): void
    {
        Livewire::test(ManagePromocodes::class)
            ->callAction(TestAction::make(CreateAction::getDefaultName())->table(), data: [
                'type'  => PromocodeTypeEnum::FIXED->value,
                'value' => 10,
            ])
            ->assertHasFormErrors(['code']);
    }

    public function test_edit_promocode_with_valid_data(): void
    {
        $promocode = Promocode::factory()->create(['code' => 'OLDCODE', 'value' => 5]);

        Livewire::test(ManagePromocodes::class)
            ->callAction(TestAction::make(EditAction::getDefaultName())->table($promocode), data: [
                'code'  => 'OLDCODE',
                'type'  => PromocodeTypeEnum::FIXED->value,
                'value' => 25,
            ])
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('shop_promocodes', [
            'id'    => $promocode->id,
            'value' => 25.00,
        ]);
    }

    public function test_delete_promocode(): void
    {
        $promocode = Promocode::factory()->create();

        Livewire::test(ManagePromocodes::class)
            ->callAction(TestAction::make(DeleteAction::getDefaultName())->table($promocode))
            ->assertSuccessful();

        $this->assertModelMissing($promocode);
    }
}
