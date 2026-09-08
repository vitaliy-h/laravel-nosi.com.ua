<?php

namespace App\Modules\Core\Support;

use Illuminate\Http\JsonResponse;

/**
 * The one place that shapes an API response body.
 *
 * Every `api/*` response carries the same four keys — `success`, `message`,
 * `data`, `errors` — so a client parses one form instead of guessing which half
 * of the envelope it got. A success sets `errors` to null, a failure sets `data`
 * to null; neither key ever goes missing.
 *
 * Controllers reach this through `Core\Traits\Api\ApiResponse`; failures raised
 * as exceptions reach it through the `api/*` renderer in `bootstrap/app.php`,
 * which is why this is a plain class and not only a trait — the renderer is not
 * a controller and used to hand-roll the same body eight times.
 */
class ApiEnvelope
{
    public static function success(
        mixed $data = null,
        ?string $message = 'OK',
        int $status = 200
    ): JsonResponse {
        return response()->json([
            'success' => true,
            'message' => $message,
            'data'    => $data,
            'errors'  => null,
        ], $status);
    }

    public static function error(
        string $message = 'Error',
        mixed $errors = null,
        int $status = 400
    ): JsonResponse {
        return response()->json([
            'success' => false,
            'message' => $message,
            'data'    => null,
            'errors'  => $errors,
        ], $status);
    }
}
