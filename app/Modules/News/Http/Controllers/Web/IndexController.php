<?php

namespace App\Modules\News\Http\Controllers\Web;

use App\Modules\Core\Abstract\BaseWebController;
use App\Modules\News\Queries\FindNewsBySlugQuery;
use App\Modules\News\Queries\GetNewsListQuery;
use Illuminate\Http\Request;

class IndexController extends BaseWebController
{
    public function __construct(
        private readonly GetNewsListQuery $getNewsList,
        private readonly FindNewsBySlugQuery $findNewsBySlug,
    ) {}

    public function index(Request $request)
    {
        $news = $this->getNewsList->handle();

        return view('pages.news.index', compact('news'));
    }

    public function show(Request $request, string $slug)
    {
        $newsItem = $this->findNewsBySlug->handle($slug);

        view()->share([
            'meta_title' => $newsItem->meta_title,
            'meta_description' => $newsItem->meta_description,
        ]);

        return view('pages.news.show', compact('newsItem'));
    }
}
