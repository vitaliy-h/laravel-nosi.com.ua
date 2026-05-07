<?php

namespace App\Modules\Core\Facades;

use Illuminate\Support\Facades\Facade;

class UrlFacade extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return 'url-helper';
    }
}
