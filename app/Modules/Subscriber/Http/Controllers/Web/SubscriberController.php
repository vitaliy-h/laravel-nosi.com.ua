<?php

namespace App\Modules\Subscriber\Http\Controllers\Web;

use App\Modules\Core\Abstract\BaseWebController;
use App\Modules\Subscriber\Actions\SubscribeAction;
use App\Modules\Subscriber\Dtos\SubscribeData;
use App\Modules\Subscriber\Http\Requests\StoreSubscriberRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;

class SubscriberController extends BaseWebController
{
    public function __construct(
        private readonly SubscribeAction $subscribeAction,
    ) {}

    public function store(StoreSubscriberRequest $request): JsonResponse|RedirectResponse
    {
        $result = $this->subscribeAction->handle(new SubscribeData(
            email: $request->validated('email'),
            ip: $request->ip(),
        ));

        $message = $result->message;

        // AJAX (data-abcr form): clear the input and render the message inline.
        if ($request->ajax()) {
            return response()->json([
                'actions' => [
                    [
                        'method' => 'clearForm',
                        'selector' => '#newsletter-form',
                    ],
                    [
                        'method' => 'html',
                        'selector' => '#newsletter-message',
                        'content' => view('components.newsletter_message', [
                            'message' => $message,
                            'alreadyExists' => $result->data->alreadyExists,
                        ])->render(),
                    ],
                ],
            ]);
        }

        // Regular form submit: redirect back so the browser handles the POST/Redirect/GET cycle.
        return back()->with('success', $message);
    }
}
