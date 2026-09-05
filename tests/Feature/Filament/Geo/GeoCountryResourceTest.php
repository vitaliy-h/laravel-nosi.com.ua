<?php

namespace Tests\Feature\Filament\Geo;

use App\Filament\Resources\Geo\GeoCountries\Pages\ManageGeoCountries;
use App\Modules\Geo\Models\GeoCountry;
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
class GeoCountryResourceTest extends TestCase
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
        GeoCountry::factory()->count(3)->create();

        Livewire::test(ManageGeoCountries::class)
            ->assertSuccessful();
    }

    public function test_list_can_search_by_title(): void
    {
        $target = GeoCountry::factory()->create(['title' => 'UniqueCountry']);
        $other = GeoCountry::factory()->create(['title' => 'AnotherLand']);

        Livewire::test(ManageGeoCountries::class)
            ->searchTable('UniqueCountry')
            ->assertCanSeeTableRecords([$target])
            ->assertCanNotSeeTableRecords([$other]);
    }

    public function test_list_can_search_by_iso2(): void
    {
        $target = GeoCountry::factory()->create(['iso2' => 'ZZ']);
        $other = GeoCountry::factory()->create(['iso2' => 'YY']);

        Livewire::test(ManageGeoCountries::class)
            ->searchTable('ZZ')
            ->assertCanSeeTableRecords([$target])
            ->assertCanNotSeeTableRecords([$other]);
    }

    public function test_create_country_with_valid_data(): void
    {
        Livewire::test(ManageGeoCountries::class)
            ->callAction(TestAction::make(CreateAction::getDefaultName())->table(), data: [
                'title'   => 'New Country',
                'display' => true,
            ])
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('geo_countries', ['title' => 'New Country']);
    }

    public function test_create_country_requires_title(): void
    {
        Livewire::test(ManageGeoCountries::class)
            ->callAction(TestAction::make(CreateAction::getDefaultName())->table(), data: [
                'iso2' => 'XX',
            ])
            ->assertHasFormErrors(['title']);
    }

    public function test_edit_country_with_valid_data(): void
    {
        $country = GeoCountry::factory()->create(['title' => 'Old Name']);

        Livewire::test(ManageGeoCountries::class)
            ->callAction(TestAction::make(EditAction::getDefaultName())->table($country), data: [
                'title' => 'Updated Name',
            ])
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('geo_countries', ['id' => $country->id, 'title' => 'Updated Name']);
    }

    public function test_delete_country(): void
    {
        $country = GeoCountry::factory()->create();

        Livewire::test(ManageGeoCountries::class)
            ->callAction(TestAction::make(DeleteAction::getDefaultName())->table($country))
            ->assertSuccessful();

        $this->assertModelMissing($country);
    }
}
