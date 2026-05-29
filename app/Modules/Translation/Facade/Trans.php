<?php

namespace App\Modules\Translation\Facade;

use App\Modules\Translation\Enums\Localization;
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
