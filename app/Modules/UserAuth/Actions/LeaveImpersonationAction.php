<?php

namespace App\Modules\UserAuth\Actions;

use App\Modules\User\Models\User;
use Illuminate\Contracts\Session\Session;
use Illuminate\Support\Facades\Auth;

class LeaveImpersonationAction
{
    /**
     * Restores the admin stored by ImpersonateAction. If that account is gone,
     * the session is dropped entirely rather than left half-restored.
     */
    public function handle(Session $session): void
    {
        $adminId = $session->pull(ImpersonateAction::SESSION_KEY);

        $admin = User::find($adminId);

        if ($admin) {
            Auth::login($admin);
            $session->regenerate();

            return;
        }

        Auth::logout();
        $session->invalidate();
        $session->regenerateToken();
    }
}
