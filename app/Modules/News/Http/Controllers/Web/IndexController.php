<?php

namespace App\Modules\News\Http\Controllers\Web;

use App\Modules\Core\Abstract\BaseWebController;
use App\Modules\News\Queries\FindNewsBySlugQuery;
use App\Modules\News\Queries\GetNewsListQuery;
use App\Modules\News\ViewModels\NewsViewModel;
use Illuminate\Http\Request;

class IndexController extends BaseWebController
{
    public function __construct(
        private readonly GetNewsListQuery $getNewsList,
        private readonly FindNewsBySlugQuery $findNewsBySlug,
    ) {}

    public function index(Request $request)
    {
        $news = NewsViewModel::collect($this->getNewsList->handle());

        return $this->render('pages.news.index', compact('news'));
    }

    public function show(Request $request, string $slug)
    {
        $newsItem = NewsViewModel::from($this->findNewsBySlug->handle($slug));

        view()->share([
            'meta_title' => $newsItem->meta_title,
            'meta_description' => $newsItem->meta_description,
        ]);

        return $this->render('pages.news.show', compact('newsItem'));
    }
}
