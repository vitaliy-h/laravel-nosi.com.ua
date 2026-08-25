<?php

namespace App\Filament\Dashboard\Queries\System;

use App\Filament\Dashboard\Dtos\System\SystemStatsData;
use App\Modules\Core\Support\ModuleGate;
use App\Modules\Notification\Enums\LogLetterStatusEnum;
use App\Modules\Notification\Enums\LogSmsStatusEnum;
use App\Modules\Notification\Models\LogLetter;
use App\Modules\Notification\Models\LogSms;
use Composer\InstalledVersions;
use Illuminate\Database\Eloquent\Builder;
use Throwable;

class GetSystemStatsQuery
{
    /**
     * Environment and delivery health for the super admin block. Always a
     * snapshot of the last 24 hours, independent of the period filter.
     */
    public function handle(): SystemStatsData
    {
        $since = now()->subDay();

        $letters = $this->deliveryCounts(
            LogLetter::query()->where('created_at', '>=', $since),
            LogLetterStatusEnum::SENT->value,
            LogLetterStatusEnum::FAILED->value,
        );

        // A disabled sms module yields null counters, so the widget can drop
        // the card rather than report on a channel that is not in use.
        $sms = ModuleGate::isEnabled('sms')
            ? $this->deliveryCounts(
                LogSms::query()->where('created_at', '>=', $since),
                LogSmsStatusEnum::SENT->value,
                LogSmsStatusEnum::FAILED->value,
            )
            : ['sent' => null, 'failed' => null];

        return new SystemStatsData(
            environment: (string) app()->environment(),
            debug: (bool) config('app.debug'),
            phpVersion: PHP_VERSION,
            laravelVersion: $this->packageVersion('laravel/framework') ?? app()->version(),
            filamentVersion: $this->packageVersion('filament/filament') ?? '—',
            mailSent: $letters['sent'],
            mailFailed: $letters['failed'],
            smsSent: $sms['sent'],
            smsFailed: $sms['failed'],
        );
    }

    /**
     * @return array{sent: int, failed: int}
     */
    private function deliveryCounts(Builder $query, string $sent, string $failed): array
    {
        $counts = $query
            ->selectRaw('status, COUNT(*) AS total')
            ->groupBy('status')
            ->pluck('total', 'status');

        return [
            'sent' => (int) ($counts[$sent] ?? 0),
            'failed' => (int) ($counts[$failed] ?? 0),
        ];
    }

    private function packageVersion(string $package): ?string
    {
        try {
            return InstalledVersions::getPrettyVersion($package);
        } catch (Throwable) {
            return null;
        }
    }
}
