<?php

namespace App\Modules\Feedback\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Feedback;
use App\Modules\Core\Dtos\SendLetterTemplateData;
use App\Modules\Core\Enums\LetterEnum;
use App\Modules\Core\Facades\LetterTemplateFacade;
use App\Modules\Core\Interfaces\ModuleIndexControllerInterface;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class IndexController extends Controller implements ModuleIndexControllerInterface
{
    public function accessCheck(): RedirectResponse|Response|null
    {
        return null;
    }

    public function index(Request $request, array $segments = [])
    {
        return view('pages.feedback');
    }
}
