<?php

namespace Tests\Feature\Shop;

use App\Modules\User\Models\User;
use App\Modules\Shop\Models\Category;
use App\Modules\Shop\Models\Product;
use App\Modules\Shop\Models\ProductReview;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProductReviewsTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seedPagesForRouting();
    }

    private function productUrl(Product $product, Category $category): string
    {
        return '/shop/'.$category->slug.'/'.$product->slug.'-'.$product->id;
    }

    public function test_product_page_shows_visible_reviews_only(): void
    {
        $category = Category::factory()->create();
        $product = Product::factory()->forCategory($category)->create();

        $visible = ProductReview::factory()->forProduct($product)->create(['text' => 'Visible review text']);
        $hidden = ProductReview::factory()->forProduct($product)->invisible()->create(['text' => 'Hidden review text']);

        $response = $this->get($this->productUrl($product, $category));

        $response->assertStatus(200);
        $response->assertSee('review-item');
        $response->assertSee($visible->text);
        $response->assertDontSee($hidden->text);
    }

    public function test_review_line_breaks_are_rendered_without_unescaping_markup(): void
    {
        $category = Category::factory()->create();
        $product = Product::factory()->forCategory($category)->create();

        ProductReview::factory()->forProduct($product)->create([
            'text' => "First line\nSecond line <b>bold</b>",
        ]);

        $response = $this->get($this->productUrl($product, $category));

        $response->assertStatus(200);
        $response->assertSee('First line<br />', false);
        // The text is escaped before nl2br, so posted markup stays inert.
        $response->assertSee('&lt;b&gt;bold&lt;/b&gt;', false);
    }

    public function test_product_page_without_reviews_renders_empty_rating(): void
    {
        $category = Category::factory()->create();
        $product = Product::factory()->forCategory($category)->create();

        $response = $this->get($this->productUrl($product, $category));

        $response->assertStatus(200);
        $response->assertViewHas('rating', function ($rating) {
            return $rating->total === 0
                && $rating->average === 0.0
                && $rating->breakdown === [5 => 0, 4 => 0, 3 => 0, 2 => 0, 1 => 0];
        });
    }

    public function test_store_creates_hidden_review_and_leaves_product_rating_untouched(): void
    {
        $product = Product::factory()->create();

        $response = $this->from('/')->post('/shop/product-reviews', [
            'product_id' => $product->id,
            'rating' => 5,
            'name' => 'Guest Reviewer',
            'email' => 'guest@example.com',
            'text' => 'Works as advertised.',
        ]);

        $response->assertRedirect('/');
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('shop_product_reviews', [
            'product_id' => $product->id,
            'name' => 'Guest Reviewer',
            'display' => false,
        ]);

        $product->refresh();

        $this->assertSame(0.0, $product->rating);
        $this->assertSame(0, $product->reviews_count);
    }

    public function test_store_requires_rating_and_text(): void
    {
        $product = Product::factory()->create();

        $response = $this->from('/')->post('/shop/product-reviews', [
            'product_id' => $product->id,
            'name' => 'Guest Reviewer',
            'email' => 'guest@example.com',
        ]);

        $response->assertSessionHasErrors(['rating', 'text']);
        $this->assertSame(0, ProductReview::count());
    }

    public function test_store_via_ajax_returns_actions_json(): void
    {
        $product = Product::factory()->create();

        $response = $this
            ->withHeader('X-Requested-With', 'XMLHttpRequest')
            ->post('/shop/product-reviews', [
                'product_id' => $product->id,
                'rating' => 4,
                'name' => 'Ajax Reviewer',
                'email' => 'ajax@example.com',
                'text' => 'Sent over XHR.',
            ]);

        $response->assertStatus(200);
        $response->assertJsonPath('actions.0.method', 'clearForm');
        $response->assertJsonPath('actions.0.selector', '#review-form');
        $response->assertJsonPath('actions.1.method', 'html');
        $response->assertJsonPath('actions.1.selector', '#review-message');
        $this->assertDatabaseHas('shop_product_reviews', ['name' => 'Ajax Reviewer']);
    }

    public function test_logged_in_user_review_is_stored_under_the_account_identity(): void
    {
        $user = User::factory()->create([
            'name' => 'Account Name',
            'email' => 'account@example.com',
        ]);
        $product = Product::factory()->create();

        $this->actingAs($user)->from('/')->post('/shop/product-reviews', [
            'product_id' => $product->id,
            'rating' => 5,
            // Someone else's identity must not survive.
            'name' => 'Someone Else',
            'email' => 'someone@example.com',
            'text' => 'Posted while logged in.',
        ]);

        $this->assertDatabaseHas('shop_product_reviews', [
            'product_id' => $product->id,
            'user_id' => $user->id,
            'name' => 'Account Name',
            'email' => 'account@example.com',
        ]);
    }

    public function test_review_form_is_prefilled_for_logged_in_user(): void
    {
        $category = Category::factory()->create();
        $product = Product::factory()->forCategory($category)->create();

        $user = User::factory()->create([
            'name' => 'Prefilled Name',
            'email' => 'prefilled@example.com',
        ]);

        $response = $this->actingAs($user)->get($this->productUrl($product, $category));

        $response->assertStatus(200);
        $response->assertSee('value="Prefilled Name"', false);
        $response->assertSee('value="prefilled@example.com"', false);
    }

    public function test_load_more_button_appears_only_when_more_pages_exist(): void
    {
        $category = Category::factory()->create();
        $product = Product::factory()->forCategory($category)->create();

        ProductReview::factory()->count(11)->forProduct($product)->create();

        $response = $this->get($this->productUrl($product, $category));

        $response->assertStatus(200);
        $response->assertSee('load-more-btn');
        $response->assertSee('data-page="2"', false);
    }

    public function test_load_more_returns_append_action_for_the_next_page(): void
    {
        $product = Product::factory()->create();

        ProductReview::factory()->count(11)->forProduct($product)->create();

        $response = $this
            ->withHeader('X-Requested-With', 'XMLHttpRequest')
            ->get('/shop/product-reviews?product='.$product->id.'&page=2');

        $response->assertStatus(200);
        $response->assertJsonPath('actions.0.method', 'append');
        $response->assertJsonPath('actions.0.selector', '#reviews-list');
        $response->assertJsonPath('actions.1.method', 'replaceWith');
        $response->assertJsonPath('actions.1.selector', '#reviews-load-more');
        $this->assertStringContainsString('review-item', $response->json('actions.0.content'));
    }
}
