<?php

namespace Tests\Feature\Filament\Review;

use App\Filament\Resources\Home\Reviews\Pages\ManageReviews;
use App\Modules\Review\Models\Review;
use App\Modules\User\Models\User;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Actions\Testing\TestAction;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class ReviewResourceTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->skipUnlessModules('review');

        $this->admin = $this->actingAsAdmin();
        $this->actingAs($this->admin);
    }

    public function test_list_renders_table(): void
    {
        Review::factory()->count(3)->create();

        Livewire::test(ManageReviews::class)
            ->assertSuccessful();
    }

    public function test_list_can_search_by_author_name(): void
    {
        $target = Review::factory()->create(['name' => 'Zaphod Beeblebrox']);
        $other = Review::factory()->create(['name' => 'Arthur Dent']);

        Livewire::test(ManageReviews::class)
            ->searchTable('Zaphod')
            ->assertCanSeeTableRecords([$target])
            ->assertCanNotSeeTableRecords([$other]);
    }

    public function test_list_can_search_by_text(): void
    {
        $target = Review::factory()->create(['text' => 'UniqueReviewPhrase about delivery']);
        $other = Review::factory()->create(['text' => 'Something else entirely']);

        Livewire::test(ManageReviews::class)
            ->searchTable('UniqueReviewPhrase')
            ->assertCanSeeTableRecords([$target])
            ->assertCanNotSeeTableRecords([$other]);
    }

    public function test_list_can_filter_by_rating(): void
    {
        $target = Review::factory()->withRating(5)->create();
        $other = Review::factory()->withRating(2)->create();

        Livewire::test(ManageReviews::class)
            ->filterTable('rating', 5)
            ->assertCanSeeTableRecords([$target])
            ->assertCanNotSeeTableRecords([$other]);
    }

    public function test_list_can_filter_unpublished_reviews(): void
    {
        $pending = Review::factory()->invisible()->create();
        $published = Review::factory()->create();

        Livewire::test(ManageReviews::class)
            ->filterTable('display', false)
            ->assertCanSeeTableRecords([$pending])
            ->assertCanNotSeeTableRecords([$published]);
    }

    public function test_create_review_with_valid_data(): void
    {
        Livewire::test(ManageReviews::class)
            ->callAction(TestAction::make(CreateAction::getDefaultName())->table(), data: [
                'rating' => 4,
                'name'   => 'New Reviewer',
                'city'   => 'Lviv',
                'text'   => 'Solid shop.',
            ])
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('reviews', [
            'name'   => 'New Reviewer',
            'city'   => 'Lviv',
            'rating' => 4,
            // Reviews created in the admin panel are hidden until published.
            'display' => false,
        ]);
    }

    public function test_create_review_requires_rating(): void
    {
        Livewire::test(ManageReviews::class)
            ->callAction(TestAction::make(CreateAction::getDefaultName())->table(), data: [
                'name' => 'No Rating',
            ])
            ->assertHasFormErrors(['rating']);
    }

    public function test_edit_review_with_valid_data(): void
    {
        $review = Review::factory()->create(['text' => 'Old text']);

        Livewire::test(ManageReviews::class)
            ->callAction(TestAction::make(EditAction::getDefaultName())->table($review), data: [
                'text' => 'Updated text',
            ])
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('reviews', [
            'id'   => $review->id,
            'text' => 'Updated text',
        ]);
    }

    public function test_delete_review(): void
    {
        $review = Review::factory()->create();

        Livewire::test(ManageReviews::class)
            ->callAction(TestAction::make(DeleteAction::getDefaultName())->table($review))
            ->assertSuccessful();

        $this->assertModelMissing($review);
    }
}
