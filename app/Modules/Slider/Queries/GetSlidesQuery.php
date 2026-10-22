<?php

namespace App\Modules\Slider\Queries;

use App\Modules\Slider\Models\Slider;
use Illuminate\Support\Collection;

class GetSlidesQuery
{
    /**
     * @return Collection<int, Slider>
     */
    public function handle(): Collection
    {
        return Slider::visible()
            ->orderByDesc('rank')
            ->orderBy('id')
            ->get();
    }
}
