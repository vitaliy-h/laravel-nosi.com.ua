<?php

namespace App\Modules\Notification\Dtos;

use App\Modules\Notification\Enums\LetterEnum;
use App\Modules\Notification\Enums\NotificationSourceEnum;
use Spatie\LaravelData\Data;

class SendLetterTemplateData extends Data
{
    /**
     * @param LetterAttachmentData[] $attachments
     */
    public function __construct(
        public LetterEnum $key,
        public array $replace = [],
        public ?string $to = null,
        public array $attachments = [],
        public ?NotificationSourceEnum $source = null,
    ) {}
}
