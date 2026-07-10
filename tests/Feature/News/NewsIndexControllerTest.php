<?php

namespace Tests\Feature\News;

use App\Modules\News\Models\News;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class NewsIndexControllerTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seedPagesForRouting();
    }

    public function test_index_returns_200_and_lists_visible_news(): void
    {
        $visible = News::factory()->create(['title' => 'Visible News']);
        News::factory()->invisible()->create(['title' => 'Hidden News']);

        $response = $this->get('/news');

        $response->assertStatus(200);
        $response->assertViewIs('pages.news.index');
        $response->assertViewHas('news', function ($news) use ($visible) {
            return $news->pluck('id')->contains($visible->id) && $news->count() === 1;
        });
    }

    public function test_show_returns_200_for_visible_news_by_slug(): void
    {
        $news = News::factory()->create(['slug' => 'my-news-item']);

        $response = $this->get('/news/my-news-item');

        $response->assertStatus(200);
        $response->assertViewIs('pages.news.show');
        $response->assertViewHas('newsItem', fn ($newsItem) => $newsItem->id === $news->id);
    }

    public function test_show_returns_404_for_invisible_news(): void
    {
        News::factory()->invisible()->create(['slug' => 'hidden-news']);

        $response = $this->get('/news/hidden-news');

        $response->assertStatus(404);
    }

    public function test_show_returns_404_for_unknown_slug(): void
    {
        $response = $this->get('/news/unknown-slug');

        $response->assertStatus(404);
    }
}
