<?php

namespace App\Modules\Core\Facades;

use App\Modules\Core\Enums\Localization;
use Illuminate\Support\Facades\Facade;

/**
 * @method static string get(string $key, array $replace = [])
 * @method static void clearCache(string|Localization $locale)
 */
class Trans extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return 'translation';
    }
}
