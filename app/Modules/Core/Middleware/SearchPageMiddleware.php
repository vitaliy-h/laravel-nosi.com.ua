<?php

namespace App\Modules\Core\Middleware;

use App\Modules\Core\Enums\PageModule;
use App\Modules\Core\Facades\UrlFacade;
use App\Modules\Core\Repositories\PagesRepository;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

readonly class SearchPageMiddleware
{
    public function __construct(
        private PagesRepository $pages
    )
    {
    }

    /**
     * Handle an incoming request.
     *
     * @param Closure(Request): (Response) $next
     */
    public function handle(Request $request, Closure $next, string $module = null): Response
    {
        if ($module) {
            $page = $this->pages->findByModule(PageModule::from($module));
        } else {
            $slug = $request->path();
            $page = $this->pages->findBySlug(UrlFacade::normalize($slug));
        }

        if (!$page) {
            abort(404);
        }
        view()->share([
            'page' => $page,
            'meta_title' => $page->meta_title,
            'meta_description' => $page->meta_description,
        ]);
        return $next($request);
    }
}
