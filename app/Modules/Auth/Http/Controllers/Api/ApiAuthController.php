<?php

namespace App\Modules\Auth\Http\Controllers\Api;

use App\Modules\Auth\Actions\LoginAction;
use App\Modules\Auth\Actions\RegisterAction;
use App\Modules\Auth\Dtos\LoginData;
use App\Modules\Auth\Dtos\RegisterData;
use App\Modules\Auth\Http\Requests\LoginRequest;
use App\Modules\Auth\Http\Requests\RegisterRequest;
use App\Modules\Auth\Http\Resources\AuthResource;
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
