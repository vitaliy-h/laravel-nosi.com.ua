<?php

namespace App\Modules\Core\Dtos;

use App\Modules\Core\Enums\LetterEnum;
use App\Modules\Core\Enums\LogLetterSourceEnum;
use Spatie\LaravelData\Data;

class SendLetterTemplateData extends Data
{
    /**
     * @param LetterAttachmentData[] $attachments
     */
    public function __construct(
        public LetterEnum $key,
        public array      $replace = [],
        public ?string    $to = null,
        public array      $attachments = [],
        public ?LogLetterSourceEnum    $source = null,
    )
    {
    }
}
