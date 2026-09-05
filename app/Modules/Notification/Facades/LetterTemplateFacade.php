<?php

namespace App\Modules\Notification\Facades;

use App\Modules\Notification\Services\LetterTemplateService;
use Illuminate\Support\Facades\Facade;

class LetterTemplateFacade extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return LetterTemplateService::class;
    }
}
