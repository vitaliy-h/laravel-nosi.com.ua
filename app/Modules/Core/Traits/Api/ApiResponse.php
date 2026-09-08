<?php

namespace App\Modules\Core\Traits\Api;

use App\Modules\Core\Support\ApiEnvelope;
use Illuminate\Http\JsonResponse;

/**
 * The controller-side shorthand for `Core\Support\ApiEnvelope`, which defines
 * the envelope itself. See `docs/api/README.md`.
 */
trait ApiResponse
{
    protected function success(
        mixed $data = null,
        ?string $message = 'OK',
        int $status = 200
    ): JsonResponse {
        return ApiEnvelope::success($data, $message, $status);
    }

    protected function error(
        string $message = 'Error',
        mixed $errors = null,
        int $status = 400
    ): JsonResponse {
        return ApiEnvelope::error($message, $errors, $status);
    }
}
