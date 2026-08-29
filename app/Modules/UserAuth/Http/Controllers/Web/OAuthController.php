<?php

namespace App\Modules\UserAuth\Http\Controllers\Web;

use App\Modules\UserAuth\Actions\ConnectSocialProfileAction;
use App\Modules\UserAuth\Actions\FindOrCreateSocialUserAction;
use App\Modules\ShopCart\Actions\MergeCartOnAuthAction;
use App\Modules\Core\Abstract\BaseWebController;
use Illuminate\Http\RedirectResponse;
use Laravel\Socialite\Facades\Socialite;

class OAuthController extends BaseWebController
{
    public function __construct(
        private readonly FindOrCreateSocialUserAction $findOrCreate,
        private readonly ConnectSocialProfileAction   $connect,
        private readonly MergeCartOnAuthAction      $mergeCart,
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
            return redirect()->to(save_route('auth.index'))
                ->withErrors(['social' => t('social.auth_failed')]);
        }

        if (auth()->check()) {
            $socialProfile = $this->connect->handle(auth()->user(), $provider, $socialUser);

            return redirect()->to(save_route('profile.edit'))
                ->with('success', t('social.connected', ['identifier' => $socialProfile->identifier]));
        }

        $user = $this->findOrCreate->handle($provider, $socialUser);

        auth()->login($user, true);

        $this->mergeCart->handle($user);

        return redirect()->intended(save_route('profile.edit'));
    }

    private function validateProvider(string $provider): void
    {
        $allowed = config('social.providers', []);

        abort_unless(in_array($provider, $allowed), 404);
    }
}
