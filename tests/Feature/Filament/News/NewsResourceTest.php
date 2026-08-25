<?php

namespace Tests\Feature\Filament\News;

use App\Filament\Resources\News\Pages\ManageNews;
use App\Modules\Core\Models\User;
use App\Modules\Core\Support\ModuleGate;
use App\Modules\News\Models\News;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Actions\Testing\TestAction;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class NewsResourceTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->skipUnlessModules('news');

        $this->admin = $this->actingAsAdmin();
        $this->actingAs($this->admin);
    }

    public function test_list_renders_table(): void
    {
        News::factory()->count(3)->create();

        Livewire::test(ManageNews::class)
            ->assertSuccessful();
    }

    public function test_list_can_search_by_title(): void
    {
        $target = News::factory()->create(['title' => 'Unique News Title']);
        $other = News::factory()->create(['title' => 'Another Article']);

        Livewire::test(ManageNews::class)
            ->searchTable('Unique News Title')
            ->assertCanSeeTableRecords([$target])
            ->assertCanNotSeeTableRecords([$other]);
    }

    public function test_list_can_search_by_slug(): void
    {
        $target = News::factory()->create(['slug' => 'find-this-news']);
        $other = News::factory()->create(['slug' => 'other-news']);

        Livewire::test(ManageNews::class)
            ->searchTable('find-this-news')
            ->assertCanSeeTableRecords([$target])
            ->assertCanNotSeeTableRecords([$other]);
    }

    public function test_create_news_with_valid_data(): void
    {
        Livewire::test(ManageNews::class)
            ->callAction(TestAction::make(CreateAction::getDefaultName())->table(), data: [
                'title'   => 'New Article',
                'display' => true,
            ])
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('news', ['title' => 'New Article']);
    }

    public function test_create_news_requires_title(): void
    {
        Livewire::test(ManageNews::class)
            ->callAction(TestAction::make(CreateAction::getDefaultName())->table(), data: [
                'display' => true,
            ])
            ->assertHasFormErrors(['title']);
    }

    public function test_edit_news_with_valid_data(): void
    {
        $news = News::factory()->create(['title' => 'Old Title']);

        Livewire::test(ManageNews::class)
            ->callAction(TestAction::make(EditAction::getDefaultName())->table($news), data: [
                'title' => 'Updated Title',
            ])
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('news', ['id' => $news->id, 'title' => 'Updated Title']);
    }

    public function test_delete_news(): void
    {
        $news = News::factory()->create();

        Livewire::test(ManageNews::class)
            ->callAction(TestAction::make(DeleteAction::getDefaultName())->table($news))
            ->assertSuccessful();

        $this->assertModelMissing($news);
    }
}
