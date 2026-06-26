<?php

namespace App\Modules\Auth\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Modules\Auth\Actions\ConnectSocialProfileAction;
use App\Modules\Auth\Actions\FindOrCreateSocialUserAction;
use Illuminate\Http\RedirectResponse;
use Laravel\Socialite\Facades\Socialite;

class OAuthController extends Controller
{
    public function __construct(
        private readonly FindOrCreateSocialUserAction $findOrCreate,
        private readonly ConnectSocialProfileAction   $connect,
    ) {}

    public function redirect(string $provider): RedirectResponse
    {
        $this->validateProvider($provider);

        return Socialite::driver($provider)->redirect();
    }

    public function callback(string $provider): RedirectResponse
    {
        $this->validateProvider($provider);

        try {
            $socialUser = Socialite::driver($provider)->user();
        } catch (\Throwable) {
            return redirect()->to(module_url('auth'))
                ->withErrors(['social' => __('dictionary.social.auth_failed')]);
        }

        if (auth()->check()) {
            app(ConnectSocialProfileAction::class)->handle(auth()->user(), $provider, $socialUser);

            return redirect()->to(module_url('profile', 'edit'))
                ->with('success', __('dictionary.social.connected'));
        }

        $user = $this->findOrCreate->handle($provider, $socialUser);

        auth()->login($user, true);

        return redirect()->intended(module_url('profile', 'edit'));
    }

    private function validateProvider(string $provider): void
    {
        $allowed = config('social.providers', []);

        abort_unless(in_array($provider, $allowed), 404);
    }
}
