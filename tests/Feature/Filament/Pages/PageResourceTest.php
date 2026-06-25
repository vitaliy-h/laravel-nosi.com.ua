<?php

namespace Tests\Feature\Filament\Pages;

use App\Filament\Resources\Pages\Pages\ManagePages;
use App\Modules\Core\Enums\PageModule;
use App\Modules\Core\Models\Page;
use App\Modules\Core\Models\User;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Actions\Testing\TestAction;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class PageResourceTest extends TestCase
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
        Page::factory()->count(3)->create();

        Livewire::test(ManagePages::class)
            ->assertSuccessful();
    }

    public function test_list_can_search_by_title(): void
    {
        $target = Page::factory()->create(['title' => 'Unique Page Title']);
        $other = Page::factory()->create(['title' => 'Another Page']);

        Livewire::test(ManagePages::class)
            ->searchTable('Unique Page Title')
            ->assertCanSeeTableRecords([$target])
            ->assertCanNotSeeTableRecords([$other]);
    }

    public function test_list_can_search_by_slug(): void
    {
        $target = Page::factory()->withSlug('find-this-slug')->create();
        $other = Page::factory()->withSlug('other-slug')->create();

        Livewire::test(ManagePages::class)
            ->searchTable('find-this-slug')
            ->assertCanSeeTableRecords([$target])
            ->assertCanNotSeeTableRecords([$other]);
    }

    public function test_create_page_with_valid_data(): void
    {
        Livewire::test(ManagePages::class)
            ->callAction(TestAction::make(CreateAction::getDefaultName())->table(), data: [
                'title'   => 'New Page',
                'module'  => PageModule::TEXT->value,
                'rank'    => 1,
                'display' => true,
            ])
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('pages', ['title' => 'New Page']);
    }

    public function test_create_page_requires_title(): void
    {
        Livewire::test(ManagePages::class)
            ->callAction(TestAction::make(CreateAction::getDefaultName())->table(), data: [
                'module' => PageModule::TEXT->value,
            ])
            ->assertHasFormErrors(['title']);
    }

    public function test_edit_page_with_valid_data(): void
    {
        $page = Page::factory()->create(['title' => 'Old Title']);

        Livewire::test(ManagePages::class)
            ->callAction(TestAction::make(EditAction::getDefaultName())->table($page), data: [
                'title'  => 'Updated Title',
                'module' => PageModule::TEXT->value,
            ])
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('pages', ['id' => $page->id, 'title' => 'Updated Title']);
    }

    public function test_delete_text_page(): void
    {
        $page = Page::factory()->create(['module' => PageModule::TEXT]);

        Livewire::test(ManagePages::class)
            ->callAction(TestAction::make(DeleteAction::getDefaultName())->table($page))
            ->assertSuccessful();

        $this->assertModelMissing($page);
    }

    public function test_delete_disabled_for_non_text_module(): void
    {
        $page = Page::factory()->forModule(PageModule::NEWS)->create();

        Livewire::test(ManagePages::class)
            ->assertActionDisabled(
                TestAction::make(DeleteAction::getDefaultName())->table($page)
            );

        $this->assertModelExists($page);
    }
}
