<?php

namespace App\Filament\Dashboard\Queries\Shop;

use App\Modules\ShopOrder\Models\Order;
use Illuminate\Database\Eloquent\Builder;

class GetLatestOrdersQuery
{
    /**
     * Returns a Builder rather than a DTO: the widget rendering it is a
     * Filament TableWidget, and `table()` needs a query it can paginate and
     * sort itself.
     */
    public function handle(int $limit = 10): Builder
    {
        return Order::query()
            ->with('user')
            ->latest()
            ->limit($limit);
    }
}
