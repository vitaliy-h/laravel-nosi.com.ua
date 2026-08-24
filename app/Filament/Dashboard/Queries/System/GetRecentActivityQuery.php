<?php

namespace App\Filament\Dashboard\Queries\System;

use App\Modules\Core\Models\Log;
use Illuminate\Database\Eloquent\Builder;

class GetRecentActivityQuery
{
    /**
     * Returns a Builder rather than a DTO: the widget rendering it is a
     * Filament TableWidget.
     */
    public function handle(int $limit = 10): Builder
    {
        return Log::query()
            ->with('user')
            ->latest()
            ->limit($limit);
    }
}
