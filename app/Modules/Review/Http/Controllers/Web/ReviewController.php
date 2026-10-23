<?php

namespace App\Modules\Review\Http\Controllers\Web;

use App\Modules\Core\Abstract\BaseWebController;
use App\Modules\Review\Actions\CreateReviewAction;
use App\Modules\Review\Dtos\CreateReviewData;
use App\Modules\Review\Http\Requests\StoreReviewRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;

class ReviewController extends BaseWebController
{
    public function __construct(
        private readonly CreateReviewAction $createReview,
    ) {}

    public function store(StoreReviewRequest $request): JsonResponse|RedirectResponse
    {
        $user = Auth::user();

        $this->createReview->handle(new CreateReviewData(
            rating: (int) $request->validated('rating'),
            // What the customer typed wins (the form prefills it from the account);
            // a logged-in review stays linked to the account through userId.
            name: $request->validated('name'),
            email: $request->validated('email'),
            text: $request->validated('text'),
            city: $request->validated('city'),
            ip: $request->ip(),
            userId: $user?->getKey(),
        ));

        $message = t('home.review_saved');

        // AJAX (data-abcr form in the home page dialog): swap the form for the message.
        if ($request->ajax()) {
            return response()->json([
                'actions' => [
                    [
                        'method'   => 'html',
                        'selector' => '#review-dialog-body',
                        'content'  => $this->render('modules.index.partials.review_message', [
                            'message' => $message,
                        ])->render(),
                    ],
                ],
            ]);
        }

        // Regular form submit: redirect back so the browser handles POST/Redirect/GET.
        return back()->with('success', $message);
    }
}
