<?php

namespace App\Modules\News\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\News\Repository\NewsRepository;
use Illuminate\Http\Request;

class NewsController extends Controller
{
    public function __construct(private readonly NewsRepository $repository)
    {
    }

    public function index(Request $request)
    {
        $news = $this->repository->all();
        return view('pages.news.index', compact('news'));
    }

    public function show($slug, Request $request)
    {
        $newsItem = $this->repository->getBySlug($slug);
        view()->share([
            'meta_title' => $newsItem->meta_title,
            'meta_description' => $newsItem->meta_description,
        ]);
        return view('pages.news.show', compact('newsItem'));
    }
}
