<?php

namespace App\Modules\Profile\Http\Controllers\Web;

use App\Modules\Core\Abstract\BaseWebController;
use App\Modules\Profile\Actions\DeleteUserAccountAction;
use App\Modules\Profile\Actions\UpdateUserDataAction;
use App\Modules\Profile\Actions\UpdateUserPasswordAction;
use App\Modules\Profile\Dtos\ProfileUpdateUserDataData;
use App\Modules\Profile\Http\Requests\ProfileDeleteUserAccountRequest;
use App\Modules\Profile\Http\Requests\ProfileUpdateUserDataRequest;
use App\Modules\Profile\Http\Requests\ProfileUpdateUserPasswordRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;

class ProfileController extends BaseWebController
{
    public function __construct(
        private readonly UpdateUserDataAction $updateUserDataAction,
        private readonly UpdateUserPasswordAction $updateUserPasswordAction,
        private readonly DeleteUserAccountAction $deleteUserAccountAction,
    ) {}

    public function updateUserData(ProfileUpdateUserDataRequest $request): JsonResponse|RedirectResponse
    {
        $this->updateUserDataAction->handle(
            $request->user(),
            ProfileUpdateUserDataData::from($request->validated())
        );

        // AJAX (data-abcr form): re-render the form fragment with a flash message visible in the current request.
        // session()->now() keeps the flash in this request only — flash() would reappear after a reload.
        if ($request->ajax()) {
            session()->now('user_data_success', t('profile.user_data_updated'));

            return response()->json([
                'actions' => [
                    [
                        'method' => 'replaceWith',
                        'selector' => '#form-user-data',
                        'content' => $this->render('pages.profile.components.edit_user_data', [
                            'user' => $request->user(),
                        ])->render(),
                    ],
                ],
            ]);
        }

        // Regular form submit: redirect back so the browser handles the POST/Redirect/GET cycle.
        return back()->with('user_data_success', t('profile.user_data_updated'));
    }

    public function updateUserPassword(ProfileUpdateUserPasswordRequest $request): RedirectResponse|JsonResponse
    {
        $this->updateUserPasswordAction->handle(
            $request->user(),
            $request->input('password')
        );

        // Same AJAX / regular-form branching as updateUserData above.
        if ($request->ajax()) {
            session()->now('password_change_success', t('profile.password_updated'));

            return response()->json([
                'actions' => [
                    [
                        'method' => 'replaceWith',
                        'selector' => '#form-user-password',
                        'content' => $this->render('pages.profile.components.edit_user_password', [
                            'user' => $request->user(),
                        ])->render(),
                    ],
                ],
            ]);
        }

        return back()->with('password_change_success', t('profile.password_updated'));
    }

    public function deleteUserAccount(ProfileDeleteUserAccountRequest $request): RedirectResponse
    {
        $user = $request->user();

        Auth::logout();

        $this->deleteUserAccountAction->handle($user);

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->to(save_route('index.index'));
    }
}
