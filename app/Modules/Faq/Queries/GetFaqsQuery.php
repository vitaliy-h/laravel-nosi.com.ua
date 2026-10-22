<?php

namespace App\Modules\Faq\Queries;

use App\Modules\Faq\Models\Faq;
use Illuminate\Support\Collection;

class GetFaqsQuery
{
    /**
     * @return Collection<int, Faq>
     */
    public function handle(): Collection
    {
        return Faq::visible()
            ->whereNotNull('title')
            ->where('title', '!=', '')
            ->orderByDesc('rank')
            ->orderBy('id')
            ->get();
    }
}
