<?php

namespace App\Modules\Core\Http\Middleware;

use App\Modules\Core\Enums\PageModule;
use Illuminate\Auth\Middleware\Authenticate as Middleware;

class Authenticate extends Middleware
{
    protected function redirectTo($request): ?string
    {
        if ($request->is('api/*')) {
            return null;
        }

        return module_url(PageModule::AUTH);
    }
}
