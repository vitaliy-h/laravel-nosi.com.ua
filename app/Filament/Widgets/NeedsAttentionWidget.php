<?php

namespace App\Filament\Widgets;

use App\Filament\Resources\Feedback\FeedbackResource;
use App\Filament\Resources\LogLetters\LogLetterResource;
use App\Filament\Resources\Shop\Orders\OrderResource;
use App\Filament\Resources\Shop\ProductReviews\ProductReviewResource;
use App\Filament\Resources\Subscribers\SubscriberResource;
use App\Filament\Dashboard\Concerns\FormatsDashboardStats;
use App\Filament\Dashboard\Queries\System\GetNeedsAttentionQuery;
use App\Modules\Notification\Enums\LogLetterStatusEnum;
use App\Modules\ShopOrders\Enums\PaymentStatusEnum;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

/**
 * What is waiting for an admin right now. Ignores the period filter on
 * purpose, and every card links into its resource with the matching filter
 * already applied.
 */
class NeedsAttentionWidget extends StatsOverviewWidget
{
    use FormatsDashboardStats;

    protected static ?int $sort = 4;

    protected int|string|array $columnSpan = 'full';

    protected function getHeading(): ?string
    {
        return __('admin.dashboard.widgets.needs_attention');
    }

    /**
     * @return array<Stat>
     */
    protected function getStats(): array
    {
        $counters = app(GetNeedsAttentionQuery::class)->handle();

        // A null counter means the module is off — drop the card rather than
        // show a zero that can never change.
        return array_values(array_filter([
            $this->card(
                $counters['pendingReviews'],
                __('admin.dashboard.stats.pending_product_reviews'),
                'heroicon-o-star',
                fn (): string => ProductReviewResource::getUrl(parameters: [
                    'filters' => ['display' => ['value' => '0']],
                ]),
            ),
            $this->card(
                $counters['unprocessedFeedback'],
                __('admin.dashboard.stats.unprocessed_feedback'),
                'heroicon-o-chat-bubble-left-right',
                fn (): string => FeedbackResource::getUrl(parameters: [
                    'filters' => ['processed' => ['value' => '0']],
                ]),
            ),
            $this->card(
                $counters['staleUnpaidOrders'],
                __('admin.dashboard.stats.stale_unpaid_orders'),
                'heroicon-o-shopping-cart',
                fn (): string => OrderResource::getUrl(parameters: [
                    'filters' => ['payment_status' => ['value' => PaymentStatusEnum::UNPAID->value]],
                ]),
                __('admin.dashboard.descriptions.older_than_days', [
                    'days' => GetNeedsAttentionQuery::STALE_UNPAID_DAYS,
                ]),
            ),
            $this->card(
                $counters['failedSubscribers'],
                __('admin.dashboard.stats.failed_subscribers'),
                'heroicon-o-envelope-open',
                fn (): string => SubscriberResource::getUrl(parameters: [
                    'filters' => ['error' => ['value' => '1']],
                ]),
            ),
            $this->card(
                $counters['failedLetters'],
                __('admin.dashboard.stats.failed_letters'),
                'heroicon-o-inbox',
                fn (): string => LogLetterResource::getUrl(parameters: [
                    'filters' => ['status' => ['value' => LogLetterStatusEnum::FAILED->value]],
                ]),
                __('admin.dashboard.descriptions.last_24_hours'),
            ),
        ]));
    }

    private function card(?int $count, string $label, string $icon, \Closure $url, ?string $description = null): ?Stat
    {
        // A null counter means the module is off; a zero counter means the
        // module is on but nothing needs attention — drop the card either
        // way instead of showing a stat no admin needs to act on.
        if ($count === null || $count === 0) {
            return null;
        }

        $stat = Stat::make($label, $count)
            ->icon($icon)
            ->color($this->alertColor($count))
            ->url($url());

        return $description === null ? $stat : $stat->description($description);
    }
}
