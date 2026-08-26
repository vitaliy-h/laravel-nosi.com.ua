<?php

namespace App\Filament\Dashboard\Queries\Shop;

use App\Filament\Dashboard\Dtos\DashboardPeriodData;
use App\Filament\Dashboard\Dtos\Shop\PromocodeStatsData;
use App\Modules\ShopCart\Models\Promocode;
use App\Modules\ShopOrder\Models\Order;
use Illuminate\Support\Facades\Cache;

class GetPromocodeStatsQuery
{
    public function handle(DashboardPeriodData $period): PromocodeStatsData
    {
        // config/cache.php sets serializable_classes => false: unserializing
        // any object (including this DTO) from cache storage comes back as
        // __PHP_Incomplete_Class. Cache the plain array instead and rehydrate
        // the DTO on every read.
        $data = Cache::remember(
            "dashboard:promocodes:{$period->cacheKey()}",
            now()->addMinutes(5),
            fn (): array => $this->build($period)->toArray(),
        );

        return PromocodeStatsData::from($data);
    }

    private function build(DashboardPeriodData $period): PromocodeStatsData
    {
        // Usage is counted from the code denormalised onto the order, not from
        // shop_promocodes.used_count — that counter has no time dimension and
        // survives the promocode being deleted.
        $used = Order::query()
            ->whereNotNull('promocode_code')
            ->whereBetween('created_at', [$period->from, $period->to]);

        $usage = (clone $used)
            ->selectRaw('COUNT(*) AS uses, COALESCE(SUM(discount_amount), 0) AS discount_given')
            ->first();

        $top = (clone $used)
            ->selectRaw('promocode_code, COUNT(*) AS uses')
            ->groupBy('promocode_code')
            ->orderByDesc('uses')
            ->first();

        $revenueInPeriod = (float) Order::query()
            ->whereBetween('created_at', [$period->from, $period->to])
            ->sum('total_amount');

        return new PromocodeStatsData(
            // "Active" is whatever the model scope says it is — do not restate
            // the rule here.
            activeCount: Promocode::query()->active()->count(),
            expiredCount: Promocode::query()
                ->whereNotNull('expires_at')
                ->where('expires_at', '<', now())
                ->count(),
            usesInPeriod: (int) ($usage->uses ?? 0),
            discountGiven: (float) ($usage->discount_given ?? 0),
            revenueInPeriod: $revenueInPeriod,
            topCode: $top?->promocode_code,
            topCodeUses: (int) ($top->uses ?? 0),
        );
    }
}
