<?php

namespace App\Modules\Shop\Http\Controllers\Web;

use App\Modules\Core\Abstract\BaseWebController;
use App\Modules\Shop\Actions\CreateReviewAction;
use App\Modules\Shop\Dtos\CreateReviewData;
use App\Modules\Shop\Http\Requests\StoreReviewRequest;
use App\Modules\Shop\Models\Review;
use App\Modules\Shop\Queries\Product\FindProductByIdQuery;
use App\Modules\Shop\Queries\Review\GetReviewsByProductQuery;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ReviewController extends BaseWebController
{
    public function __construct(
        private readonly CreateReviewAction $createReview,
        private readonly FindProductByIdQuery $findProductById,
        private readonly GetReviewsByProductQuery $getReviewsByProduct,
    ) {}

    public function store(StoreReviewRequest $request): JsonResponse|RedirectResponse
    {
        $user = Auth::user();

        $this->createReview->handle(new CreateReviewData(
            productId: (int) $request->validated('product_id'),
            rating: (int) $request->validated('rating'),
            // A logged-in user always reviews under their own account identity,
            // whatever the form posted.
            name: $user?->name ?: $request->validated('name'),
            email: $user?->email ?: $request->validated('email'),
            text: $request->validated('text'),
            ip: $request->ip(),
            userId: $user?->getKey(),
        ));

        $message = t('shop.review_saved');

        // AJAX (data-abcr form): clear the form and render the message inline.
        if ($request->ajax()) {
            $actions = [
                [
                    'method' => 'clearForm',
                    'selector' => '#review-form',
                ],
                [
                    'method' => 'html',
                    'selector' => '#review-message',
                    'content' => view('components.review_message', [
                        'message' => $message,
                    ])->render(),
                ],
            ];

            // clearForm empties the readonly identity fields too — put them back.
            if ($user) {
                $actions[] = ['method' => 'prop', 'selector' => '#review-name', 'prop' => 'value', 'content' => $user->name];
                $actions[] = ['method' => 'prop', 'selector' => '#review-email', 'prop' => 'value', 'content' => $user->email];
            }

            return response()->json(['actions' => $actions]);
        }

        // Regular form submit: redirect back so the browser handles POST/Redirect/GET.
        return back()->with('success', $message);
    }

    /**
     * "Load more" for the reviews list: appends the next page of reviews and
     * re-renders the button (which carries the next page number).
     */
    public function list(Request $request): JsonResponse
    {
        $product = $this->findProductById->handle((int) $request->query('product'));
        $page = max(1, (int) $request->query('page', 1));

        $reviews = $this->getReviewsByProduct->handle($product, $page);

        $items = $reviews
            ->map(fn (Review $review) => view('pages.shop.partials.review-item', compact('review'))->render())
            ->implode('');

        return response()->json([
            'actions' => [
                [
                    'method' => 'append',
                    'selector' => '#reviews-list',
                    'content' => $items,
                ],
                [
                    'method' => 'replaceWith',
                    'selector' => '#reviews-load-more',
                    'content' => view('pages.shop.partials.reviews-load-more', compact('product', 'reviews'))->render(),
                ],
            ],
        ]);
    }
}
