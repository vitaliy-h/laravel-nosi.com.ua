<?php

namespace App\Modules\Shop\Dtos;

readonly class CategorySearchData
{
    public function __construct(
        public bool $root = false,
        public bool $hasImage = false,
        public bool $onlyWithProducts = false,
        public bool $random = false,
        public ?int $limit = null,
    ) {}
}
