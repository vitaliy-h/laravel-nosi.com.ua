<?php

namespace App\Modules\Notification\Models;

use App\Modules\Core\Traits\Models\HasImage;
use App\Modules\Notification\Enums\LetterEnum;
use App\Modules\Notification\Enums\NotificationSourceEnum;
use App\Modules\Notification\Enums\LogLetterStatusEnum;
use Illuminate\Database\Eloquent\Model;

class LogLetter extends Model
{
    use HasImage;

    protected $fillable = [
        'template_key',
        'receiver',
        'subject',
        'sender',
        'sender_name',
        'body',
        'content',
        'status',
        'error_message',
        'source',
        'attachments',
    ];

    protected $casts = [
        'attachments' => 'array',
        'status' => LogLetterStatusEnum::class,
        'source' => NotificationSourceEnum::class,
        'template_key' => LetterEnum::class,
    ];
}
