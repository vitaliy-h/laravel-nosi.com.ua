<?php

namespace App\Modules\Core\Http\Middleware;

use Illuminate\Auth\Middleware\Authenticate as Middleware;

class Authenticate extends Middleware
{
    protected function redirectTo($request): ?string
    {
        if ($request->is('api/*')) {
            return null;
        }

        return save_route('auth.index');
    }
}
