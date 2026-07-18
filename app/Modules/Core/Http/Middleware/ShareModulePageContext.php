<?php

namespace App\Modules\Core\Http\Middleware;

use App\Modules\Core\Enums\PageModule;
use App\Modules\Core\Models\Page;
use App\Modules\Core\Queries\FindPageByModuleQuery;
use App\Modules\Core\Services\PageContextSharer;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * Attached automatically (per module) by ModuleServiceProvider::registerWebRoute()
 * for every module that maps to a PageModule case. Shares the CMS page context so
 * module controllers don't need to fetch/share it themselves.
 *
 * Resolves the page by PageModule at request time (not baked in at boot) so it
 * stays correct even if the page didn't exist yet when routes were registered
 * (e.g. tests seeding the DB after the app booted).
 */
class ShareModulePageContext
{
    public function __construct(
        private FindPageByModuleQuery $findPageByModule,
        private PageContextSharer $sharer,
    ) {}

    public function handle(Request $request, Closure $next, string $moduleValue): mixed
    {
        $pageModule = PageModule::tryFrom($moduleValue);
        $page = $pageModule ? $this->findPageByModule->handle($pageModule) : null;

        if (!$page) {
            throw new NotFoundHttpException();
        }

        // FindPageByModuleQuery reads the cached routing list, which only selects
        // id/title/slug/module. Views need the full row (content, text, SEO fields),
        // otherwise they silently render empty and is_indexable reads as null.
        $page = Page::findOrFail($page->id);

        $this->sharer->share($page);

        return $next($request);
    }
}
