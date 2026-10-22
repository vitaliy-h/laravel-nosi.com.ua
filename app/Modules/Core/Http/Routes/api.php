<?php

use App\Modules\Core\Http\Controllers\Api\DeployController;

Route::post('/deploy', DeployController::class)
    ->middleware('throttle:5,1')
    ->name('api.deploy');
