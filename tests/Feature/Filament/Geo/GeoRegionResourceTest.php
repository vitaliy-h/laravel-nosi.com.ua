<?php

namespace Tests\Feature\Filament\Geo;

use App\Filament\Resources\Geo\GeoRegions\Pages\ManageGeoRegions;
use App\Modules\Geo\Models\GeoCountry;
use App\Modules\Geo\Models\GeoRegion;
use App\Modules\User\Models\User;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Actions\Testing\TestAction;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use PHPUnit\Framework\Attributes\Group;
use Tests\TestCase;

#[Group('geo')]
class GeoRegionResourceTest extends TestCase
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
        GeoRegion::factory()->count(3)->create();

        Livewire::test(ManageGeoRegions::class)
            ->assertSuccessful();
    }

    public function test_list_can_search_by_title(): void
    {
        $target = GeoRegion::factory()->create(['title' => 'UniqueRegion']);
        $other = GeoRegion::factory()->create(['title' => 'AnotherRegion']);

        Livewire::test(ManageGeoRegions::class)
            ->searchTable('UniqueRegion')
            ->assertCanSeeTableRecords([$target])
            ->assertCanNotSeeTableRecords([$other]);
    }

    public function test_list_can_filter_by_country(): void
    {
        $country = GeoCountry::factory()->create();
        $other = GeoCountry::factory()->create();

        $inCountry = GeoRegion::factory()->forCountry($country)->create();
        $outCountry = GeoRegion::factory()->forCountry($other)->create();

        Livewire::test(ManageGeoRegions::class)
            ->filterTable('geo_country_id', $country)
            ->assertCanSeeTableRecords([$inCountry])
            ->assertCanNotSeeTableRecords([$outCountry]);
    }

    public function test_create_region_with_valid_data(): void
    {
        $country = GeoCountry::factory()->create();

        Livewire::test(ManageGeoRegions::class)
            ->callAction(TestAction::make(CreateAction::getDefaultName())->table(), data: [
                'title'          => 'New Region',
                'geo_country_id' => $country->id,
                'display'        => true,
            ])
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('geo_regions', ['title' => 'New Region']);
    }

    public function test_create_region_requires_title(): void
    {
        Livewire::test(ManageGeoRegions::class)
            ->callAction(TestAction::make(CreateAction::getDefaultName())->table(), data: [
                'display' => true,
            ])
            ->assertHasFormErrors(['title']);
    }

    public function test_edit_region_with_valid_data(): void
    {
        $region = GeoRegion::factory()->create(['title' => 'Old Region']);

        Livewire::test(ManageGeoRegions::class)
            ->callAction(TestAction::make(EditAction::getDefaultName())->table($region), data: [
                'title' => 'Updated Region',
            ])
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('geo_regions', ['id' => $region->id, 'title' => 'Updated Region']);
    }

    public function test_delete_region(): void
    {
        $region = GeoRegion::factory()->create();

        Livewire::test(ManageGeoRegions::class)
            ->callAction(TestAction::make(DeleteAction::getDefaultName())->table($region))
            ->assertSuccessful();

        $this->assertModelMissing($region);
    }
}
