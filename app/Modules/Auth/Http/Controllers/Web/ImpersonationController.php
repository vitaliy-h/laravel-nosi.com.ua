<?php

namespace App\Modules\Auth\Http\Controllers\Web;

use App\Modules\Auth\Actions\ImpersonateAction;
use App\Modules\Auth\Actions\LeaveImpersonationAction;
use App\Modules\Core\Abstract\BaseWebController;
use App\Modules\Core\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class ImpersonationController extends BaseWebController
{
    public function impersonate(Request $request, User $user, ImpersonateAction $action): RedirectResponse
    {
        $action->handle($request, $user);

        return redirect('/');
    }

    public function leave(Request $request, LeaveImpersonationAction $action): RedirectResponse
    {
        $action->handle($request);

        return redirect(config('filament.path', '/admin'));
    }
}
