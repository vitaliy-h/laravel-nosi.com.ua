<?php

namespace App\Modules\Core\Abstract;

use App\Http\Controllers\Controller;
use App\Modules\Core\Traits\Api\ApiResponse;

abstract class BaseApiController extends Controller
{
    use ApiResponse;
}
