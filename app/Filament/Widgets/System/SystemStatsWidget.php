<?php

namespace App\Filament\Widgets\System;

use App\Filament\Resources\LogLetters\LogLetterResource;
use App\Filament\Resources\LogSms\LogSmsResource;
use App\Filament\Dashboard\Concerns\FormatsDashboardStats;
use App\Filament\Dashboard\Queries\System\GetSystemStatsQuery;
use App\Modules\Notification\Enums\LogLetterStatusEnum;
use App\Modules\Notification\Enums\LogSmsStatusEnum;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class SystemStatsWidget extends StatsOverviewWidget
{
    use FormatsDashboardStats;

    protected static ?int $sort = 11;

    protected int|string|array $columnSpan = 'full';

    protected function getHeading(): ?string
    {
        return __('admin.dashboard.widgets.system');
    }

    /**
     * @return array<Stat>
     */
    protected function getStats(): array
    {
        $stats = app(GetSystemStatsQuery::class)->handle();

        // Null sms counters mean the sms module is off: LogSmsResource is not
        // registered then, so the card cannot even build its link.
        return array_values(array_filter([
            Stat::make(__('admin.dashboard.stats.environment'), $stats->environment)
                ->description($stats->isDebugRisky() ? __('admin.dashboard.descriptions.debug_enabled') : null)
                ->color($stats->isDebugRisky() ? 'danger' : 'gray')
                ->icon('heroicon-o-server-stack'),

            Stat::make(__('admin.dashboard.stats.versions'), 'PHP '.$stats->phpVersion)
                ->description('Laravel '.$stats->laravelVersion.' · Filament '.$stats->filamentVersion)
                ->icon('heroicon-o-cube-transparent'),

            Stat::make(__('admin.dashboard.stats.mail_24h'), $stats->mailSent)
                ->description(__('admin.dashboard.descriptions.failed', ['count' => $stats->mailFailed]))
                ->descriptionColor($this->alertColor($stats->mailFailed))
                ->icon('heroicon-o-envelope')
                ->url(LogLetterResource::getUrl(parameters: [
                    'tableFilters' => ['status' => ['value' => LogLetterStatusEnum::FAILED->value]],
                ])),

            $stats->smsSent === null ? null : Stat::make(__('admin.dashboard.stats.sms_24h'), $stats->smsSent)
                ->description(__('admin.dashboard.descriptions.failed', ['count' => $stats->smsFailed]))
                ->descriptionColor($this->alertColor((int) $stats->smsFailed))
                ->icon('heroicon-o-chat-bubble-bottom-center-text')
                ->url(LogSmsResource::getUrl(parameters: [
                    'tableFilters' => ['status' => ['value' => LogSmsStatusEnum::FAILED->value]],
                ])),
        ]));
    }
}
