<?php

namespace App\Modules\Core\Observers;

use App\Modules\Core\Models\Page;
use App\Modules\Core\Queries\GetAllPagesQuery;

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
