<?php

namespace App\Modules\Notification\Models;

use App\Modules\Notification\Enums\SmsEnum;
use Illuminate\Database\Eloquent\Model;

class SmsTemplate extends Model
{
    protected $fillable = [
        'key',
        'subject',
        'body',
    ];

    protected $casts = [
        'key' => SmsEnum::class,
    ];
}
