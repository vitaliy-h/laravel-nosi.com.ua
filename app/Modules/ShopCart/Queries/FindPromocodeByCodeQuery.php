<?php

namespace App\Modules\ShopCart\Queries;

use App\Modules\ShopCart\Models\Promocode;
use Illuminate\Support\Str;

class FindPromocodeByCodeQuery
{
    public function handle(string $code): ?Promocode
    {
        return Promocode::active()
            ->where('code', Str::upper(trim($code)))
            ->first();
    }
}
