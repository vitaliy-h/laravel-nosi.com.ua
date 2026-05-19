<?php

namespace App\Modules\Feedback\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Core\Dtos\SendLetterTemplateData;
use App\Modules\Core\Enums\LetterEnum;
use App\Modules\Core\Facades\LetterTemplateFacade;
use Illuminate\Http\Request;

class FeedbackController extends Controller
{
    public function index(Request $request)
    {
        return view('pages.feedback');
    }

    public function save(Request $request): \Illuminate\Http\RedirectResponse
    {
        LetterTemplateFacade::send(
            new SendLetterTemplateData(
                key: LetterEnum::FEEDBACK,
                replace: [
                    'user_name' => $request->input('name'),
                    'message' => $request->input('message'),
                ],
            )
        );
        /*todo save feedback*/
        return redirect()->back()->with('success', t('feedback.saved'));
    }
}
