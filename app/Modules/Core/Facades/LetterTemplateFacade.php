<?php

namespace App\Modules\Core\Facades;

use Illuminate\Support\Facades\Facade;

class LetterTemplateFacade extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return 'letter-template-service';
    }
}
