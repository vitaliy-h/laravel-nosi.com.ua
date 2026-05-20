<?php

namespace App\Modules\Feedback\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Core\Dtos\SendLetterTemplateData;
use App\Modules\Core\Enums\LetterEnum;
use App\Modules\Core\Facades\LetterTemplateFacade;
use App\Models\Feedback;
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
                    'user_email' => $request->input('email'),
                    'message' => $request->input('message'),
                ],
            )
        );

        Feedback::create([
            'name' => $request->input('name'),
            'email' => $request->input('email'),
            'ip' => $request->ip(),
            'message' => $request->input('message'),
        ]);

        return redirect()->back()->with('success', t('feedback.saved'));
    }
}
