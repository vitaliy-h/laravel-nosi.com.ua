<?php

namespace App\Modules\UserAuth\Actions;

use App\Modules\User\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ImpersonateAction
{
    public function handle(Request $request, User $targetUser): void
    {
        $adminId = Auth::id();

        Auth::login($targetUser);

        $request->session()->regenerate();
        $request->session()->put('impersonating_admin_id', $adminId);
    }
}
