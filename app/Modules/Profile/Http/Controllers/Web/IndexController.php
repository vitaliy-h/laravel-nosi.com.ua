<?php

namespace App\Modules\Profile\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Modules\Core\Enums\PageModule;
use App\Modules\Core\Interfaces\ModuleIndexControllerInterface;
use Illuminate\Http\Request;


class IndexController extends Controller implements ModuleIndexControllerInterface
{

    public function accessCheck(): \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse|null
    {
        if (!auth()->check()) {
            return redirect()->to(module_url(PageModule::AUTH));
        }
        return null;
    }

    public function index(Request $request, array $segments = [])
    {
        $user = auth()->user();
        return view('pages.profile.index',compact('user'));
    }
}
