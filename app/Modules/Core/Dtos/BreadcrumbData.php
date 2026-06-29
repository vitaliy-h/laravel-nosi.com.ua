<?php

namespace App\Modules\Core\Dtos;

use Spatie\LaravelData\Data;

class BreadcrumbData extends Data
{
    public function __construct(
        public string $title,
        public string $url,
    )
    {
    }
}
