<?php

namespace App\Modules\Announce\ViewModels;

use App\Modules\Announce\Models\Announce;
use App\Modules\Core\Abstract\BaseViewModel;

class AnnounceViewModel extends BaseViewModel
{
    public function __construct(
        public readonly string $title,
        public readonly ?string $url,
    ) {}

    public static function fromAnnounce(Announce $announce): self
    {
        return new self(
            title: (string) $announce->title,
            url: $announce->url ?: null,
        );
    }
}
