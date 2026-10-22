<?php

namespace App\Modules\Faq\ViewModels;

use App\Modules\Core\Abstract\BaseViewModel;
use App\Modules\Faq\Models\Faq;

class FaqViewModel extends BaseViewModel
{
    public function __construct(
        public readonly string $title,
        public readonly string $text,
    ) {}

    public static function fromFaq(Faq $faq): self
    {
        return new self(
            title: (string) $faq->title,
            text: (string) str((string) $faq->text)->sanitizeHtml(),
        );
    }
}
