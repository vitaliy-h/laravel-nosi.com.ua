<?php

namespace App\Modules\Core\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Basket\Services\BasketService;
use App\Modules\Core\Queries\FindPageBySlugQuery;
use App\Modules\Core\Queries\GetPageBreadcrumbsQuery;
use App\Modules\Core\Services\MenuService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Str;

class ModuleRouteController extends Controller
{
    public function __construct(
        private FindPageBySlugQuery $findPageBySlug,
        private GetPageBreadcrumbsQuery $getPageBreadcrumbs,
        private MenuService $menu,
        private BasketService $basket,
    ) {}

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

        $page = $this->findPageBySlug->handle($firstSegment);
        if (! $page) {
            /*
            |--------------------------------------------------------------------------
            | Search Text page by full slug
            |--------------------------------------------------------------------------
            */
            $page = $this->findPageBySlug->handle($slug);
        }

        /*
        |--------------------------------------------------------------------------
        | Module route
        |--------------------------------------------------------------------------
        */

        if ($page) {

            $routerClass = '\\App\\Modules\\'.Str::ucfirst(Str::camel($page->module->value)).'\\Http\\Controllers\\Web\\IndexController';

            if (class_exists($routerClass)) {
                $moduleSegments = array_slice($segments, 1);

                view()->share([
                    'page' => $page,
                    'breadcrumbs' => $this->getPageBreadcrumbs->handle($page),
                    'meta_title' => $page->meta_title,
                    'meta_description' => $page->meta_description,
                    'headerMenu' => $this->menu->getHeaderMenu(),
                    'footerMenu' => $this->menu->getFooterMenu(),
                    'basketCount' => $this->basket->getCount(),
                ]);
                $controller = app($routerClass);
                $result = $controller->accessCheck();
                if ($result instanceof Response
                    || $result instanceof RedirectResponse) {
                    return $result;
                }

                return $controller->index($request, $moduleSegments);
            }
        }

        abort(404);
    }
}
