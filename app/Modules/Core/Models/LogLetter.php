<?php

namespace App\Modules\Core\Models;

use App\Modules\Core\Enums\LetterEnum;
use App\Modules\Core\Enums\LogLetterSourceEnum;
use App\Modules\Core\Enums\LogLetterStatusEnum;
use App\Modules\Core\Traits\Models\HasImage;
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
        'source' => LogLetterSourceEnum::class,
        'template_key' => LetterEnum::class,
    ];
}
