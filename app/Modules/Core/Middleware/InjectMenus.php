<?php

namespace App\Modules\Core\Middleware;

use App\Modules\Core\Services\MenuService;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

readonly class InjectMenus
{
    public function __construct(
        private MenuService $menu
    )
    {
    }

    /**
     * Handle an incoming request.
     *
     * @param Closure(Request): (Response) $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        view()->share([
            'headerMenu' => $this->menu->getHeaderMenu(),
            'footerMenu' => $this->menu->getFooterMenu(),
        ]);
        return $next($request);
    }
}
