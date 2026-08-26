<?php

namespace App\Modules\Subscriber\Actions;

use App\Modules\Subscriber\Dtos\SubscribeData;
use App\Modules\Subscriber\Dtos\SubscribeResultData;
use App\Modules\Subscriber\Models\Subscriber;

class SubscribeAction
{
    /**
     * Idempotent subscribe: re-submitting an already known email is not an
     * error, the existing record is returned untouched (its CRM integration
     * state must not be reset by a repeated subscription).
     */
    public function handle(SubscribeData $data): SubscribeResultData
    {
        $subscriber = Subscriber::query()
            ->where('email', $data->email)
            ->first();

        if ($subscriber) {
            return new SubscribeResultData(
                subscriber: $subscriber,
                alreadyExists: true,
            );
        }

        $subscriber = Subscriber::create([
            'email' => $data->email,
            'ip' => $data->ip,
            'integrated_at' => null,
            'error' => null,
        ]);

        return new SubscribeResultData(
            subscriber: $subscriber,
            alreadyExists: false,
        );
    }
}
