<?php

namespace App\Modules\Core\Observers;

use App\Modules\Core\Models\Page;
use App\Modules\Core\Repositories\PagesRepository;

readonly class PageObserver
{
    public function __construct(private PagesRepository $repository) {}

    public function created(Page $page): void
    {
        $this->repository->clearCache();
    }

    public function updated(Page $page): void
    {
        $this->repository->clearCache();
    }

    public function deleted(Page $page): void
    {
        $this->repository->clearCache();
    }
}
