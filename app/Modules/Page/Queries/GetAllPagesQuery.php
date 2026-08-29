<?php

namespace App\Modules\Page\Queries;

use App\Modules\Page\Models\Page;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Cache;

class GetAllPagesQuery
{
    private const CACHE_KEY = 'pages.all';

    private ?Collection $allPages = null;

    public function handle(): Collection
    {
        if ($this->allPages !== null) {
            return $this->allPages;
        }

        $rows = Cache::remember(self::CACHE_KEY, 60 * 60 * 24, static function () {
            return Page::visible()->select(['id', 'title', 'slug', 'module'])->get()->toArray();
        });

        return $this->allPages = Page::hydrate($rows);
    }

    public function clear(): void
    {
        $this->allPages = null;
        Cache::forget(self::CACHE_KEY);
        Artisan::call('route:clear');
        Artisan::call('cache:clear');
    }
}
