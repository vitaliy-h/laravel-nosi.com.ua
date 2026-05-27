<?php

namespace App\Modules\Auth\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Core\Enums\PageModule;
use App\Modules\Core\Interfaces\ModuleIndexControllerInterface;
use Illuminate\Http\Request;


class IndexController extends Controller implements ModuleIndexControllerInterface
{
    private const MODES = [
        'login',
        'register',
        'password_recovery',
        'password_reset',
    ];

    public function accessCheck(): \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse|null
    {
        if (auth()->check()) {
            return redirect()->to(module_url(PageModule::PROFILE));
        }
        return null;
    }

    public function index(Request $request, array $segments = [])
    {
        $mode = 'login';

        foreach (self::MODES as $availableMode) {

            if ($request->has($availableMode)) {
                $mode = $availableMode;
                break;
            }
        }
        return view('pages.auth.index', [
            'mode' => $mode,
            'token' => $request->input('token'),
            'email' => $request->input('email'),
        ]);
    }
}
