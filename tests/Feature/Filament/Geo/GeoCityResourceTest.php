<?php

namespace Tests\Feature\Filament\Geo;

use PHPUnit\Framework\Attributes\Group;

use App\Filament\Resources\Geo\GeoCities\Pages\ManageGeoCities;
use App\Modules\User\Models\User;
use App\Modules\Core\Support\ModuleGate;
use App\Modules\Geo\Models\GeoCity;
use App\Modules\Geo\Models\GeoCountry;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Actions\Testing\TestAction;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

#[Group('geo')]
class GeoCityResourceTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->skipUnlessModules('geo');

        $this->admin = $this->actingAsAdmin();
        $this->actingAs($this->admin);
    }

    public function test_list_renders_table(): void
    {
        GeoCity::factory()->count(3)->create();

        Livewire::test(ManageGeoCities::class)
            ->assertSuccessful();
    }

    public function test_list_can_search_by_title(): void
    {
        $target = GeoCity::factory()->create(['title' => 'UniqueCity']);
        $other  = GeoCity::factory()->create(['title' => 'AnotherCity']);

        Livewire::test(ManageGeoCities::class)
            ->searchTable('UniqueCity')
            ->assertCanSeeTableRecords([$target])
            ->assertCanNotSeeTableRecords([$other]);
    }

    public function test_list_can_filter_by_country(): void
    {
        $country = GeoCountry::factory()->create();
        $other   = GeoCountry::factory()->create();

        $inCountry  = GeoCity::factory()->forCountry($country)->create();
        $outCountry = GeoCity::factory()->forCountry($other)->create();

        Livewire::test(ManageGeoCities::class)
            ->filterTable('geo_location', ['geo_country_id' => $country->id, 'geo_region_id' => null])
            ->assertCanSeeTableRecords([$inCountry])
            ->assertCanNotSeeTableRecords([$outCountry]);
    }

    public function test_create_city_with_valid_data(): void
    {
        $country = GeoCountry::factory()->create();

        Livewire::test(ManageGeoCities::class)
            ->callAction(TestAction::make(CreateAction::getDefaultName())->table(), data: [
                'title'          => 'New City',
                'geo_country_id' => $country->id,
                'display'        => true,
            ])
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('geo_cities', ['title' => 'New City']);
    }

    public function test_create_city_requires_title(): void
    {
        Livewire::test(ManageGeoCities::class)
            ->callAction(TestAction::make(CreateAction::getDefaultName())->table(), data: [
                'display' => true,
            ])
            ->assertHasFormErrors(['title']);
    }

    public function test_edit_city_with_valid_data(): void
    {
        $city = GeoCity::factory()->create(['title' => 'Old City']);

        Livewire::test(ManageGeoCities::class)
            ->callAction(TestAction::make(EditAction::getDefaultName())->table($city), data: [
                'title' => 'Updated City',
            ])
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('geo_cities', ['id' => $city->id, 'title' => 'Updated City']);
    }

    public function test_delete_city(): void
    {
        $city = GeoCity::factory()->create();

        Livewire::test(ManageGeoCities::class)
            ->callAction(TestAction::make(DeleteAction::getDefaultName())->table($city))
            ->assertSuccessful();

        $this->assertModelMissing($city);
    }
}
