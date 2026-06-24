<?php

namespace App\Modules\Core\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Core\Enums\PageModule;
use App\Modules\Core\Models\Page;
use App\Modules\News\Models\News;
use App\Modules\Shop\Models\Category;
use App\Modules\Shop\Models\Product;
use Illuminate\Http\Response;

class SitemapController extends Controller
{
    private const int PER_PAGE = 500;

    private function isModuleIndexable(PageModule $module): bool
    {
        return Page::where('module', $module)
            ->where('display', true)
            ->where('is_indexable', true)
            ->exists();
    }

    public function index(): Response
    {
        $newsEnabled = $this->isModuleIndexable(PageModule::NEWS);
        $shopEnabled = $this->isModuleIndexable(PageModule::SHOP);

        $newsTotalPages = $newsEnabled ? (int) ceil(News::visible()->count() / self::PER_PAGE) : 0;
        $categoriesTotalPages = $shopEnabled ? (int) ceil(Category::visible()->count() / self::PER_PAGE) : 0;
        $productsTotalPages = $shopEnabled ? (int) ceil(Product::visible()->count() / self::PER_PAGE) : 0;

        return response()
            ->view('sitemap.index', compact('newsTotalPages', 'categoriesTotalPages', 'productsTotalPages'))
            ->header('Content-Type', 'application/xml');
    }

    public function base(): Response
    {
        $pages = Page::visible()
            ->where('is_indexable', true)
            ->orderBy('rank', 'DESC')
            ->get();

        return response()
            ->view('sitemap.base', compact('pages'))
            ->header('Content-Type', 'application/xml');
    }

    public function news(int $page): Response
    {
        $items = $this->isModuleIndexable(PageModule::NEWS)
            ? News::visible()
                ->orderBy('published_at', 'DESC')
                ->offset(($page - 1) * self::PER_PAGE)
                ->limit(self::PER_PAGE)
                ->get()
            : collect();

        return response()
            ->view('sitemap.news', compact('items'))
            ->header('Content-Type', 'application/xml');
    }

    public function categories(int $page): Response
    {
        $items = $this->isModuleIndexable(PageModule::SHOP)
            ? Category::visible()
                ->orderBy('_lft')
                ->offset(($page - 1) * self::PER_PAGE)
                ->limit(self::PER_PAGE)
                ->get()
            : collect();

        return response()
            ->view('sitemap.categories', compact('items'))
            ->header('Content-Type', 'application/xml');
    }

    public function products(int $page): Response
    {
        $items = $this->isModuleIndexable(PageModule::SHOP)
            ? Product::visible()
                ->with('category')
                ->orderBy('id')
                ->offset(($page - 1) * self::PER_PAGE)
                ->limit(self::PER_PAGE)
                ->get()
            : collect();

        return response()
            ->view('sitemap.products', compact('items'))
            ->header('Content-Type', 'application/xml');
    }
}
