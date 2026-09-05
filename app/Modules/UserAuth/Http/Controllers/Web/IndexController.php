<?php

namespace App\Modules\UserAuth\Http\Controllers\Web;

use App\Modules\Core\Abstract\BaseWebController;
use App\Modules\UserAuth\ViewModels\AuthPageViewModel;
use Illuminate\Http\Request;

class IndexController extends BaseWebController
{
    public function index(Request $request)
    {
        $page = AuthPageViewModel::make(
            mode: $this->resolveMode($request),
            token: $request->input('token'),
            email: $request->input('email'),
        );

        return $this->render('modules.user_auth.index', $page);
    }

    /**
     * The form to open is picked by a bare query flag: /auth?register.
     */
    private function resolveMode(Request $request): string
    {
        foreach (AuthPageViewModel::MODES as $mode) {
            if ($request->has($mode)) {
                return $mode;
            }
        }

        return 'login';
    }
}
