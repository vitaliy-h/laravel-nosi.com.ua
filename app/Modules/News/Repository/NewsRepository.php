<?php

namespace App\Modules\News\Repository;

use App\Modules\News\Models\News;

class NewsRepository
{
    private int $paginate = 12;

    public function all()
    {
        return News::visible()->orderBy('published_at', 'desc')->paginate($this->paginate);
    }

    public function getBySlug($slug)
    {
        return News::visible()->where('slug', $slug)->firstOrFail();
    }
}
