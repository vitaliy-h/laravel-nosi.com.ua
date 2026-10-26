<?php

namespace App\Modules\Profile\Http\Controllers\Web;

use App\Modules\Core\Abstract\BaseWebController;
use App\Modules\UserAuth\Actions\DisconnectSocialProfileAction;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class SocialProfileController extends BaseWebController
{
    public function __construct(
        private readonly DisconnectSocialProfileAction $disconnect,
    ) {}

    public function disconnect(Request $request, string $provider): RedirectResponse
    {
        try {
            $this->disconnect->handle(auth()->user(), $provider);

            return redirect()->to(save_route('profile.edit'))
                ->with('success', __('site.social.disconnected'));
        } catch (ValidationException $e) {
            return redirect()->to(save_route('profile.edit'))
                ->withErrors($e->errors());
        }
    }
}
