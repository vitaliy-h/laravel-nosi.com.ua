<?php

namespace App\Filament\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

/**
 * Switches the app locale to the admin panel locale (ADMIN_LOCALE) for every
 * panel request, Livewire updates included (registered as persistent middleware).
 * The public site keeps APP_LOCALE.
 */
class SetAdminLocale
{
    public function handle(Request $request, Closure $next): mixed
    {
        app()->setLocale(config('app.admin_locale'));

        return $next($request);
    }
}
