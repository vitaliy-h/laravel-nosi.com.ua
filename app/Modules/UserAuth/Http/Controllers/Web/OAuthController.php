<?php

namespace App\Modules\UserAuth\Http\Controllers\Web;

use App\Modules\Core\Abstract\BaseWebController;
use App\Modules\Core\Enums\AuthLogActionEnum;
use App\Modules\Core\Services\AuthLogService;
use App\Modules\ShopCart\Actions\MergeCartOnAuthAction;
use App\Modules\ShopCart\Services\CartContext;
use App\Modules\UserAuth\Actions\ConnectSocialProfileAction;
use App\Modules\UserAuth\Actions\FindOrCreateSocialUserAction;
use Illuminate\Http\RedirectResponse;
use Laravel\Socialite\Facades\Socialite;

class OAuthController extends BaseWebController
{
    public function __construct(
        private readonly FindOrCreateSocialUserAction $findOrCreate,
        private readonly ConnectSocialProfileAction $connect,
        private readonly MergeCartOnAuthAction $mergeCart,
        private readonly CartContext $cart,
        private readonly AuthLogService $authLog,
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

            $this->authLog->failure(AuthLogActionEnum::SOCIAL, $provider);

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

        $this->authLog->success(
            AuthLogActionEnum::SOCIAL,
            $user->email ?? $provider,
            $user,
        );

        $this->mergeCart->handle($user, $this->cart->uuid());
        $this->cart->forgetCount();

        return redirect()->intended(save_route('profile.edit'));
    }

    private function validateProvider(string $provider): void
    {
        $allowed = config('social.providers', []);

        abort_unless(in_array($provider, $allowed), 404);
    }
}
