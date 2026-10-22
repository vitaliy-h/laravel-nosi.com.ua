<?php

namespace App\Modules\Core\Http\Controllers\Api;

use App\Modules\Core\Abstract\BaseApiController;
use App\Modules\Core\Services\PostDeployService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * POST /api/deploy — post-deploy commands for FTP-only hosting, called by
 * .github/workflows/deploy.yml with the `X-Deploy-Token` header.
 */
class DeployController extends BaseApiController
{
    public function __invoke(Request $request, PostDeployService $deploy): JsonResponse
    {
        $token = (string) config('app.deploy_token');

        // No token configured — the endpoint does not exist.
        abort_if($token === '', 404);

        if (!hash_equals($token, (string) $request->header('X-Deploy-Token'))) {
            return $this->error('Forbidden', status: 403);
        }

        $result = $deploy->handle();

        return $result['success']
            ? $this->success($result['steps'], 'Deployed')
            : $this->error('Deploy failed', $result['steps'], 500);
    }
}
