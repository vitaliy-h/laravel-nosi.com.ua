<?php

namespace App\Modules\UserAuth\Http\Controllers\Api;

use App\Modules\UserAuth\Actions\LoginAction;
use App\Modules\UserAuth\Actions\RegisterAction;
use App\Modules\UserAuth\Dtos\LoginData;
use App\Modules\UserAuth\Dtos\RegisterData;
use App\Modules\UserAuth\Http\Requests\LoginRequest;
use App\Modules\UserAuth\Http\Requests\RegisterRequest;
use App\Modules\UserAuth\Http\Resources\AuthResource;
use App\Modules\Core\Abstract\BaseApiController;
use Illuminate\Http\Request;

class ApiAuthController extends BaseApiController
{
    public function login(LoginRequest $request, LoginAction $action): \Illuminate\Http\JsonResponse
    {
        $user = $action->handle(
            LoginData::from($request->validated())
        );

        $token = $user->createToken('api')->plainTextToken;

        return $this->success(
            new AuthResource([
                'token' => $token,
                'user' => $user,
            ]),
            t('api.login_success')
        );
    }

    public function register(RegisterRequest $request, RegisterAction $action): \Illuminate\Http\JsonResponse
    {
        $user = $action->handle(
            RegisterData::from($request->validated())
        );

        $token = $user->createToken('api')->plainTextToken;

        return $this->success(
            new AuthResource([
                'token' => $token,
                'user' => $user,
            ]),
            t('api.register_success'),
            201
        );
    }

    public function logout(Request $request): \Illuminate\Http\JsonResponse
    {
        $request->user()
            ->currentAccessToken()
            ?->delete();

        return $this->success(
            message: t('api.logout_success')
        );
    }
}
