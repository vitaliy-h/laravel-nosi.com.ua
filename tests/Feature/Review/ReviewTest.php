<?php

namespace Tests\Feature\Review;

use App\Modules\Review\Models\Review;
use App\Modules\User\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReviewTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seedPagesForRouting();
    }

    public function test_home_page_shows_visible_reviews_only(): void
    {
        $visible = Review::factory()->create(['text' => 'Visible review text']);
        $hidden = Review::factory()->invisible()->create(['text' => 'Hidden review text']);

        $response = $this->get('/');

        $response->assertStatus(200);
        $response->assertSee('review-author');
        $response->assertSee($visible->text);
        $response->assertDontSee($hidden->text);
    }

    public function test_home_page_rating_summary_counts_visible_reviews(): void
    {
        Review::factory()->withRating(5)->create();
        Review::factory()->withRating(4)->create();
        Review::factory()->withRating(1)->invisible()->create();

        $response = $this->get('/');

        $response->assertStatus(200);
        $response->assertViewHas('rating_value', '4.5');
        $response->assertViewHas('reviews_total', '2');
    }

    public function test_home_page_offers_the_review_form_before_any_review_is_published(): void
    {
        $response = $this->get('/');

        $response->assertStatus(200);
        $response->assertSee('id="review-dialog"', false);
        $response->assertSee('id="review-form"', false);
        $response->assertDontSee('review-track');
    }

    public function test_review_line_breaks_are_rendered_without_unescaping_markup(): void
    {
        Review::factory()->create([
            'text' => "First line\nSecond line <b>bold</b>",
        ]);

        $response = $this->get('/');

        $response->assertStatus(200);
        $response->assertSee('First line<br />', false);
        // The text is escaped before nl2br, so posted markup stays inert.
        $response->assertSee('&lt;b&gt;bold&lt;/b&gt;', false);
    }

    public function test_store_creates_hidden_review(): void
    {
        $response = $this->from('/')->post('/review', [
            'rating' => 5,
            'name'   => 'Guest Reviewer',
            'city'   => 'Kyiv',
            'email'  => 'guest@example.com',
            'text'   => 'Fast delivery, great service.',
        ]);

        $response->assertRedirect('/');
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('reviews', [
            'name'    => 'Guest Reviewer',
            'city'    => 'Kyiv',
            'rating'  => 5,
            'display' => false,
        ]);
    }

    public function test_store_requires_rating_and_text(): void
    {
        $response = $this->from('/')->post('/review', [
            'name'  => 'Guest Reviewer',
            'email' => 'guest@example.com',
        ]);

        $response->assertSessionHasErrors(['rating', 'text']);
        $this->assertSame(0, Review::count());
    }

    public function test_store_rejects_invalid_email(): void
    {
        foreach (['not-an-email', 'guest@example'] as $email) {
            $response = $this->from('/')->post('/review', [
                'rating' => 5,
                'name'   => 'Guest Reviewer',
                'email'  => $email,
                'text'   => 'Fast delivery, great service.',
            ]);

            $response->assertSessionHasErrors('email');
        }

        $this->assertSame(0, Review::count());
    }

    public function test_store_via_ajax_returns_422_for_invalid_email(): void
    {
        $response = $this
            ->withHeader('X-Requested-With', 'XMLHttpRequest')
            ->postJson('/review', [
                'rating' => 4,
                'name'   => 'Ajax Reviewer',
                'email'  => 'ajax@example',
                'text'   => 'Sent over XHR.',
            ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors('email');
        $this->assertSame(0, Review::count());
    }

    public function test_review_dialog_has_close_button_and_email_pattern(): void
    {
        $response = $this->get('/');

        $response->assertStatus(200);
        $response->assertSee('class="modal-close" data-dialog-close', false);
        $response->assertSee('id="review-email" name="email" pattern=', false);
    }

    public function test_store_via_ajax_replaces_the_dialog_body(): void
    {
        $response = $this
            ->withHeader('X-Requested-With', 'XMLHttpRequest')
            ->post('/review', [
                'rating' => 4,
                'name'   => 'Ajax Reviewer',
                'email'  => 'ajax@example.com',
                'text'   => 'Sent over XHR.',
            ]);

        $response->assertStatus(200);
        $response->assertJsonPath('actions.0.method', 'html');
        $response->assertJsonPath('actions.0.selector', '#review-dialog-body');
        $this->assertDatabaseHas('reviews', ['name' => 'Ajax Reviewer']);
    }

    public function test_logged_in_user_review_keeps_what_was_typed_and_links_the_account(): void
    {
        $user = User::factory()->create([
            'name'  => 'Account Name',
            'email' => 'account@example.com',
        ]);

        $this->actingAs($user)->from('/')->post('/review', [
            'rating' => 5,
            'name'   => 'Олена',
            'email'  => 'olena@example.com',
            'text'   => 'Posted while logged in.',
        ]);

        $this->assertDatabaseHas('reviews', [
            'user_id' => $user->id,
            'name'    => 'Олена',
            'email'   => 'olena@example.com',
        ]);
    }

    public function test_logged_in_user_still_needs_a_valid_email(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->from('/')->post('/review', [
            'rating' => 5,
            'name'   => 'Олена',
            'email'  => 'not-an-email',
            'text'   => 'Posted while logged in.',
        ])->assertSessionHasErrors('email');

        $this->assertDatabaseMissing('reviews', ['user_id' => $user->id]);
    }

    public function test_review_form_fields_are_editable_for_logged_in_user(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->get('/')
            ->assertStatus(200)
            ->assertDontSee('readonly', false);
    }

    public function test_review_form_is_prefilled_for_logged_in_user(): void
    {
        $user = User::factory()->create([
            'name'  => 'Prefilled Name',
            'email' => 'prefilled@example.com',
        ]);

        $response = $this->actingAs($user)->get('/');

        $response->assertStatus(200);
        $response->assertSee('value="Prefilled Name"', false);
        $response->assertSee('value="prefilled@example.com"', false);
    }
}
