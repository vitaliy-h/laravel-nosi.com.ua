<?php

namespace App\Modules\News\Queries;

use App\Modules\News\Models\News;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class GetNewsListQuery
{
    private int $paginate = 12;

    public function handle(): LengthAwarePaginator
    {
        return News::visible()->orderBy('published_at', 'desc')->paginate($this->paginate);
    }
}
