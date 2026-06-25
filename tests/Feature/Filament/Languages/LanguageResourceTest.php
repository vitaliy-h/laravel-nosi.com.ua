<?php

namespace Tests\Feature\Filament\Languages;

use App\Filament\Resources\Languages\Pages\ManageLanguages;
use App\Modules\Core\Models\User;
use App\Modules\Translation\Enums\Localization;
use App\Modules\Translation\Models\Language;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Actions\Testing\TestAction;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class LanguageResourceTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->admin = $this->actingAsAdmin();
        $this->actingAs($this->admin);
    }

    private function dictionaryFromFile(Localization $localization): array
    {
        $path = lang_path($localization->value . '/dictionary.php');

        if (!file_exists($path)) {
            $path = lang_path(Localization::EN->value . '/dictionary.php');
        }

        return $this->normalize(file_exists($path) ? require $path : []);
    }

    private function normalize(array $dictionary): array
    {
        $result = [];

        foreach ($dictionary as $key => $value) {
            $cleanKey = explode(':', $key)[0];
            $result[$cleanKey] = is_array($value) ? $this->normalize($value) : $value;
        }

        return $result;
    }

    public function test_list_renders_table(): void
    {
        Language::factory()->forLocalization(Localization::EN)->create();

        Livewire::test(ManageLanguages::class)
            ->assertSuccessful();
    }

    public function test_list_can_search_by_title(): void
    {
        $target = Language::factory()->forLocalization(Localization::EN)->create(['title' => 'English']);
        $other  = Language::factory()->forLocalization(Localization::UK)->create(['title' => 'Ukrainian']);

        Livewire::test(ManageLanguages::class)
            ->searchTable('English')
            ->assertCanSeeTableRecords([$target])
            ->assertCanNotSeeTableRecords([$other]);
    }

    public function test_create_language_with_valid_data(): void
    {
        $dictionary = $this->dictionaryFromFile(Localization::EN);

        Livewire::test(ManageLanguages::class)
            ->callAction(TestAction::make(CreateAction::getDefaultName())->table(), data: [
                'title'        => 'English',
                'localization' => Localization::EN->value,
                'rank'         => 1,
                'display'      => true,
                'dictionary'   => $dictionary,
            ])
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('languages', ['localization' => Localization::EN->value]);
    }

    public function test_create_language_requires_localization(): void
    {
        Livewire::test(ManageLanguages::class)
            ->callAction(TestAction::make(CreateAction::getDefaultName())->table(), data: [
                'title' => 'No Locale',
                'rank'  => 1,
            ])
            ->assertHasFormErrors(['localization']);
    }

    public function test_create_language_requires_unique_localization(): void
    {
        Language::factory()->forLocalization(Localization::EN)->create();

        Livewire::test(ManageLanguages::class)
            ->callAction(TestAction::make(CreateAction::getDefaultName())->table(), data: [
                'title'        => 'Duplicate',
                'localization' => Localization::EN->value,
                'rank'         => 1,
            ])
            ->assertHasFormErrors(['localization']);
    }

    public function test_edit_language_with_valid_data(): void
    {
        $language = Language::factory()->forLocalization(Localization::EN)->create(['title' => 'Old Title']);
        $dictionary = $this->dictionaryFromFile(Localization::EN);

        Livewire::test(ManageLanguages::class)
            ->callAction(TestAction::make(EditAction::getDefaultName())->table($language), data: [
                'title'      => 'Updated Title',
                'rank'       => 5,
                'dictionary' => $dictionary,
            ])
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('languages', ['id' => $language->id, 'title' => 'Updated Title']);
    }

    public function test_edit_language_stores_dictionary_from_file(): void
    {
        $language = Language::factory()->forLocalization(Localization::EN)->create();
        $dictionary = $this->dictionaryFromFile(Localization::EN);

        Livewire::test(ManageLanguages::class)
            ->callAction(TestAction::make(EditAction::getDefaultName())->table($language), data: [
                'title'      => $language->title,
                'rank'       => $language->rank,
                'dictionary' => $dictionary,
            ])
            ->assertHasNoFormErrors();

        $saved = Language::find($language->id);
        $this->assertArrayHasKey('index', $saved->dictionary);
        $this->assertArrayHasKey('feedback', $saved->dictionary);
    }

    public function test_delete_language(): void
    {
        $language = Language::factory()->forLocalization(Localization::EN)->create();

        Livewire::test(ManageLanguages::class)
            ->callAction(TestAction::make(DeleteAction::getDefaultName())->table($language))
            ->assertSuccessful();

        $this->assertModelMissing($language);
    }
}
