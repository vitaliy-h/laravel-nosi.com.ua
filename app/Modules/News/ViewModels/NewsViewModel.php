<?php

namespace App\Modules\News\ViewModels;

use App\Modules\Core\Abstract\BaseViewModel;
use App\Modules\News\Models\News;

class NewsViewModel extends BaseViewModel
{
    /**
     * @param  array<int, array<string, mixed>>  $content  builder blocks
     */
    public function __construct(
        public readonly int $id,
        public readonly string $title,
        public readonly string $url,
        public readonly ?string $short_text,
        public readonly bool $has_image,
        public readonly string $image,
        public readonly ?string $published_at_formatted,
        public readonly array $content,
        public readonly ?string $meta_title,
        public readonly ?string $meta_description,
    ) {}

    public static function fromNews(News $news): self
    {
        return new self(
            id: $news->id,
            title: (string) $news->title,
            url: save_route('news.show', $news->slug),
            short_text: $news->short_text,
            has_image: ! empty($news->img),
            image: $news->image,
            published_at_formatted: $news->published_at?->format('d.m.Y H:i'),
            content: $news->content ?? [],
            meta_title: $news->meta_title,
            meta_description: $news->meta_description,
        );
    }
}
