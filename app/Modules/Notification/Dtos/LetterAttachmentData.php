<?php

namespace App\Modules\Notification\Dtos;

use Spatie\LaravelData\Data;

class LetterAttachmentData extends Data
{
    public function __construct(
        public string $path,
    ) {}
}
