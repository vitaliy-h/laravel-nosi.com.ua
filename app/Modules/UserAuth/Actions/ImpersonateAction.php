<?php

namespace App\Modules\UserAuth\Actions;

use App\Modules\User\Models\User;
use Illuminate\Contracts\Session\Session;
use Illuminate\Support\Facades\Auth;

class ImpersonateAction
{
    public const SESSION_KEY = 'impersonating_admin_id';

    /**
     * The session is what the impersonation state lives in, so that is what the
     * Action takes — passing the whole Request would tie it to HTTP for no gain.
     */
    public function handle(Session $session, User $targetUser): void
    {
        $adminId = Auth::id();

        Auth::login($targetUser);

        $session->regenerate();
        $session->put(self::SESSION_KEY, $adminId);
    }
}
