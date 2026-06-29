<?php

namespace App\Modules\Notification\Models;

use App\Modules\Notification\Enums\SmsEnum;
use Database\Factories\SmsTemplateFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SmsTemplate extends Model
{
    /** @use HasFactory<SmsTemplateFactory> */
    use HasFactory;

    protected $fillable = [
        'key',
        'subject',
        'body',
    ];

    protected $casts = [
        'key' => SmsEnum::class,
    ];
}
