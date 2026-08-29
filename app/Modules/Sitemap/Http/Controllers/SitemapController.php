<?php

namespace App\Modules\Sitemap\Http\Controllers;

use App\Modules\Core\Abstract\BaseWebController;
use App\Modules\Page\Enums\PageModule;
use App\Modules\Page\Models\Page;
use App\Modules\News\Models\News;
use App\Modules\Shop\Models\Category;
use App\Modules\Shop\Models\Product;
use Illuminate\Http\Response;

class SitemapController extends BaseWebController
{
    private const int PER_PAGE = 500;

    private function isModuleIndexable(PageModule $module): bool
    {
        return Page::where('module', $module)
            ->where('display', true)
            ->where('is_indexable', true)
            ->exists();
    }

    private function xmlResponse(string $view, array $data = []): Response
    {
        return response(
            '<?xml version="1.0" encoding="UTF-8"?>' . $this->render($view, $data)->render(),
            200,
            ['Content-Type' => 'application/xml']
        );
    }

    public function index(): Response
    {
        $newsEnabled = $this->isModuleIndexable(PageModule::NEWS);
        $shopEnabled = $this->isModuleIndexable(PageModule::SHOP);

        $newsTotalPages = $newsEnabled ? (int) ceil(News::visible()->count() / self::PER_PAGE) : 0;
        $categoriesTotalPages = $shopEnabled ? (int) ceil(Category::visible()->count() / self::PER_PAGE) : 0;
        $productsTotalPages = $shopEnabled ? (int) ceil(Product::visible()->count() / self::PER_PAGE) : 0;

        return $this->xmlResponse('sitemap.index', compact('newsTotalPages', 'categoriesTotalPages', 'productsTotalPages'));
    }

    public function base(): Response
    {
        $pages = Page::visible()
            ->where('is_indexable', true)
            ->orderBy('rank', 'DESC')
            ->get();

        return $this->xmlResponse('sitemap.base', compact('pages'));
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

        return $this->xmlResponse('sitemap.news', compact('items'));
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

        return $this->xmlResponse('sitemap.categories', compact('items'));
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

        return $this->xmlResponse('sitemap.products', compact('items'));
    }
}
