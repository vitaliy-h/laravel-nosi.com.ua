<?php

namespace App\Modules\Auth\Actions;

use App\Modules\Core\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LeaveImpersonationAction
{
    public function handle(Request $request): void
    {
        $adminId = $request->session()->pull('impersonating_admin_id');

        $admin = User::find($adminId);

        if ($admin) {
            Auth::login($admin);
            $request->session()->regenerate();
        } else {
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();
        }
    }
}
