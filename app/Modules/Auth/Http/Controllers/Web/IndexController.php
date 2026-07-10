<?php

namespace App\Modules\Auth\Http\Controllers\Web;

use App\Modules\Core\Abstract\BaseWebController;
use Illuminate\Http\Request;

class IndexController extends BaseWebController
{
    private const MODES = [
        'login',
        'register',
        'password_recovery',
        'password_reset',
    ];

    public function index(Request $request)
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
