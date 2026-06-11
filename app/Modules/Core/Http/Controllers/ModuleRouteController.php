<?php

namespace App\Modules\Core\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Core\Repositories\PagesRepository;
use App\Modules\Core\Services\MenuService;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class ModuleRouteController extends Controller
{
    public function __construct(
        private PagesRepository $pages,
        private MenuService $menu
    )
    {
    }

    public function __invoke(Request $request, ?string $slug = null)
    {
        $slug = trim($slug ?? '', '/');

        $segments = explode('/', $slug);

        $firstSegment = $segments[0] ?? '';

        /*
        |--------------------------------------------------------------------------
        | Find module page
        |--------------------------------------------------------------------------
        */

        $page = $this->pages->findBySlug($firstSegment);
        if (!$page) {
            /*
            |--------------------------------------------------------------------------
            | Search Text page by full slug
            |--------------------------------------------------------------------------
            */
            $page = $this->pages->findBySlug($slug);
        }

        /*
        |--------------------------------------------------------------------------
        | Module route
        |--------------------------------------------------------------------------
        */

        if ($page) {

            $routerClass = '\\App\\Modules\\' . Str::ucfirst(Str::camel($page->module->value))  . '\\Http\\Controllers\\Web\\IndexController';

            if (class_exists($routerClass)) {
                $moduleSegments = array_slice($segments, 1);
                view()->share([
                    'page' => $page,
                    'breadcrumbs' => $this->pages->getBreadcrumbs($page),
                    'meta_title' => $page->meta_title,
                    'meta_description' => $page->meta_description,
                    'headerMenu' => $this->menu->getHeaderMenu(),
                    'footerMenu' => $this->menu->getFooterMenu(),
                ]);
                $result = app($routerClass)->accessCheck();
                if ($result instanceof \Illuminate\Http\Response
                    || $result instanceof \Illuminate\Http\RedirectResponse) {
                    return $result;
                }
                return app($routerClass)->index($request,$moduleSegments);
            }
        }

        abort(404);
    }
}
