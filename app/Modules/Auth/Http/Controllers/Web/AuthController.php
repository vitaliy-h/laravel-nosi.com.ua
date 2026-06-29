<?php

namespace App\Modules\Auth\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Modules\Auth\Actions\LoginAction;
use App\Modules\Auth\Actions\RegisterAction;
use App\Modules\Auth\Actions\ResetPasswordAction;
use App\Modules\Auth\Actions\SendPasswordResetAction;
use App\Modules\Auth\Dtos\LoginData;
use App\Modules\Auth\Dtos\PasswordRecoveryData;
use App\Modules\Auth\Dtos\PasswordResetData;
use App\Modules\Auth\Dtos\RegisterData;
use App\Modules\Auth\Http\Requests\LoginRequest;
use App\Modules\Auth\Http\Requests\RegisterRequest;
use App\Modules\Auth\Http\Requests\Web\PasswordRecoveryRequest;
use App\Modules\Auth\Http\Requests\Web\PasswordResetRequest;
use App\Modules\Core\Enums\PageModule;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    public function login(LoginRequest $request, LoginAction $action): RedirectResponse
    {
        $remember = (bool)$request->boolean('remember_me');

        $user = $action->handle(
            LoginData::from($request)
        );

        Auth::login($user, $remember);
        $request->session()->regenerate();

        return redirect()->to(module_url('profile'));
    }

    public function logout(Request $request): RedirectResponse
    {
        Auth::logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return redirect(module_url('index'));
    }

    public function register(RegisterRequest $request, RegisterAction $action): RedirectResponse
    {
        $user = $action->handle(
            RegisterData::from($request)
        );

        Auth::login($user);

        $request->session()->regenerate();

        return redirect(module_url('profile'));
    }

    public function passwordRecovery(PasswordRecoveryRequest $request, SendPasswordResetAction $action): RedirectResponse
    {
        $action->handle(
            PasswordRecoveryData::from($request)
        );

        return back()->with('success', t('auth.password_reset_email_sent'));
    }

    public function passwordReset(PasswordResetRequest $request, ResetPasswordAction $action): RedirectResponse
    {
        $action->handle(
            PasswordResetData::from($request)
        );
        return redirect()
            ->to(module_url('auth'))
            ->with('success', t('auth.password_reset_success'));
    }
}
