<?php

namespace App\Modules\TextPage\Http\Controllers\Web;

use App\Http\Controllers\Controller;
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
        return view('pages.text_page');
    }
}
