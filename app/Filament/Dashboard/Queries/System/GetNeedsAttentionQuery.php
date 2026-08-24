<?php

namespace App\Filament\Dashboard\Queries\System;

use App\Modules\Core\Support\ModuleGate;
use App\Modules\Feedback\Models\Feedback;
use App\Modules\Notification\Enums\LogLetterStatusEnum;
use App\Modules\Notification\Models\LogLetter;
use App\Modules\Shop\Models\ProductReview;
use App\Modules\ShopOrders\Enums\PaymentStatusEnum;
use App\Modules\ShopOrders\Models\Order;
use App\Modules\Subscribers\Models\Subscriber;

class GetNeedsAttentionQuery
{
    /**
     * How long an order may stay unpaid before it is worth a look.
     */
    public const STALE_UNPAID_DAYS = 3;

    /**
     * Counters of things waiting for an admin action right now — the period
     * filter is deliberately ignored.
     *
     * A disabled module yields `null` rather than `0`, so the widget can drop
     * the card entirely instead of showing a permanent, meaningless zero.
     *
     * Not cached: an admin coming back from a resource must see the result of
     * the action they have just taken.
     *
     * @return array<string, int|null>
     */
    public function handle(): array
    {
        return [
            'pendingReviews' => ModuleGate::isEnabled('shop')
                ? ProductReview::query()->where('display', false)->count()
                : null,

            'unprocessedFeedback' => ModuleGate::isEnabled('feedback')
                ? Feedback::query()->where('processed', false)->count()
                : null,

            'staleUnpaidOrders' => ModuleGate::isEnabled('shop_orders')
                ? Order::query()
                    ->where('payment_status', PaymentStatusEnum::UNPAID)
                    ->where('created_at', '<', now()->subDays(self::STALE_UNPAID_DAYS))
                    ->count()
                : null,

            'failedSubscribers' => ModuleGate::isEnabled('subscribers')
                ? Subscriber::query()->whereNotNull('error')->count()
                : null,

            // Letters are part of the always-on Notification module.
            'failedLetters' => LogLetter::query()
                ->where('status', LogLetterStatusEnum::FAILED)
                ->where('created_at', '>=', now()->subDay())
                ->count(),
        ];
    }
}
