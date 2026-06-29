<?php

namespace App\Modules\Notification\Models;

use App\Modules\Notification\Enums\NotificationSourceEnum;
use App\Modules\Notification\Enums\SmsEnum;
use App\Modules\Notification\Enums\SmsProviderEnum;
use App\Modules\Notification\Enums\LogSmsStatusEnum;
use Database\Factories\LogSmsFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LogSms extends Model
{
    /** @use HasFactory<LogSmsFactory> */
    use HasFactory;

    protected $table = 'log_sms';

    protected $fillable = [
        'template_key',
        'receiver',
        'subject',
        'body',
        'status',
        'provider',
        'source',
        'error_message',
    ];

    protected $casts = [
        'status' => LogSmsStatusEnum::class,
        'provider' => SmsProviderEnum::class,
        'template_key' => SmsEnum::class,
        'source' => NotificationSourceEnum::class,
    ];
}
