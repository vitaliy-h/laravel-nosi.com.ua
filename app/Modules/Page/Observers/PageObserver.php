<?php

namespace App\Modules\Page\Observers;

use App\Modules\Page\Models\Page;
use App\Modules\Page\Queries\GetAllPagesQuery;

readonly class PageObserver
{
    public function __construct(private GetAllPagesQuery $getAllPages) {}

    public function created(Page $page): void
    {
        $this->getAllPages->clear();
    }

    public function updated(Page $page): void
    {
        $this->getAllPages->clear();
    }

    public function deleted(Page $page): void
    {
        $this->getAllPages->clear();
    }
}
