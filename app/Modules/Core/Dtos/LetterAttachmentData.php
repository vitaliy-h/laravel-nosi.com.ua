<?php

namespace App\Modules\Core\Dtos;

use Spatie\LaravelData\Data;

class LetterAttachmentData extends Data
{
    public function __construct(
        public string $path,
    )
    {
    }
}
