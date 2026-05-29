<?php

namespace App\Modules\Profile\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Modules\Core\Enums\PageModule;
use App\Modules\Profile\Actions\DeleteUserAccountAction;
use App\Modules\Profile\Actions\UpdateUserDataAction;
use App\Modules\Profile\Actions\UpdateUserPasswordAction;
use App\Modules\Profile\Dtos\ProfileUpdateUserDataData;
use App\Modules\Profile\Http\Requests\ProfileDeleteUserAccountRequest;
use App\Modules\Profile\Http\Requests\ProfileUpdateUserDataRequest;
use App\Modules\Profile\Http\Requests\ProfileUpdateUserPasswordRequest;
use Illuminate\Support\Facades\Auth;

class ProfileController extends Controller
{
    public function __construct(
        private readonly UpdateUserDataAction     $updateUserDataAction,
        private readonly UpdateUserPasswordAction $updateUserPasswordAction,
        private readonly DeleteUserAccountAction  $deleteUserAccountAction,
    )
    {
    }

    public function updateUserData(ProfileUpdateUserDataRequest $request): \Illuminate\Http\RedirectResponse
    {
        $this->updateUserDataAction->handle(
            $request->user(),
            ProfileUpdateUserDataData::from($request->validated())
        );

        return back()->with('user_data_success', t('profile.user_data_updated'));
    }

    public function updateUserPassword(ProfileUpdateUserPasswordRequest $request): \Illuminate\Http\RedirectResponse
    {
        $this->updateUserPasswordAction->handle(
            $request->user(),
            $request->input('password')
        );

        return back()->with('password_change_success', t('profile.password_updated'));
    }

    public function deleteUserAccount(ProfileDeleteUserAccountRequest $request): \Illuminate\Http\RedirectResponse
    {
        $user = $request->user();

        Auth::logout();

        $this->deleteUserAccountAction->handle($user);

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->to(module_url(PageModule::INDEX));
    }
}
