<?php

namespace App\Modules\Announce\Queries;

use App\Modules\Announce\Models\Announce;
use Illuminate\Support\Collection;

class GetAnnouncesQuery
{
    /**
     * @return Collection<int, Announce>
     */
    public function handle(): Collection
    {
        return Announce::visible()
            ->whereNotNull('title')
            ->where('title', '!=', '')
            ->orderByDesc('rank')
            ->orderBy('id')
            ->get();
    }
}
