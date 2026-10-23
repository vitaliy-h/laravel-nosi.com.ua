<?php

namespace App\Filament\Widgets\Review;

use App\Filament\Dashboard\Concerns\FormatsDashboardStats;
use App\Filament\Dashboard\Concerns\InteractsWithDashboardPeriod;
use App\Filament\Dashboard\Queries\Review\GetReviewStatsQuery;
use App\Filament\Resources\Home\Reviews\ReviewResource;
use App\Modules\Core\Support\ModuleGate;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class ReviewStatsWidget extends StatsOverviewWidget
{
    use FormatsDashboardStats;
    use InteractsWithDashboardPeriod;

    protected static ?int $sort = 9;

    protected int|string|array $columnSpan = 'full';

    public static function canView(): bool
    {
        return ModuleGate::isEnabled('review');
    }

    protected function getHeading(): ?string
    {
        return __('admin.dashboard.widgets.reviews');
    }

    /**
     * @return array<Stat>
     */
    protected function getStats(): array
    {
        $stats = app(GetReviewStatsQuery::class)->handle($this->period());

        return [
            Stat::make(__('admin.dashboard.stats.average_rating'), number_format($stats->averageRating, 2))
                ->description(__('admin.dashboard.descriptions.visible_reviews'))
                ->icon('heroicon-o-star'),

            Stat::make(__('admin.dashboard.stats.new_reviews'), $stats->newReviews)
                ->icon('heroicon-o-chat-bubble-bottom-center-text'),

            Stat::make(__('admin.dashboard.stats.pending_moderation'), $stats->pendingModeration)
                ->color($this->alertColor($stats->pendingModeration, 'warning'))
                ->icon('heroicon-o-eye-slash')
                ->url(ReviewResource::getUrl(parameters: [
                    'tableFilters' => ['display' => ['value' => '0']],
                ])),

            Stat::make(__('admin.dashboard.stats.rating_breakdown'), array_sum($stats->breakdown))
                ->description(__('admin.dashboard.descriptions.visible_reviews'))
                // Bars run 5 stars → 1 star, left to right.
                ->chart($stats->chart()),
        ];
    }
}
