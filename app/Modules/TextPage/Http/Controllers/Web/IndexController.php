<?php

namespace App\Modules\TextPage\Http\Controllers\Web;

use App\Modules\Core\Abstract\BaseWebController;
use App\Modules\Core\Services\PageContextSharer;
use App\Modules\TextPage\Queries\FindTextPageBySlugQuery;
use Illuminate\Http\Request;

class IndexController extends BaseWebController
{
    public function __construct(
        private readonly FindTextPageBySlugQuery $findTextPageBySlug,
        private readonly PageContextSharer $pageContextSharer,
    ) {}

    public function __invoke(Request $request, ?string $slug = null)
    {
        $page = $this->findTextPageBySlug->handle(trim($slug ?? '', '/'));

        abort_unless($page, 404);

        $this->pageContextSharer->share($page);

        return $this->render('pages.text_page');
    }
}
