<?php

namespace App\Modules\Notification\Models;

use App\Modules\Notification\Enums\LogTelegramStatusEnum;
use App\Modules\Notification\Enums\NotificationSourceEnum;
use Illuminate\Database\Eloquent\Model;

class LogTelegram extends Model
{
    protected $table = 'log_telegram';

    protected $fillable = [
        'receiver',
        'body',
        'status',
        'source',
        'error_message',
    ];

    protected $casts = [
        'status' => LogTelegramStatusEnum::class,
        'source' => NotificationSourceEnum::class,
    ];
}
