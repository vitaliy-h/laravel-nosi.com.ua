<?php

namespace Tests\Feature\Filament\InlineEdit;

use App\Filament\Resources\News\Pages\ManageNews;
use App\Filament\Resources\Pages\Pages\ManagePages;
use App\Filament\Resources\Shop\Categories\Pages\ManageCategories;
use App\Filament\Tables\Columns\InlineEditColumn;
use App\Modules\News\Models\News;
use App\Modules\Page\Models\Page;
use App\Modules\Shop\Models\Category;
use App\Modules\Shop\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Gate;
use Livewire\Features\SupportTesting\Testable;
use Livewire\Livewire;
use Tests\Feature\Filament\InlineEdit\Fixtures\DenyUpdatePolicy;
use Tests\Feature\Filament\InlineEdit\Fixtures\InlineEditTable;
use Tests\TestCase;

/**
 * InlineEditColumn behaviour shared by every resource. Saving goes through
 * Filament's `updateTableColumnState()` — the same call the Alpine component makes.
 */
class InlineEditColumnTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->actingAs($this->actingAsAdmin());
    }

    public function test_saving_a_cell_updates_the_record(): void
    {
        $page = Page::factory()->create(['title' => 'Old title']);

        $result = $this->updateCell(Livewire::test(ManagePages::class), 'title', $page, 'New title');

        $this->assertSame('New title', $result);
        $this->assertSame('New title', $page->fresh()->title);
    }

    public function test_invalid_value_returns_an_error_and_keeps_the_record(): void
    {
        $page = Page::factory()->create(['rank' => 5]);

        $result = $this->updateCell(Livewire::test(ManagePages::class), 'rank', $page, 'abc');

        $this->assertIsArray($result);
        $this->assertArrayHasKey('error', $result);
        $this->assertSame(5, $page->fresh()->rank);
    }

    public function test_slug_must_stay_unique(): void
    {
        Page::factory()->create(['slug' => 'taken']);
        $page = Page::factory()->create(['slug' => 'free']);

        $result = $this->updateCell(Livewire::test(ManagePages::class), 'slug', $page, 'taken');

        $this->assertArrayHasKey('error', $result);
        $this->assertSame('free', $page->fresh()->slug);
    }

    public function test_slug_can_be_saved_unchanged(): void
    {
        $page = Page::factory()->create(['slug' => 'same']);

        $this->updateCell(Livewire::test(ManagePages::class), 'slug', $page, 'same');

        $this->assertSame('same', $page->fresh()->slug);
    }

    public function test_record_the_user_cannot_edit_is_neither_saved_nor_editable(): void
    {
        $page = Page::factory()->create(['title' => 'Locked']);
        Gate::policy(Page::class, DenyUpdatePolicy::class);

        $component = Livewire::test(ManagePages::class);
        $result = $this->updateCell($component, 'title', $page, 'Changed');

        $this->assertNull($result);
        $this->assertSame('Locked', $page->fresh()->title);
        $this->assertStringNotContainsString('inlineEditColumn', $component->html());

        $column = $this->column($component, 'title', $page);
        $this->assertTrue($column->isDisabled());
        $this->assertFalse($column->isClickDisabled());
    }

    public function test_editable_cell_does_not_run_the_row_action(): void
    {
        $page = Page::factory()->create();

        $component = Livewire::test(ManagePages::class);

        $this->assertStringContainsString('inlineEditColumn', $component->html());
        $this->assertTrue($this->column($component, 'title', $page)->isClickDisabled());
    }

    public function test_cell_shows_formatted_text_but_edits_the_raw_value(): void
    {
        $this->skipUnlessModules('shop');

        $parent = Category::factory()->create(['title' => 'Parent']);
        $child = Category::factory()->make(['title' => 'Child']);
        $child->appendToNode($parent)->save();

        $component = Livewire::test(ManageCategories::class);

        $component->assertSee('— Child');
        $this->assertSame('Child', $this->column($component, 'title', $child->fresh())->getEditState());
    }

    public function test_empty_editable_cell_shows_a_placeholder(): void
    {
        $this->skipUnlessModules('news');

        News::factory()->create(['published_at' => null]);

        $component = Livewire::test(ManageNews::class);

        $this->assertStringContainsString('fi-ta-placeholder', $component->html());
    }

    public function test_changing_the_title_does_not_regenerate_seo(): void
    {
        $this->skipUnlessModules('news');

        $news = News::factory()->create([
            'title'      => 'Old',
            'slug'       => 'old-slug',
            'meta_title' => 'Old meta',
        ]);

        $this->updateCell(Livewire::test(ManageNews::class), 'title', $news, 'Brand new title');

        $news->refresh();
        $this->assertSame('Brand new title', $news->title);
        $this->assertSame('old-slug', $news->slug);
        $this->assertSame('Old meta', $news->meta_title);
    }

    public function test_datetime_cell_edits_and_saves_minutes(): void
    {
        $this->skipUnlessModules('news');

        $news = News::factory()->create(['published_at' => '2026-01-02 03:04:00']);

        $component = Livewire::test(ManageNews::class);
        $this->assertSame('2026-01-02T03:04', $this->column($component, 'published_at', $news)->getEditState());

        $this->updateCell($component, 'published_at', $news, '2026-05-06T07:08');

        $this->assertSame('2026-05-06 07:08:00', $news->fresh()->published_at->format('Y-m-d H:i:s'));
    }

    public function test_multiple_saves_an_array_column(): void
    {
        $this->skipUnlessModules('shop');

        $product = Product::factory()->create(['imgs' => ['a.jpg']]);

        $component = Livewire::test(InlineEditTable::class);
        $this->assertSame(['a.jpg'], $this->column($component, 'imgs', $product)->getEditState());

        $this->updateCell($component, 'imgs', $product, ['b.jpg', '', 'c.jpg']);
        $this->assertSame(['b.jpg', 'c.jpg'], $product->fresh()->imgs);

        $this->updateCell($component, 'imgs', $product, []);
        $this->assertSame([], $product->fresh()->imgs);
    }

    public function test_date_cell_edits_the_date_part(): void
    {
        $this->skipUnlessModules('shop');

        $product = Product::factory()->create(['created_at' => '2026-03-04 10:00:00']);

        $component = Livewire::test(InlineEditTable::class);
        $this->assertSame('2026-03-04', $this->column($component, 'created_at', $product)->getEditState());

        $this->updateCell($component, 'created_at', $product, '2026-07-08');

        $this->assertSame('2026-07-08', $product->fresh()->created_at->format('Y-m-d'));
    }

    public function test_table_outside_a_resource_is_editable(): void
    {
        $this->skipUnlessModules('shop');

        $product = Product::factory()->create();

        $this->assertFalse($this->column(Livewire::test(InlineEditTable::class), 'imgs', $product)->isDisabled());
    }

    protected function updateCell(Testable $component, string $column, mixed $record, mixed $value): mixed
    {
        return $component->instance()->updateTableColumnState($column, (string) $record->getKey(), $value);
    }

    protected function column(Testable $component, string $name, mixed $record): InlineEditColumn
    {
        $column = $component->instance()->getTable()->getColumn($name);

        $this->assertInstanceOf(InlineEditColumn::class, $column);

        return $column->record($record);
    }
}
