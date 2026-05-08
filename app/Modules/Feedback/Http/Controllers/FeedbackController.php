<?php

namespace App\Modules\Feedback\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class FeedbackController extends Controller
{
    public function index(Request $request)
    {
        return view('pages.feedback');
    }

    public function save(Request $request): \Illuminate\Http\RedirectResponse
    {
        /*todo save feedback*/
        return redirect()->back()->with('success', t('feedback_saved'));
    }
}
