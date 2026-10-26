<?php

namespace App\Modules\Subscriber\Actions;

use App\Modules\Core\Dtos\ActionResult;
use App\Modules\Subscriber\Dtos\SubscribeData;
use App\Modules\Subscriber\Dtos\SubscriberData;
use App\Modules\Subscriber\Models\Subscriber;

class SubscribeAction
{
    /**
     * Idempotent subscribe: re-submitting an already known email is not an
     * error, the existing record is returned untouched (its CRM integration
     * state must not be reset by a repeated subscription). Both branches are a
     * success — they differ only in the message and the alreadyExists flag.
     *
     * @return ActionResult<SubscriberData>
     */
    public function handle(SubscribeData $data): ActionResult
    {
        $subscriber = Subscriber::query()
            ->where('email', $data->email)
            ->first();

        if ($subscriber) {
            return ActionResult::ok(
                new SubscriberData(subscriber: $subscriber, alreadyExists: true),
                __('site.subscribe.already'),
            );
        }

        $subscriber = Subscriber::create([
            'email'         => $data->email,
            'ip'            => $data->ip,
            'integrated_at' => null,
            'error'         => null,
        ]);

        return ActionResult::ok(
            new SubscriberData(subscriber: $subscriber, alreadyExists: false),
            __('site.subscribe.saved'),
        );
    }
}
