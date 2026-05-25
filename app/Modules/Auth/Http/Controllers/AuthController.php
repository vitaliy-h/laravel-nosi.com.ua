<?php

namespace App\Modules\Auth\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Modules\Auth\Http\Requests\LoginRequest;
use App\Modules\Auth\Http\Requests\PasswordRecoveryRequest;
use App\Modules\Auth\Http\Requests\PasswordResetRequest;
use App\Modules\Auth\Http\Requests\RegisterRequest;
use App\Modules\Core\Dtos\SendLetterTemplateData;
use App\Modules\Core\Enums\LetterEnum;
use App\Modules\Core\Facades\LetterTemplateFacade;
use App\Modules\Core\Repositories\UsersRepository;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;

class AuthController extends Controller
{

    public function __construct(private readonly UsersRepository $usersRepository)
    {
    }

    public function index(Request $request)
    {
        return view('pages.auth.index', [
            'mode' => $request->input('mode', 'login'),
            'token' => $request->input('token'),
            'email' => $request->input('email'),
        ]);
    }

    public function login(LoginRequest $request): RedirectResponse
    {
        $credentials = $request->only([
            'email',
            'password',
        ]);

        $remember = (bool)$request->boolean('remember_me');

        if (!Auth::attempt($credentials, $remember)) {
            return back()
                ->withInput($request->except('password'))
                ->withErrors([
                    'email' => t('auth.invalid_credentials'),
                ]);
        }

        $request->session()->regenerate();

        return redirect()->intended('/');
    }

    public function logout(Request $request): RedirectResponse
    {
        Auth::logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return redirect('/');
    }

    public function register(RegisterRequest $request): RedirectResponse
    {
        $user = User::query()->create([
            'name' => $request->string('name'),
            'email' => $request->string('email'),
            'password' => Hash::make($request->string('password')),
        ]);

        Auth::login($user);

        $request->session()->regenerate();

        return redirect('/');
    }

    public function passwordRecovery(PasswordRecoveryRequest $request): RedirectResponse
    {
        $user = $this->usersRepository->getByEmail($request->email);
        if (!$user) {
            return back()->with('error', t('auth.user_not_found'));
        }

        $token = Password::broker()->createToken($user);

        $resetUrl = route('auth', [
            'mode' => 'password_reset',
            'token' => $token,
            'email' => $user->email,
        ]);
        LetterTemplateFacade::send(
            new SendLetterTemplateData(
                key: LetterEnum::PASSWORD_RESET,
                replace: [
                    'reset_url' => $resetUrl,
                ],
                to: $request->email
            )
        );
        return back()->with('success', t('auth.password_reset_email_sent'));
    }

    public function passwordReset(PasswordResetRequest $request): RedirectResponse
    {
        $status = Password::reset(
            $request->only(
                'email',
                'password',
                'password_confirmation',
                'token'
            ),
            function ($user) use ($request) {

                $user->forceFill([
                    'password' => Hash::make($request->password),
                    'remember_token' => Str::random(60),
                ])->save();

                event(new PasswordReset($user));
            }
        );

        if ($status !== Password::PASSWORD_RESET) {

            return back()->withErrors([
                'email' => t('auth.password_reset_error'),
            ]);
        }

        return redirect()
            ->route('auth')
            ->with('success', t('auth.password_reset_success'));
    }
}
