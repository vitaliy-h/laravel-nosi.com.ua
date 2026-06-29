<?php

namespace App\Modules\Notification\Facades;

use Illuminate\Support\Facades\Facade;

class SmsTemplateFacade extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return 'sms-template-service';
    }
}
