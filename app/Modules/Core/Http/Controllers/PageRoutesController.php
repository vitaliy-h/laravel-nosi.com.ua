<?php
/*
namespace App\Modules\Core\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Core\Enums\PageModule;
use App\Modules\Core\Models\Page;
use App\Modules\Core\Repositories\PagesRepository;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Str;

class PageRoutesController extends Controller
{
    public function __construct(
        private readonly PagesRepository $pages
    ) {
    }

    public static function register(): void
    {
        $pages = Page::query()
            ->where('display', true)
            ->where('module', PageModule::SHOP->value) //todo remove
            ->get();

        foreach ($pages as $page) {
            $moduleName = $page->module->value;

            $controllerClass = 'App\\Modules\\'
                . Str::ucfirst(Str::camel($moduleName))
                . '\\Http\\Controllers\\Web\\IndexController';

            if (!class_exists($controllerClass)) {
                continue;
            }

            Route::match(['get', 'post'], $page->slug . '/{segments?}', [self::class, 'handle'])
                ->where('segments', '.*')
                ->defaults('page_id', $page->id)
                ->name('module.' . $moduleName);
        }
    }

    public function __invoke(Request $request)
    {
        $page = Page::query()
            ->where('module', PageModule::INDEX->value)
            ->where('display', true)
            ->first();

        if (!$page) {
            abort(404);
        }

        return $this->runModule($request, $page, []);
    }

    public function handle(Request $request)
    {
        $pageId = $request->route('page_id');

        $page = Page::query()
            ->whereKey($pageId)
            ->where('display', true)
            ->first();

        if (!$page) {
            abort(404);
        }

        $segments = $request->route('segments');

        $moduleSegments = $segments
            ? explode('/', trim($segments, '/'))
            : [];

        return $this->runModule($request, $page, $moduleSegments);
    }

    public function handleText(Request $request, string $slug)
    {
        $page = $this->pages->findBySlug($slug);

        if (!$page || $page->module !== PageModule::TEXT) {
            abort(404);
        }

        return $this->runModule($request, $page, []);
    }

    private function runModule(Request $request, Page $page, array $segments = [])
    {
        $controllerClass = 'App\\Modules\\'
            . Str::ucfirst(Str::camel($page->module->value))
            . '\\Http\\Controllers\\Web\\IndexController';

        if (!class_exists($controllerClass)) {
            abort(404);
        }

        view()->share([
            'page' => $page,
            'breadcrumbs' => $this->pages->getBreadcrumbs(),
            'meta_title' => $page->meta_title ?: $page->title,
            'meta_description' => $page->meta_description,
        ]);

        $controller = app($controllerClass);

        $accessResult = $controller->accessCheck();

        if ($accessResult) {
            return $accessResult;
        }

        return $controller->index($request, $segments);
    }
}
/**/
