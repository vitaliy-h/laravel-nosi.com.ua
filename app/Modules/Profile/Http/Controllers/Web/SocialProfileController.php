<?php

namespace App\Modules\Profile\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Modules\Auth\Actions\DisconnectSocialProfileAction;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class SocialProfileController extends Controller
{
    public function __construct(
        private readonly DisconnectSocialProfileAction $disconnect,
    ) {}

    public function disconnect(Request $request, string $provider): RedirectResponse
    {
        try {
            $this->disconnect->handle(auth()->user(), $provider);

            return redirect()->to(module_url('profile', 'edit'))
                ->with('success', __('dictionary.social.disconnected'));
        } catch (\Illuminate\Validation\ValidationException $e) {
            return redirect()->to(module_url('profile', 'edit'))
                ->withErrors($e->errors());
        }
    }
}
