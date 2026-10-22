<?php

use App\Modules\Core\Http\Controllers\Api\DeployController;

// No throttle: the rate limiter keeps its counters in the cache, which is the
// `cache` table — missing on a fresh database, the very case this endpoint
// has to migrate. The token check is the protection.
Route::post('/deploy', DeployController::class)
    ->name('api.deploy');
