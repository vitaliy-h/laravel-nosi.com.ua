<?php

namespace App\Modules\Profile\Http\Controllers\Api;

use App\Modules\Core\Abstract\BaseApiController;
use App\Modules\Profile\Actions\DeleteUserAccountAction;
use App\Modules\Profile\Actions\UpdateUserDataAction;
use App\Modules\Profile\Actions\UpdateUserPasswordAction;
use App\Modules\Profile\Dtos\ProfileUpdateUserDataData;
use App\Modules\Profile\Http\Requests\ProfileDeleteUserAccountRequest;
use App\Modules\Profile\Http\Requests\ProfileUpdateUserDataRequest;
use App\Modules\Profile\Http\Requests\ProfileUpdateUserPasswordRequest;
use App\Modules\Profile\Http\Resources\UserResource;

class ProfileController extends BaseApiController
{
    public function __construct(
        private readonly UpdateUserDataAction     $updateUserDataAction,
        private readonly UpdateUserPasswordAction $updateUserPasswordAction,
        private readonly DeleteUserAccountAction  $deleteUserAccountAction,
    )
    {
    }

    public function updateUserData(ProfileUpdateUserDataRequest $request): \Illuminate\Http\JsonResponse
    {
        $user = $request->user();
        $this->updateUserDataAction->handle(
            $user,
            ProfileUpdateUserDataData::from($request->validated())
        );
        return $this->success(
            new UserResource($user),
            t('profile.user_data_updated')
        );
    }

    public function updateUserPassword(ProfileUpdateUserPasswordRequest $request): \Illuminate\Http\JsonResponse
    {
        $user = $request->user();

        $this->updateUserPasswordAction->handle(
            $user,
            $request->input('password')
        );
        return $this->success(
            message: t('profile.password_updated')
        );
    }

    public function deleteUserAccount(ProfileDeleteUserAccountRequest $request): \Illuminate\Http\JsonResponse
    {
        $user = $request->user();

        $this->deleteUserAccountAction->handle($user);

        return $this->success(
            message: t('profile.account_deleted')
        );
    }
}
