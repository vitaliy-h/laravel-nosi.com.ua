<?php

namespace Tests\Unit\News\ViewModels;

use App\Modules\News\Models\News;
use App\Modules\News\ViewModels\NewsViewModel;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class NewsViewModelTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_prepares_the_url_and_the_publication_date(): void
    {
        $news = News::factory()->create([
            'slug'         => 'my-news',
            'published_at' => '2026-03-04 15:30:00',
        ]);

        $vm = NewsViewModel::from($news);

        $this->assertSame(save_route('news.show', 'my-news'), $vm->url);
        $this->assertSame('04.03.2026 15:30', $vm->published_at_formatted);
    }

    public function test_an_unpublished_item_has_no_date_to_show(): void
    {
        $vm = NewsViewModel::from(News::factory()->create(['published_at' => null]));

        $this->assertNull($vm->published_at_formatted);
    }

    public function test_an_item_without_a_picture_still_resolves_an_image(): void
    {
        $vm = NewsViewModel::from(News::factory()->create(['img' => null]));

        $this->assertFalse($vm->has_image);
        $this->assertNotSame('', $vm->image);
    }

    public function test_missing_builder_content_reads_as_an_empty_list(): void
    {
        $vm = NewsViewModel::from(News::factory()->create(['content' => null]));

        $this->assertSame([], $vm->content);
    }
}
