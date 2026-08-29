<?php

namespace App\Modules\UserAuth\Http\Controllers\Web;

use App\Modules\ShopCart\Actions\MergeCartOnAuthAction;
use App\Modules\UserAuth\Actions\LoginAction;
use App\Modules\UserAuth\Actions\RegisterAction;
use App\Modules\UserAuth\Actions\ResetPasswordAction;
use App\Modules\UserAuth\Actions\SendPasswordResetAction;
use App\Modules\UserAuth\Dtos\LoginData;
use App\Modules\UserAuth\Dtos\PasswordRecoveryData;
use App\Modules\UserAuth\Dtos\PasswordResetData;
use App\Modules\UserAuth\Dtos\RegisterData;
use App\Modules\UserAuth\Http\Requests\LoginRequest;
use App\Modules\UserAuth\Http\Requests\RegisterRequest;
use App\Modules\UserAuth\Http\Requests\Web\PasswordRecoveryRequest;
use App\Modules\UserAuth\Http\Requests\Web\PasswordResetRequest;
use App\Modules\Core\Abstract\BaseWebController;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthController extends BaseWebController
{
    public function login(LoginRequest $request, LoginAction $action, MergeCartOnAuthAction $mergeCart): RedirectResponse
    {
        $remember = (bool)$request->boolean('remember_me');

        $user = $action->handle(
            LoginData::from($request->validated())
        );

        Auth::login($user, $remember);
        $request->session()->regenerate();

        $mergeCart->handle($user);

        return redirect()->to(save_route('profile.edit'));
    }

    public function logout(Request $request): RedirectResponse
    {
        Auth::logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return redirect(save_route('index.index'));
    }

    public function register(RegisterRequest $request, RegisterAction $action, MergeCartOnAuthAction $mergeCart): RedirectResponse
    {
        $user = $action->handle(
            RegisterData::from($request->validated())
        );

        Auth::login($user);

        $request->session()->regenerate();

        $mergeCart->handle($user);

        return redirect(save_route('profile.edit'));
    }

    public function passwordRecovery(PasswordRecoveryRequest $request, SendPasswordResetAction $action): RedirectResponse
    {
        $action->handle(
            PasswordRecoveryData::from($request->validated())
        );

        return back()->with('success', t('auth.password_reset_email_sent'));
    }

    public function passwordReset(PasswordResetRequest $request, ResetPasswordAction $action): RedirectResponse
    {
        $action->handle(
            PasswordResetData::from($request->validated())
        );
        return redirect()
            ->to(save_route('auth.index'))
            ->with('success', t('auth.password_reset_success'));
    }
}
