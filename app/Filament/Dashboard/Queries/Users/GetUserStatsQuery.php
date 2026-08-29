<?php

namespace App\Filament\Dashboard\Queries\Users;

use App\Filament\Dashboard\Dtos\DashboardPeriodData;
use App\Filament\Dashboard\Dtos\Users\UserStatsData;
use App\Modules\User\Models\User;
use App\Filament\Dashboard\Support\DashboardSeries;
use App\Modules\Core\Support\ModuleGate;
use App\Modules\Subscriber\Models\Subscriber;

class GetUserStatsQuery
{
    public function handle(DashboardPeriodData $period): UserStatsData
    {
        $hasSubscribers = ModuleGate::isEnabled('subscriber');

        return new UserStatsData(
            usersTotal: User::query()->count(),
            newUsers: User::query()
                ->whereBetween('created_at', [$period->from, $period->to])
                ->count(),
            previousNewUsers: User::query()
                ->whereBetween('created_at', [$period->previousFrom, $period->previousTo])
                ->count(),
            registrationsPerDay: $this->registrationsPerDay($period),
            // A disabled module yields null so the widget can drop the cards
            // instead of showing permanent zeros.
            subscribersTotal: $hasSubscribers ? Subscriber::query()->count() : null,
            newSubscribers: $hasSubscribers
                ? Subscriber::query()->whereBetween('created_at', [$period->from, $period->to])->count()
                : null,
            failedSubscribers: $hasSubscribers
                ? Subscriber::query()->whereNotNull('error')->count()
                : null,
        );
    }

    /**
     * Registrations per bucket, gaps filled with zeros so the sparkline keeps
     * the shape of the calendar.
     *
     * @return array<int, float>
     */
    private function registrationsPerDay(DashboardPeriodData $period): array
    {
        $rows = User::query()
            ->whereBetween('created_at', [$period->from, $period->to])
            ->selectRaw(
                DashboardSeries::bucketExpression($period, 'created_at')
                .' AS bucket, COUNT(*) AS total'
            )
            ->groupBy('bucket')
            ->pluck('total', 'bucket');

        $series = [];

        foreach (array_keys(DashboardSeries::buckets($period)) as $key) {
            $series[] = (float) ($rows[$key] ?? 0);
        }

        return $series;
    }
}
