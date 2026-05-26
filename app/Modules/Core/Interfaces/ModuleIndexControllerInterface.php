<?php

namespace App\Modules\Core\Interfaces;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

interface ModuleIndexControllerInterface
{
    public function accessCheck() : RedirectResponse|Response|null;

    public function index(Request $request, array $segments = []);

}
