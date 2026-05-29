<?php

namespace App\Modules\News\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Modules\Core\Interfaces\ModuleIndexControllerInterface;
use App\Modules\News\Repository\NewsRepository;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class IndexController extends Controller implements ModuleIndexControllerInterface
{
    public function __construct(private readonly NewsRepository $repository)
    {
    }

    public function accessCheck(): RedirectResponse|Response|null
    {
        return null;
    }

    public function index(Request $request, array $segments = [])
    {
        if (!empty($segments)) {
            $slug = $segments[0];
            return $this->show($slug, $request);
        }
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
