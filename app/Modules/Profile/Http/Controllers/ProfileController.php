<?php

namespace App\Modules\Profile\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Core\Enums\PageModule;
use App\Modules\Profile\Dtos\ProfileUpdateUserDataDto;
use App\Modules\Profile\Http\Requests\ProfileDeleteUserAccountRequest;
use App\Modules\Profile\Http\Requests\ProfileUpdateUserDataRequest;
use App\Modules\Profile\Http\Requests\ProfileUpdateUserPasswordRequest;
use App\Modules\Profile\Services\ProfileUserService;
use Illuminate\Support\Facades\Auth;

class ProfileController extends Controller
{
    public function __construct(private readonly ProfileUserService $service)
    {
    }

    public function updateUserData(ProfileUpdateUserDataRequest $request): \Illuminate\Http\RedirectResponse
    {
        try {
            $updateDto = ProfileUpdateUserDataDto::from($request->validated());
            $res = $this->service->updateUserData(auth()->user(), $updateDto);
            if ($res) {
                return redirect()->back()->with('user_data_success', t('profile.user_data_updated'));
            }
        } catch (\Throwable $e) {
            report($e);
            return back()->with('user_data_error', t('errors.something_went_wrong'));
        }
        return redirect()->back()->with('user_data_error', t('profile.user_data_update_error'));
    }

    public function updateUserPassword(ProfileUpdateUserPasswordRequest $request): \Illuminate\Http\RedirectResponse
    {
        try {
            $res = $this->service->updateUserPassword(auth()->user(), $request->input('password'));
            if ($res) {
                return redirect()->back()->with('password_change_success', t('profile.password_updated'));
            }
        } catch (\Throwable $e) {
            report($e);
            return back()->with('password_change_error', t('errors.something_went_wrong'));
        }
        return redirect()->back()->with('password_change_error', t('profile.password_updated_error'));
    }

    public function deleteUserAccount(ProfileDeleteUserAccountRequest $request): \Illuminate\Http\RedirectResponse
    {
        try {
            $user = auth()->user();

            Auth::logout();

            $this->service->deleteUserAccount($user);

            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect()->to(module_url(PageModule::INDEX));

        } catch (\Throwable $e) {
            report($e);

            return back()->with('error', t('errors.something_went_wrong'));
        }
    }
}
