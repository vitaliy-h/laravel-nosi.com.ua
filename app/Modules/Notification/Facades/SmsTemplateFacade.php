<?php

namespace App\Modules\Notification\Facades;

use App\Modules\Notification\Services\SmsTemplateService;
use Illuminate\Support\Facades\Facade;

class SmsTemplateFacade extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return SmsTemplateService::class;
    }
}
