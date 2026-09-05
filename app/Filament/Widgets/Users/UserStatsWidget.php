<?php

namespace App\Filament\Widgets\Users;

use App\Filament\Dashboard\Concerns\FormatsDashboardStats;
use App\Filament\Dashboard\Concerns\InteractsWithDashboardPeriod;
use App\Filament\Dashboard\Queries\Users\GetUserStatsQuery;
use App\Filament\Resources\Subscribers\SubscriberResource;
use App\Filament\Resources\Users\UserResource;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class UserStatsWidget extends StatsOverviewWidget
{
    use FormatsDashboardStats;
    use InteractsWithDashboardPeriod;

    protected static ?int $sort = 10;

    protected int|string|array $columnSpan = 'full';

    protected function getHeading(): ?string
    {
        return __('admin.dashboard.widgets.users');
    }

    /**
     * @return array<Stat>
     */
    protected function getStats(): array
    {
        $stats = app(GetUserStatsQuery::class)->handle($this->period());

        $usersChange = $this->percentChange($stats->newUsers, $stats->previousNewUsers);

        // The last two cards belong to the subscribers module: null means it is
        // switched off, so they are dropped rather than shown empty.
        return array_values(array_filter([
            Stat::make(__('admin.dashboard.stats.users_total'), $stats->usersTotal)
                ->description(__('admin.dashboard.descriptions.in_period', ['count' => $stats->newUsers]))
                ->icon('heroicon-o-users')
                ->url(UserResource::getUrl()),

            Stat::make(__('admin.dashboard.stats.new_users'), $stats->newUsers)
                ->description($this->trendDescription($usersChange))
                ->descriptionColor($this->trendColor($usersChange))
                ->descriptionIcon($this->trendIcon($usersChange))
                ->chart($stats->registrationsPerDay)
                ->icon('heroicon-o-user-plus'),

            $stats->subscribersTotal === null
                ? null
                : Stat::make(__('admin.dashboard.stats.subscribers'), $stats->subscribersTotal)
                    ->description(__('admin.dashboard.descriptions.in_period', ['count' => $stats->newSubscribers]))
                    ->icon('heroicon-o-envelope-open')
                    ->url(SubscriberResource::getUrl()),

            $stats->failedSubscribers === null
                ? null
                : Stat::make(__('admin.dashboard.stats.failed_integrations'), $stats->failedSubscribers)
                    ->color($this->alertColor($stats->failedSubscribers))
                    ->icon('heroicon-o-exclamation-circle')
                    ->url(SubscriberResource::getUrl(parameters: [
                        'tableFilters' => ['error' => ['value' => '1']],
                    ])),
        ]));
    }
}
