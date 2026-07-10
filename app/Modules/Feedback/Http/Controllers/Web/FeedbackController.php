<?php

namespace App\Modules\Feedback\Http\Controllers\Web;

use App\Modules\Core\Abstract\BaseWebController;
use App\Modules\Feedback\Actions\CreateFeedbackAction;
use App\Modules\Feedback\Dtos\CreateFeedbackData;
use App\Modules\Feedback\Http\Requests\StoreFeedbackRequest;
use Illuminate\Http\RedirectResponse;

class FeedbackController extends BaseWebController
{
    public function store(StoreFeedbackRequest $request, CreateFeedbackAction $createFeedback): RedirectResponse
    {
        $createFeedback->handle(new CreateFeedbackData(
            name: $request->validated('name'),
            email: $request->validated('email'),
            message: $request->validated('message'),
            ip: $request->ip(),
        ));

        return redirect()->back()->with('success', t('feedback.saved'));
    }
}
